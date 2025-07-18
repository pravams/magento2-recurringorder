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

class ShippingMethod extends \Magento\Framework\App\Action\Action
{
    
    protected $_resultPageFactory;
    
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    protected $resultRawFactory;
    
    /**
     * @var \Magento\Customer\Model\Session $customerSession
     */
    protected $customerSession;
    
    /**
     * @var Session $subscriptionSession
     */
    protected $subscriptionSession;
    
    /**
     * @var \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     */
    protected $cartManagementInterface;
    
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    protected $customerRepository;
    
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     */
    protected $cartRepository;
    
    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     */
    protected $totalsCollector;
    
    /**
     * @var \Magento\Quote\Model\Cart\ShippingMethodConverter $converter
     */
    protected $converter;
    
    /**
     * @var \Magento\Catalog\Model\Product $product
     */
    protected $_product;
    
    /**
     * @var \Magento\Payment\Model\MethodList $methodList
     */
    protected $methodList;
    
    /**
     * @var \Pravams\RecurringOrder\Model\MsQuote $_msquote;
     */
    protected $_msquote;
    
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Customer\Model\Session $customerSession,
        Session $subscriptionSession,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Quote\Model\Cart\ShippingMethodConverter $converter,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Payment\Model\MethodList $methodList
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->customerSession = $customerSession;
        $this->subscriptionSession = $subscriptionSession;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->customerRepository = $customerRepository;
        $this->cartRepository = $cartRepository;
        $this->totalsCollector = $totalsCollector;
        $this->converter = $converter;
        $this->_objectManager = $objectManager;
        $this->_product = $product;
        $this->methodList = $methodList;
        $this->_msquote = $this->subscriptionSession->getMsQuote();
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
        $addressId = $params['addressId'];
        // check that quote exists
        $customerId = $this->customerSession->getCustomerId();
        //$quote = $this->cartManagementInterface->getCartForCustomer($customerId);
        
        $quoteId = $this->cartManagementInterface->createEmptyCartForCustomer($customerId);
        $quote = $this->cartManagementInterface->getCartForCustomer($customerId);
        $cart = $this->cartRepository->get($quoteId);
        $customer = $this->customerRepository->getById($customerId);
        
        $mgItems = [];
        foreach ($quote->getItemsCollection() as $_mgItem) {
            $mgItems[] = $_mgItem;
            $quote->deleteItem($_mgItem);
        }
        
        // add product to cart
        $msquoteItemModel = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteItem');
        $msquoteItemColl = $msquoteItemModel->getCollection()
                ->setMsQuoteFilter($msquoteId);
        foreach ($msquoteItemColl as $_msquoteItem) {
            $product = $this->_product->load($_msquoteItem->getProductId());
            $quoteItem = $quote->addProduct($product, intval($_msquoteItem->getQty()));
        }
        
        // add shipping address to quote
        $addresses = $customer->getAddresses();
        $shippingAddress = "";
        foreach ($addresses as $_address) {
            if ($_address->getId() == $addressId) {
                $shippingAddress = $_address;
            }
        }
        $shipAddress = ['firstname' => $shippingAddress->getFirstName(),
                'lastname' => $shippingAddress->getLastName(),
                'street' => $shippingAddress->getStreet(),
                'city' => $shippingAddress->getCity(),
                'country_id' => $shippingAddress->getCountryId(),
                'region' => $shippingAddress->getRegion()->getRegion(),
                'postcode' => $shippingAddress->getPostCode(),
                'telephone' => $shippingAddress->getTelephone(),
                'fax' => $shippingAddress->getFax(),
                'save_in_address_book' => 1];
        
        $cart->getShippingAddress()->addData($shipAddress);
        
        // get the shipping methods
        $shipMethods = $this->getShippingMethods($quote);
        $shipMethodsJ = [];
        foreach ($shipMethods as $_shipMethod) {
            $shipMethodsJ[] = $_shipMethod->__toArray();
        }
        $respData = "{ \"shipping_method\": ".json_encode($shipMethodsJ);
        //get the payment methods
        $paymentMethods = $this->methodList->getAvailableMethods($quote);
        $paymentMethodsJ = [];
        foreach ($paymentMethods as $_paymentMethod) {
            $paymentMethodsI['code'] = $_paymentMethod->getCode();
            $paymentMethodsI['title'] = $_paymentMethod->getTitle();
            $paymentMethodsJ[] = $paymentMethodsI;
        }
        $respData = $respData.", \"payment_method\": ".json_encode($paymentMethodsJ)." }";
        $respData = "{\"data\": ".$respData." }" ;
        $result = $this->resultRawFactory->create();
        $result->setContents($respData);
        
        // add the items back to cart
        /*foreach($mgItems as $_msquoteItem){
            $product = $this->_product->load($_msquoteItem->getProductId());
            $quote->addProduct($product, intval($_msquoteItem->getQty()));
            $quote->save();
        }*/
        $taxAmount = $quote->getTotals()['tax']->getValue();
        // msquote update
        $this->_msquote->loadMsQuote($msquoteId, $this->_msquote);
        $msquote = $this->_msquote;
        $subtotal = $msquote->getSubtotal();
        $grandTotal = $subtotal + $taxAmount;
        $msquote->setBaseSubtotal($taxAmount);
        $msquote->setGrandTotal($grandTotal);
        $msquote->save();
        
        return $result;
    }
    
    private function getShippingMethods(\Magento\Quote\Model\Quote $quote)
    {
        $output = [];
        $shippingAddress = $quote->getShippingAddress();
        //$shippingAddress->addData();
        $shippingAddress->setCollectShippingRates(true);
        
        $this->totalsCollector->collectAddressTotals($quote, $shippingAddress);
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $output[] = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
            }
        }
        return $output;
    }
}
