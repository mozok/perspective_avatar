<?php
/**
 * Created by PhpStorm.
 * User: kharidas
 * Date: 23.01.20
 * Time: 15:47
 */

namespace Perspective\CustomerAvatar\Block\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Perspective\CustomerAvatar\Api\AvatarManagerInterface;
use Magento\Customer\Block\Form\Edit as CustomerEditBlock;
use Magento\Customer\Model\Data\Customer as CustomerDataModel;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Newsletter\Model\SubscriberFactory;

class Edit extends CustomerEditBlock
{
    /**
     * @var AvatarManagerInterface
     */
    protected $_avatarManager;

    public function __construct(
        Context $context,
        Session $customerSession,
        SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        AvatarManagerInterface $avatarManager,
        array $data = []
    )
    {
        $this->_avatarManager = $avatarManager;
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $data
        );
    }

    /**
     * Get customer image url
     *
     * @return string
     */
    public function getImageUrl() : string
    {
        /** @var CustomerDataModel $dataModel */
        $dataModel = $this->getCustomer();
        return $this->_avatarManager->getAvatarUrl($dataModel);
    }

    /**
     * Is avatar generated
     *
     * @param string $path
     * @return bool
     */
    public function isGenerated($path) : bool
    {
        return $this->_avatarManager->isGeneratedImage($path);
    }

    /**
     * Get customer's generated image
     *
     * @return string
     */
    public function getGeneratedImage() : string
    {
        /** @var CustomerDataModel $dataModel */
        $dataModel = $this->getCustomer();
        return $this->_avatarManager->getGeneratedImage($dataModel);
    }

    /**
     * Get attribute code
     *
     * @return string
     */
    public function getAttributeCode()
    {
        return AvatarManagerInterface::ATTRIBUTE_CODE;
    }
}
