<?php
/**
 * Created by PhpStorm.
 * User: kharidas
 * Date: 03.02.20
 * Time: 16:49
 */

namespace Perspective\CustomerAvatar\Model\Image;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Perspective\CustomerAvatar\Api\AvatarManagerInterface;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class Provider
{
    /**
     * @var CustomerResource
     */
    protected $_resource;

    /**
     * @var Attribute
     */
    protected $_attribute;

    /**
     * @var null|string
     */
    protected $_typeCode = null;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var null|string
     */
    protected $_mediaUrl = null;

    public function __construct(
        CustomerResource $resource,
        Attribute $attribute,
        StoreManagerInterface $storeManager
    )
    {
        $this->_storeManager = $storeManager;
        $this->_attribute = $attribute;
        $this->_resource = $resource;
    }

    /**
     * Get image url
     *
     * @param $id
     * @return string
     * @throws LocalizedException
     */
    public function get($id) : string
    {
         $image = $this->getAttributeValue($id, AvatarManagerInterface::ATTRIBUTE_CODE);
         $path = $image ? $image : $this->getAttributeValue($id, AvatarManagerInterface::ATTRIBUTE_CODE_GENERATED);
         return $this->buildUrl($path);
    }

    /**
     * Build image url by path
     *
     * @param string $path
     * @return string
     * @throws LocalizedException
     */
    public function buildUrl(string $path) : string
    {
        return $this->getMediaUrl().$this->getEntityTypeCode().$path;
    }

    /**
     * Get media url
     *
     * @return string
     */
    public function getMediaUrl() : string
    {
        if($this->_mediaUrl === null) {
            $store = $this->getStore();
            $this->_mediaUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        }
        return $this->_mediaUrl;
    }

    /**
     * Get current store
     *
     * @return StoreInterface|null
     */
    public function getStore()
    {
        try {
            $store = $this->_storeManager->getStore();
        } catch (NoSuchEntityException $e) {
            $store = $this->_storeManager->getDefaultStoreView();
        }
        return $store;
    }

    /**
     * @param $id
     * @param $code
     * @return string
     * @throws LocalizedException
     */
    public function getAttributeValue($id, $code) : string
    {
        $connection = $this->_resource->getConnection();
        $attribute = $this->loadAttribute($code);
        $select = $connection->select()->from(
            $attribute->getBackendTable(),
            'value'
        )->where(
            'entity_id = '.$id.' AND attribute_id = '.$attribute->getId()
        );
        $result = $connection->fetchRow($select);
        return $result ? $result['value'] : '';
    }

    /**
     * Get attribute instance
     *
     * @param $code
     * @return Attribute
     * @throws LocalizedException
     */
    public function loadAttribute($code) : Attribute
    {
        return $this->_attribute->loadByCode($this->getEntityTypeCode(), $code);
    }

    /**
     * Get entity type code
     *
     * @return string|null
     * @throws LocalizedException
     */
    public function getEntityTypeCode() : string
    {
        if($this->_typeCode === null) {
            $this->_typeCode = $this->_resource->getEntityType()->getEntityTypeCode();
        }
        return $this->_typeCode;
    }
}
