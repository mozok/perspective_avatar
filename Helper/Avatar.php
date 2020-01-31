<?php


namespace Perspective\CustomerAvatar\Helper;

use Perspective\CustomerAvatar\Api\AvatarManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Avatar extends AbstractHelper
{
    /**
     * @var AvatarManagerInterface
     */
    protected $_avatarManger;

    public function __construct(
        Context $context,
        AvatarManagerInterface $avatarManager
    )
    {
        $this->_avatarManger = $avatarManager;
        parent::__construct($context);
    }

    /**
     * Get customer's avatar
     *
     * @param $id
     * @return string
     */
    public function getCustomerAvatar($id) : string
    {
        return $this->_avatarManger->getAvatarUrlById($id);
    }
}
