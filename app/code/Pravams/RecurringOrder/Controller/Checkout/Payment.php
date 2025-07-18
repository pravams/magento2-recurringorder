<?php

/**
 * Pravams RecurringOrder Module
 *
 * @category    Pravams
 * @package     Pravams_RecurringOrder
 * @copyright   Copyright (c) 2018 Pravams. (http://pravams.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pravams\RecurringOrder\Controller\Checkout;

use Magento\Framework\App\Action\Context;

use Pravams\RecurringOrder\Model\Session;
use Pravams\RecurringOrder\Model\Cart as CustomerCart;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Payment extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session $customerSession
     */
    protected $customerSession;
    
    /**
     * @var Session $subscriptionSession
     */
    protected $subscriptionSession;
    
    /**
     * @var CustomerRepositoryInterface $customerRepository
     */
    protected $customerRepository;
    
    /**
     * @var CustomerCart $cart
     */
    protected $cart;
    
    /**
     * @var \Pravams\RecurringOrder\Model\MsQuote $_msquote;
     */
    protected $_msquote;
    
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    protected $resultRawFactory;
    
    /**
     * @var \Magento\Framework\Session\StorageInterface $storage
     */
    protected $storage;
    
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        Session $subscriptionSession,
        CustomerRepositoryInterface $customerRepository,
        CustomerCart $cart,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Session\StorageInterface $storage
    ) {
        $this->customerSession = $customerSession;
        $this->subscriptionSession = $subscriptionSession;
        $this->customerRepository = $customerRepository;
        $this->cart = $cart;
        $this->_msquote = $this->subscriptionSession->getMsQuote();
        $this->resultRawFactory = $resultRawFactory;
        $this->storage = $storage;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $msquoteId=$this->subscriptionSession->getMsQuoteId();
        // check that the user is logged in
        if (!$this->customerSession->isLoggedIn() || !$msquoteId) {
            return $this->_redirect('customer/account/login/');
        }
        $params = $this->getRequest()->getParams();
        $subscriptionName = $params['subscription_name'];
        $subscriptionType = $params['subscription_type'];
        $subscriptionTypeX = $params['subscription_x'];
        $subscriptionTypeY = $params['subscription_y'];
        $subscriptionStartDate = $params['subscription_start_date'];
        $pmMethod = $params['payment_method'];
        $pmTitle = $params['payment_method_title'];
        $billingAddressId = $params['billing_address_id_'.$pmMethod];
        
        // save the billing address
        $customerSession = $this->customerSession;
        $customerId = $customerSession->getId();
        $addresses = $this->customerRepository->getById($customerId)->getAddresses();
        $msquoteAddress = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteAddress');
        $aFlag=0;
        foreach ($addresses as $_address) {
            if ($_address->getId() == $billingAddressId) {
                $aFlag=1;
                $msquoteAddress->setMsquoteId($msquoteId);
                $msquoteAddress->setCustomerId($customerId);
                $msquoteAddress->setAddressType($msquoteAddress::ADDRESS_TYPE_BILLING);
                $msquoteAddress->setCustomerAddressId($billingAddressId);
                $msquoteAddress->setFirstname($_address->getFirstName());
                $msquoteAddress->setLastname($_address->getLastName());
                $msquoteAddress->setStreet(json_encode($_address->getStreet()));
                $msquoteAddress->setCity($_address->getCity());
                $msquoteAddress->setRegion($_address->getRegion()->getRegion());
                $msquoteAddress->setRegionId($_address->getRegion()->getRegionId());
                $msquoteAddress->setPostcode($_address->getPostCode());
                $msquoteAddress->setCountryId($_address->getCountryId());
                $msquoteAddress->setTelephone($_address->getTelephone());
                $msquoteAddress->save();
            }
        }
        
        // save the payment method
        $msquotePayment = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuotePayment');
        $msquotePayment->setMsquoteId($msquoteId);
        $msquotePayment->setMethod($pmMethod);
        $msquotePayment->setAdditionalData($pmTitle);
        $msquotePayment->save();
        
        // save the subscription frequency
        $subscriptionStartDate = $subscriptionStartDate." 00:00:00";
        $msProfile = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsProfile');
        $msProfile->setMsquoteId($msquoteId);
        $msProfile->setStoreId($this->_msquote->getStoreId());
        $msProfile->setStartDate($subscriptionStartDate);
        $msProfile->setName($subscriptionName);
        $msProfile->setType($subscriptionType);
        $msProfile->setTypeX($subscriptionTypeX);
        $msProfile->setTypeY($subscriptionTypeY);
        $msProfile->setStatus($msProfile::ACTIVE);
        $msProfile->save();
        
        //invalidate the subscription session
        $this->storage->setData('msquote_id', '');
        
        $respData = "<div>Congratulation your subscription has been created. Click <a href=\"/\">here</a> to continue shopping</div>";
        $result = $this->resultRawFactory->create();
        $result->setContents($respData);
        return $result;
    }
}
