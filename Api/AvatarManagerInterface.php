<?php
/**
 * Created by PhpStorm.
 * User: kharidas
 * Date: 21.01.20
 * Time: 15:12
 */

namespace Perspective\CustomerAvatar\Api;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Data\Customer as CustomerDataModel;
use Magento\Framework\Exception\NoSuchEntityException;
use Exception;
use Perspective\CustomerAvatar\Exception\AvatarInvalidName;

interface AvatarManagerInterface
{
    const ATTRIBUTE_CODE = 'eav_attribute';

    const ATTRIBUTE_CODE_GENERATED = 'eav_attribute_generated';

    /**
     * Build customer image url
     *
     * @param CustomerDataModel $customer
     * @return string
     */
    public function getAvatarUrl(CustomerDataModel $customer) : string;

    /**
     * Get generated customer image
     *
     * @param CustomerDataModel $customer
     * @return string
     */
    public function getGeneratedImage(CustomerDataModel $customer) : string;

    /**
     * Get customer's avatar url by id
     *
     * @param $customerId
     * @return string
     */
    public function getAvatarUrlById($customerId) : string;

    /**
     * Process image generation after customer created/edited
     *
     * @param CustomerDataModel $customer
     * @throws NoSuchEntityException
     * @throws Exception
     * @throws AvatarInvalidName
     */
    public function aggregateChanges(CustomerDataModel $customer) : void;

    /**
     * Validate customer's attribute
     *
     * @param Customer $customer
     * @return bool
     */
    public function validateAvatar(Customer $customer) : bool;

    /**
     * Set customer's previous data model
     *
     * @param CustomerDataModel|null $customer
     * @return self
     */
    public function setPreviousData($customer);

    /**
     * Get customer previous data model
     *
     * @return CustomerDataModel|null
     */
    public function getPreviousData();

    /**
     * Is avatar has been generated
     *
     * @param Customer $customer
     * @return bool
     */
    public function isGenerated(Customer $customer) : bool;

    /**
     * Is avatar has been generated
     *
     * @param string $path
     * @return bool
     */
    public function isGeneratedImage(string $path) : bool;
}
