<?php
/**
 * Created by PhpStorm.
 * User: kharidas
 * Date: 23.01.20
 * Time: 11:43
 */

namespace Perspective\CustomerAvatar\Model;

use Magento\Framework\Exception\LocalizedException;
use Perspective\CustomerAvatar\Api\AvatarManagerInterface;
use Perspective\CustomerAvatar\Api\ImageGeneratorInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Data\Customer as CustomerDataModel;
use Magento\Framework\Exception\NoSuchEntityException;
use Exception;
use Perspective\CustomerAvatar\Exception\AvatarInvalidName;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;

class AvatarManager implements AvatarManagerInterface
{
    /**
     * @var Customer
     */
    protected $_customer;

    /**
     * @var null|Customer
     */
    protected $_loadedCustomer = null;

    /**
     * @var ImageGeneratorInterface
     */
    protected $_imageGenerator;

    /**
     * @var null|CustomerDataModel
     */
    protected $_previousData = null;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    public function __construct(
        Customer $customer,
        ImageGeneratorInterface $imageGenerator,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory
    )
    {
        $this->_customerFactory = $customerFactory;
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_imageGenerator = $imageGenerator;
        $this->_customer = $customer;
    }

    /**
     * Get avatar url
     *
     * @param CustomerDataModel $customer
     * @return string
     */
    public function getAvatarUrl(CustomerDataModel $customer) : string
    {
        $attribute = $this->getCustomer($customer->getId())->getData(self::ATTRIBUTE_CODE);
        return $attribute ? $this->getMediaUrl().Customer::ENTITY.$attribute : $this->getGeneratedImage($customer);
    }

    /**
     * Get customer generated image
     *
     * @param CustomerDataModel $customer
     * @return string
     */
    public function getGeneratedImage(CustomerDataModel $customer) : string
    {
        $generatedAttribute = $this->getCustomer($customer->getId())->getData(self::ATTRIBUTE_CODE_GENERATED);
        $attrValue = $this->validateAttribute(
            $customer,
            $generatedAttribute ?? null
        );
        return $this->getMediaUrl().Customer::ENTITY.$attrValue;
    }

    /**
     * Get customer avatar url by customer id
     *
     * @param $customerId
     * @return string
     */
    public function getAvatarUrlById($customerId) : string
    {
        /** @var CustomerDataModel $dataModel */
        $dataModel = $this->createCustomer($customerId)->getDataModel();
        return $this->getAvatarUrl($dataModel);
    }

    /**
     * Get media url
     *
     * @return string
     */
    public function getMediaUrl() : string
    {
        $store = $this->getStore();
        return $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Validate avatar value
     *
     * @param CustomerDataModel $customer
     * @param $value
     * @return mixed
     */
    public function validateAttribute(CustomerDataModel $customer, $value) : string
    {
        if(!$value) {
            try {
                $id = $customer->getId();
                $customer = $this->createCustomer($id);
                $this->generateAvatarForCustomer($customer);
                return $customer->getData(self::ATTRIBUTE_CODE_GENERATED);
            } catch (Exception $e) {
                return '';
            }
        }
        return $value;
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
     * Aggregate customer save & edit events
     *
     * @param CustomerDataModel $customer
     * @throws NoSuchEntityException
     * @throws Exception
     * @throws LocalizedException
     * @throws AvatarInvalidName
     */
    public function aggregateChanges(CustomerDataModel $customer): void
    {
        if($customer->getId()) {
            $customer = $this->getCustomer($customer->getId());
            if($this->hasToGenerateAvatarForCustomer($customer)) {

                $this->generateAvatarForCustomer($customer);
            }
        }else {
            throw new NoSuchEntityException(__('Could not find the customer'));
        }
    }

    /**
     * Validate customer's avatar
     *
     * @param Customer $customer
     * @return bool
     */
    public function validateAvatar(Customer $customer) : bool
    {
        return !!$this->getCustomerAvatarPath($customer);
    }

    /**
     * Set customer's previous data model
     *
     * @param CustomerDataModel|null $customer
     * @return self
     */
    public function setPreviousData($customer)
    {
        $this->_previousData = $customer;
        return $this;
    }

    /**
     * Get customer previous data model
     *
     * @return CustomerDataModel|null
     */
    public function getPreviousData()
    {
        return $this->_previousData;
    }

    /**
     * Has to generate avatar for customer
     *
     * @param Customer $customer
     * @return bool
     */
    protected function hasToGenerateAvatarForCustomer(Customer $customer) : bool
    {
        return !$this->validateAvatar($customer) || $this->hasNameChanges($customer);
    }

    /**
     * Is avatar has been generated
     *
     * @param Customer $customer
     * @return bool
     */
    public function isGenerated(Customer $customer) : bool
    {
        $path = $this->getCustomerAvatarPath($customer);
        return $path ? is_int(strpos($path, ImageGeneratorInterface::MEDIA_DIRECTORY)) : false;
    }

    /**
     * Is avatar has been generated
     *
     * @param $path
     * @return bool
     */
    public function isGeneratedImage(string $path) : bool
    {
        return $path ? is_int(strpos($path, ImageGeneratorInterface::MEDIA_DIRECTORY)) : false;
    }

    /** Customer avatar value
     * @param Customer $customer
     * @return mixed
     */
    public function getCustomerAvatarPath(Customer $customer)
    {
        $generated = $customer->getData(AvatarManagerInterface::ATTRIBUTE_CODE_GENERATED);
        $image = $customer->getData(AvatarManagerInterface::ATTRIBUTE_CODE);
        return $image ?? $generated;
    }

    /**
     * Has name been changed
     *
     * @param Customer $customer
     * @return bool
     */
    private function hasNameChanges(Customer $customer) : bool
    {
        $result = false;
        $data = $customer->getDataModel();
        $previousData = $this->getPreviousData();
        if($previousData) {
            $new = mb_strtoupper(mb_substr($data->getFirstname(), 0, 1).mb_substr($data->getLastname(), 0, 1));
            $prev = mb_strtoupper(
                mb_substr($previousData->getFirstname(), 0, 1).mb_substr($previousData->getLastname(), 0, 1)
            );
            if($new !== $prev) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * Generate and save avatar for customer
     *
     * @param Customer $customer
     * @throws AvatarInvalidName
     * @throws LocalizedException
     */
    protected function generateAvatarForCustomer(Customer $customer)
    {
        $prev = $customer->getData(self::ATTRIBUTE_CODE_GENERATED);
        $prev ? $this->_imageGenerator->removeGeneratedImage($customer, $prev) : null;
        $path = $this->_imageGenerator->generateForCustomer($customer);
        $customer->setData(self::ATTRIBUTE_CODE_GENERATED, $path)->save();
    }

    /**
     * Get customer by id
     *
     * @param $id
     * @return Customer
     */
    public function getCustomer($id) : Customer
    {
        if($this->_loadedCustomer === null) {
            $this->_loadedCustomer = $this->_customer->load($id);
        }
        return $this->_loadedCustomer;
    }

    /**
     * Create net customer instance
     *
     * @param $id
     * @return Customer
     */
    public function createCustomer($id) : Customer
    {
        return $this->_customerFactory->create()->load($id);
    }
}
