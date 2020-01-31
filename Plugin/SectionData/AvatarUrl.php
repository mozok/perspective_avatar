<?php


namespace Perspective\CustomerAvatar\Plugin\SectionData;

use Perspective\CustomerAvatar\Api\AvatarManagerInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\CustomerData\Customer as Subject;

class AvatarUrl
{
    /**
     * @var CurrentCustomer
     */
    protected $_currentCustomer;

    /**
     * @var AvatarManagerInterface
     */
    protected $_avatarManager;

    public function __construct(
        CurrentCustomer $currentCustomer,
        AvatarManagerInterface $avatarManager
    )
    {
        $this->_avatarManager = $avatarManager;
        $this->_currentCustomer = $currentCustomer;
    }

    public function afterGetSectionData(
        Subject $subject,
        $result
    )
    {
        $id = $this->_currentCustomer->getCustomerId();
        if($id) {
            $result['avatar_url'] = $this->_avatarManager->getAvatarUrlById($id);
        }
        return $result;
    }
}
