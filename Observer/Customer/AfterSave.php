<?php
/**
 * Created by PhpStorm.
 * User: kharidas
 * Date: 23.01.20
 * Time: 11:08
 */

namespace Perspective\CustomerAvatar\Observer\Customer;

use Magento\Framework\Exception\LocalizedException;
use Perspective\CustomerAvatar\Api\AvatarManagerInterface;
use Perspective\CustomerAvatar\Exception\AvatarInvalidName;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Data\Customer as CustomerDataModel;
use Magento\Framework\Exception\NoSuchEntityException;
use Exception;
use Psr\Log\LoggerInterface as Logger;

class AfterSave implements ObserverInterface
{
    /**
     * @var AvatarManagerInterface
     */
    protected $_avatarManager;

    /**
     * @var Logger
     */
    protected $_logger;

    public function __construct(
        AvatarManagerInterface $avatarManager,
        Logger $logger
    )
    {
        $this->_logger = $logger;
        $this->_avatarManager = $avatarManager;
    }

    /**
     * @param Observer $observer
     * @throws NoSuchEntityException
     * @throws Exception
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var $customer CustomerDataModel */
        $customer = $observer->getData('customer_data_object');
        $prevData = $observer->getData('orig_customer_data_object');
        try {
            $this->_avatarManager->setPreviousData($prevData)->aggregateChanges($customer);
        } catch (AvatarInvalidName $e) {
            $this->_logger->critical($e);
        }
    }
}
