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

class ShippingAddress extends \Magento\Framework\App\Action\Action
{
    
    protected $subscriptionSession;
    
    /**
     * @var \Magento\Customer\Model\Session $customerSession
     */
    protected $customerSession;
    
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
    
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        Session $subscriptionSession,
        CustomerRepositoryInterface $customerRepository,
        CustomerCart $cart,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        $this->customerSession = $customerSession;
        $this->subscriptionSession = $subscriptionSession;
        $this->customerRepository = $customerRepository;
        $this->cart = $cart;
        $this->_msquote = $this->subscriptionSession->getMsQuote();
        $this->resultRawFactory = $resultRawFactory;
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
        $addressId = $params['selected-shipping-address'];
        $shippingMethod = $params['shipping_method'];
        $shippingMethodA = explode("_", $shippingMethod);
        $shippingPrice = $params[$shippingMethod];
        $shippingCarrier = $params['shipping_carrier_title'];
        $shippingTitle = $params['shipping_method_title'];
        
        // shipping address update
        $msquoteAddress = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteAddress');
        $msquoteAddress->loadMsQuoteAddressFromMsQuote($msquoteId, $msquoteAddress);
        
        $customerSession = $this->customerSession;
        $customerId = $customerSession->getId();
        $addresses = $this->customerRepository->getById($customerId)->getAddresses();
        $aFlag=0;
        foreach ($addresses as $_address) {
            if ($_address->getId() == $addressId) {
                $aFlag=1;
                $msquoteAddress->setCustomerAddressId($addressId);
                $msquoteAddress->setFirstname($_address->getFirstName());
                $msquoteAddress->setLastname($_address->getLastName());
                $msquoteAddress->setStreet(json_encode($_address->getStreet()));
                $msquoteAddress->setCity($_address->getCity());
                $msquoteAddress->setRegion($_address->getRegion()->getRegion());
                $msquoteAddress->setRegionId($_address->getRegion()->getRegionId());
                $msquoteAddress->setPostcode($_address->getPostCode());
                $msquoteAddress->setCountryId($_address->getCountryId());
                $msquoteAddress->setTelephone($_address->getTelephone());
                $msquoteAddress->setShippingMethod($shippingMethod);
                $msquoteAddress->setShippingDescription($shippingCarrier." - ".$shippingTitle);
                $msquoteAddress->setShippingAmount($shippingPrice);
                $msquoteAddress->save();
                
                $streetAddress = "[";
                $i=0;
                foreach ($_address->getStreet() as $_streetadd) {
                    $streetAddress = $streetAddress."\"".$_streetadd."\"";
                    if ($i < count($_address->getStreet())-1) {
                        $streetAddress = $streetAddress.",";
                    }
                    $i++;
                }
                $streetAddress = $streetAddress."]";
                $pmtData = "{\"addressInformation\":{\"shipping_address\":{\"customerAddressId\":\"".$addressId."\",\"countryId\":\"".$_address->getCountryId()."\",\"regionId\":\"".$_address->getRegion()->getRegionId()."\",\"regionCode\":\"".$_address->getRegion()->getRegionCode()."\",\"region\":\"".$_address->getRegion()->getRegion()."\",\"customerId\":\"".$customerId."\",\"street\":".$streetAddress.",\"telephone\":\"".$_address->getTelephone()."\",\"postcode\":\"".$_address->getPostCode()."\",\"city\":\"".$_address->getCity()."\",\"firstname\":\"".$_address->getFirstName()."\",\"lastname\":\"".$_address->getLastName()."\"},\"billing_address\":{\"customerAddressId\":\"".$addressId."\",\"countryId\":\"".$_address->getCountryId()."\",\"regionId\":\"".$_address->getRegion()->getRegionId()."\",\"regionCode\":\"".$_address->getRegion()->getRegionCode()."\",\"region\":\"".$_address->getRegion()->getRegion()."\",\"customerId\":\"".$customerId."\",\"street\":".$streetAddress.",\"telephone\":\"".$_address->getTelephone()."\",\"postcode\":\"".$_address->getPostCode()."\",\"city\":\"".$_address->getCity()."\",\"firstname\":\"".$_address->getFirstName()."\",\"lastname\":\"".$_address->getLastName()."\",\"saveInAddressBook\":null},\"shipping_method_code\":\"".$shippingMethodA[1]."\",\"shipping_carrier_code\":\"".$shippingMethodA[0]."\"}}";
            }
        }
        if ($aFlag == 0) {
            return $this->_redirect('RecurringOrder');
        }
        // shipping rate
        $msquoteRate = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteShippingRate');
        
        $msquoteRateColl = $msquoteRate->getCollection()
                    ->addFieldToFilter('address_id', $msquoteAddress->getId());
        if ($msquoteRateColl->count()==0) {
            $msquoteRate->setAddressId($msquoteAddress->getId());
        } else {
            $msquoteRate->loadMsQuoteShippingRate($msquoteAddress->getId(), $msquoteRate);
        }
        $msquoteRate->setCode($shippingMethod);
        $msquoteRate->setPrice($shippingPrice);
        $msquoteRate->setCarrier($shippingMethodA[0]);
        $msquoteRate->setMethod($shippingMethodA[1]);
        $msquoteRate->setCarrierTitle($shippingCarrier);
        $msquoteRate->setMethodTitle($shippingTitle);
        $msquoteRate->save();
        
        // msquote update
        $this->_msquote->loadMsQuote($msquoteId, $this->_msquote);
        $msquote = $this->_msquote;
        $grandTotal = round($msquote->getGrandTotal(), 2);
        $taxAmount = round($msquote->getBaseSubtotal(), 2);
        $grandTotal = $grandTotal + $shippingPrice;
        $msquote->setBaseSubtotal($taxAmount);
        $msquote->setGrandTotal($grandTotal);
        $msquote->save();
        
        $respData = "{\"address\": ".$msquoteAddress->toJson().", \"pmt_data\" :".$pmtData." ,\"msquote\": ".$msquote->toJson()."}";
        $result = $this->resultRawFactory->create();
        $result->setContents($respData);
        return $result;
    }
}
