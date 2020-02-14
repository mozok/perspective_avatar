<?php
/**
 * Created by PhpStorm.
 * User: kharidas
 * Date: 31.01.20
 * Time: 11:58
 */

namespace Perspective\CustomerAvatar\Plugin\Admin\Form;

use Perspective\CustomerAvatar\Api\AvatarManagerInterface;
use Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses as Subject;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\FileUploaderDataResolver;

class DataProvider
{
    /**
     * @var AvatarManagerInterface
     */
    protected $_avatarManager;

    /**
     * @var Customer
     */
    protected $_customer;

    /**
     * @var FileUploaderDataResolver
     */
    protected $_fileResolver;

    public function __construct(
        AvatarManagerInterface $avatarManager,
        Customer $customer,
        FileUploaderDataResolver $fileResolver
    )
    {
        $this->_fileResolver = $fileResolver;
        $this->_customer = $customer;
        $this->_avatarManager = $avatarManager;
    }

    public function afterGetData(
        Subject $subject,
        $data
    )
    {

        foreach ($data as $customerId => $customerData) {
            if(isset($customerData['customer'])) {
                $accountInfo = $customerData['customer'];
                $code = AvatarManagerInterface::ATTRIBUTE_CODE;
                if(empty($accountInfo[$code])) {
                    $generatedCode = AvatarManagerInterface::ATTRIBUTE_CODE_GENERATED;
                    if(!empty($accountInfo[$generatedCode])) {
                        $generated = $accountInfo[$generatedCode];
                    }else {
                        $generated = $this->resolveGeneratedImage($customerId);
                    }
                    $data[$customerId]['customer'][$code] = $generated;
                }
            }
        }
        return $data;
    }

    /**
     * Generate customer image
     *
     * @param $customerId
     * @return null|array
     */
    public function resolveGeneratedImage($customerId)
    {
        $result = null;
        $this->_avatarManager->getAvatarUrlById($customerId);
        $customer = $this->loadCustomer($customerId);
        $data = $customer->getData();
        $this->_fileResolver->overrideFileUploaderData($customer, $data);
        if(isset($data[AvatarManagerInterface::ATTRIBUTE_CODE_GENERATED])) {
            $result = $data[AvatarManagerInterface::ATTRIBUTE_CODE_GENERATED];
        }
        return $result;
    }

    /**
     * Get customer instance by id
     *
     * @param $id
     * @return Customer
     */
    public function loadCustomer($id)
    {
        return $this->_customer->load($id);
    }
}
