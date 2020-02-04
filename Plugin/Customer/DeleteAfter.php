<?php
/**
 * Created by PhpStorm.
 * User: kharidas
 * Date: 31.01.20
 * Time: 12:28
 */

namespace Perspective\CustomerAvatar\Plugin\Customer;

use Perspective\CustomerAvatar\Api\AvatarManagerInterface;
use Perspective\CustomerAvatar\Api\ImageGeneratorInterface;
use Magento\Customer\Model\Customer;

class DeleteAfter
{
    /**
     * @var ImageGeneratorInterface
     */
    protected $_imageGenerator;

    public function __construct(
        ImageGeneratorInterface $imageGenerator
    )
    {
        $this->_imageGenerator = $imageGenerator;
    }

    public function afterDelete(
        Customer $subject,
        $model
    )
    {
        /** @var Customer $model */
        $path = $model->getData(AvatarManagerInterface::ATTRIBUTE_CODE_GENERATED);
        if($path) {
            $this->_imageGenerator->removeGeneratedImage($model, $path);
        }
        return $model;
    }
}
