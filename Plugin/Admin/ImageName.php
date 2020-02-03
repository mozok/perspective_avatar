<?php
/**
 * Created by PhpStorm.
 * User: kharidas
 * Date: 03.02.20
 * Time: 17:44
 */

namespace Perspective\CustomerAvatar\Plugin\Admin;

use Perspective\CustomerAvatar\Api\AvatarManagerInterface;
use Magento\Customer\Model\Metadata\CustomerCachedMetadata as Subject;
use Magento\Framework\App\RequestInterface;

class ImageName
{
    const CONTROLLER_NAME = 'file';

    const ACTION_NAME = 'customer_upload';

    /**
     * @var RequestInterface
     */
    protected $_request;

    public function __construct(
        RequestInterface $request
    )
    {
        $this->_request = $request;
    }

    public function beforeGetAttributeMetadata(
        Subject $subject,
        $attrCode
    )
    {
        if($this->isAdmin() && AvatarManagerInterface::ATTRIBUTE_CODE == $attrCode) {
            if(@$_FILES['customer']['name'][AvatarManagerInterface::ATTRIBUTE_CODE]) {
                $fileName = $_FILES['customer']['name'][AvatarManagerInterface::ATTRIBUTE_CODE];
                $_FILES['customer']['name'][AvatarManagerInterface::ATTRIBUTE_CODE] = $this->getNewName($fileName);
            }
        }
        return $attrCode;
    }

    /**
     * Get new file name
     *
     * @param $name
     * @return string
     */
    public function getNewName($name)
    {
        $array = explode('.', $name);
        $type = $array[count($array) - 1];
        return uniqid().'.'.$type;
    }

    /**
     * Is admin uploade
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->_request->getControllerName() === self::CONTROLLER_NAME
            && $this->_request->getActionName() === self::ACTION_NAME;
    }
}
