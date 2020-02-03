<?php
/**
 * Created by PhpStorm.
 * User: kharidas
 * Date: 03.02.20
 * Time: 14:58
 */

namespace Perspective\CustomerAvatar\Controller\Customer;

use Perspective\CustomerAvatar\Model\Image\Provider;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class Avatar extends Action
{
    /**
     * @var JsonFactory
     */
    protected $_jsonFactory;

    /**
     * @var Provider
     */
    protected $_helper;

    /**
     * @var CurrentCustomer
     */
    protected $_currentCustomer;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Provider $helper,
        CurrentCustomer $currentCustomer
    )
    {
        $this->_currentCustomer = $currentCustomer;
        $this->_helper = $helper;
        $this->_jsonFactory = $jsonFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        return $this->getJsonResult([
            'url' => $this->_helper->get($this->_currentCustomer->getCustomerId())
        ]);
    }

    /** Build json response
     * @param array $data
     * @return Json
     */
    public function getJsonResult(array $data) : Json
    {
        $result = $this->_jsonFactory->create();
        return $result->setData($data);
    }
}
