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

class DataProvider
{
    public function afterGetData(
        Subject $subject,
        $data
    )
    {
        foreach ($data as $customerId => $customerData) {
            if(@$customerData['customer']) {
                $accountInfo = $customerData['customer'];
                if(!$accountInfo[AvatarManagerInterface::ATTRIBUTE_CODE]) {
                    $generated = $accountInfo[AvatarManagerInterface::ATTRIBUTE_CODE_GENERATED];
                    $data[$customerId]['customer'][AvatarManagerInterface::ATTRIBUTE_CODE] = $generated;
                }
            }
        }
        return $data;
    }
}
