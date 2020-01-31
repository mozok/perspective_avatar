<?php
/**
 * Created by PhpStorm.
 * User: kharidas
 * Date: 21.01.20
 * Time: 17:04
 */

namespace Perspective\CustomerAvatar\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\Customer;
use Perspective\CustomerAvatar\Exception\AvatarInvalidName;

interface ImageGeneratorInterface
{
    /**
     * Image box sizing
     */
    const IMAGE_SIZE = 300;

    /**
     * Image text's size
     */
    const TEXT_SIZE = 80;

    /**
     * Font family file name
     */
    const FONT_FAMILY_FILE = 'arial.ttf';

    /**
     * Media directory path
     */
    const MEDIA_DIRECTORY = '/avatar/generated/';

    /**
     * Full media directory path
     */
    const FULL_MEDIA_DIRECTORY = 'customer/avatar/generated/';

    /**
     * Image format
     */
    const IMAGE_FORMAT = '.png';

    /**
     * Generate png image and get saved location path
     *
     * @param string $text
     * @return string
     * @throws LocalizedException
     */
    public function generate(string $text) : string;

    /**
     * Generate image by customer's data
     *
     * @param Customer $customer
     * @return string
     * @throws AvatarInvalidName
     * @throws LocalizedException
     */
    public function generateForCustomer(Customer $customer) : string;
}
