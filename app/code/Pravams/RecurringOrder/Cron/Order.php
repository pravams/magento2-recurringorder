<?php

/**
 * Pravams RecurringOrder Module
 *
 * @category    Pravams
 * @package     Pravams_RecurringOrder
 * @copyright   Copyright (c) 2018 Pravams. (http://pravams.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pravams\RecurringOrder\Cron;

class Order
{
    
    protected $_logger;
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface $objectManager
     */
    protected $objectManager;
    
    /**
     * @var \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    protected $customerFactory;
    
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    protected $customerRepository;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $_storeManager;
    
    /**
     * @var \Magento\Catalog\Model\Product $product
     */
    protected $_product;
    
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface
     */
    protected $cartRepositoryInterface;
    
    /**
     * @var \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     */
    protected $cartManagementInterface;
    
    /**
     * @var \Magento\Sales\Model\Order $order
     */
    protected $order;
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Sales\Model\Order $order
    ) {
        $this->_logger = $logger;
        $this->objectManager = $objectManager;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->order = $order;
    }
    
    public function execute()
    {
        
        
        $msProfile = $this->objectManager->create('Pravams\RecurringOrder\Model\MsProfile');
        $msProfileColl = $msProfile->getCollection()
                ->addFieldToFilter('status', $msProfile::ACTIVE)
                ->setOrder('start_date', 'ASC');
        
        foreach ($msProfileColl as $_msProfileColl) {
            $startDate = $_msProfileColl->getStartDate();
            $now = time() - strtotime($startDate);
            if ($now > 0) {
                $msQuoteId = $_msProfileColl->getMsquoteId();
                $msOrder = $this->objectManager->create('Pravams\RecurringOrder\Model\MsOrder');
                $msOrderColl = $msOrder->getCollection()
                    ->addFieldToFilter('msquote_id', $msQuoteId)
                    ->setOrder('created_at', 'ASC');
                
                if (count($msOrderColl)==0) {
                    // first time order
                    $this->placeSubscriptionOrder($msQuoteId);
                } else {
                    // subsequent orders
                    $type = $_msProfileColl->getType();
                    $typeX = $_msProfileColl->getTypeX();
                    $typeY = $_msProfileColl->getTypeY();
                    $nowSub = $msOrderColl->getLastItem()->getCreatedAt();
                    $nowSubTime = time() - strtotime($nowSub);
                    $diff = $nowSubTime/(60*60*24);
                    $diff = floor($diff);
                    
                    if ($type == "everyxy") {
                        if ($typeY == "day") {
                            if ($diff>=$typeX) {
                                $this->placeSubscriptionOrder($msQuoteId);
                            }
                        } elseif ($typeY == "week") {
                            $diff = floor($diff/7);
                            if ($diff>=$typeX) {
                                $this->placeSubscriptionOrder($msQuoteId);
                            }
                        } elseif ($typeY == "month") {
                            $diff = floor($diff/30);
                            if ($diff>=$typeX) {
                                $this->placeSubscriptionOrder($msQuoteId);
                            }
                        } elseif ($typeY == "year") {
                            $diff = floor($diff/365);
                            if ($diff>=$typeX) {
                                $this->placeSubscriptionOrder($msQuoteId);
                            }
                        }
                    } else {
                        if ($type == "daily") {
                            if ($diff>=1) {
                                $this->placeSubscriptionOrder($msQuoteId);
                            }
                        } elseif ($type == "weekly") {
                            if ($diff>=7) {
                                $this->placeSubscriptionOrder($msQuoteId);
                            }
                        } elseif ($type == "monthly") {
                            if ($diff>=30) {
                                $this->placeSubscriptionOrder($msQuoteId);
                            }
                        } elseif ($type == "yearly") {
                            if ($diff>=365) {
                                $this->placeSubscriptionOrder($msQuoteId);
                            }
                        }
                    }
                }
            }
        }
        
        return $this;
    }
    
    private function placeSubscriptionOrder($msQuoteId)
    {
        
        $msQuote = $this->objectManager->create('Pravams\RecurringOrder\Model\MsQuote');
        $msQuote->loadMsQuote($msQuoteId, $msQuote);
        
        $customer = $this->customerFactory->create();
        $customer->getResource()->load($customer, $msQuote->getCustomerId());
        if ($customer->getEntityId()) {
            $store = $this->_storeManager;
            $store->getStore($msQuote->getStoreId());
            
            $cartId = $this->cartManagementInterface->createEmptyCart();
            $quote = $this->cartRepositoryInterface->get($cartId);
            $quote->setStoreId($msQuote->getStoreId());
            $quote->setCurrency();
            $customerRepo = $this->customerRepository->getById($customer->getEntityId());
            $quote->assignCustomer($customerRepo);
            
            $msquoteItemModel = $this->objectManager->create('Pravams\RecurringOrder\Model\MsQuoteItem');
            $msquoteItemColl = $msquoteItemModel->getCollection()
                    ->setMsQuoteFilter($msQuoteId);
            foreach ($msquoteItemColl as $_msquoteItem) {
                $product = $this->_product->load($_msquoteItem->getProductId());
                $quote->addProduct($product, intval($_msquoteItem->getQty()));
            }
            
            $msquoteAddressS = $this->objectManager->create('Pravams\RecurringOrder\Model\MsQuoteAddress');
            $shippingAddress = $msquoteAddressS->loadMsQuoteAddressFromMsQuote($msQuoteId, $msquoteAddressS);
            $shipMethod = $shippingAddress->getShippingMethod();
                    
            $shipAddress = ['firstname' => $shippingAddress->getFirstname(),
                'lastname' => $shippingAddress->getLastname(),
                'street' => implode(" ", json_decode($shippingAddress->getStreet())),
                'city' => $shippingAddress->getCity(),
                'country_id' => $shippingAddress->getCountryId(),
                'region' => $shippingAddress->getRegion(),
                'region_id' => $shippingAddress->getRegionId(),
                'postcode' => $shippingAddress->getPostcode(),
                'telephone' => $shippingAddress->getTelephone(),
                'fax' => $shippingAddress->getFax(),
                'save_in_address_book' => 0];
            
            $msquoteAddressB = $this->objectManager->create('Pravams\RecurringOrder\Model\MsQuoteAddress');
            $billingAddress = $msquoteAddressB->loadMsQuoteBillingAddressFromMsQuote($msQuoteId, $msquoteAddressB);
            
            $billAddress = ['firstname' => $billingAddress->getFirstname(),
                'lastname' => $billingAddress->getLastname(),
                'street' => implode(" ", json_decode($billingAddress->getStreet())),
                'city' => $billingAddress->getCity(),
                'country_id' => $billingAddress->getCountryId(),
                'region' => $billingAddress->getRegion(),
                'region_id' => $billingAddress->getRegionId(),
                'postcode' => $billingAddress->getPostcode(),
                'telephone' => $billingAddress->getTelephone(),
                'fax' => $billingAddress->getFax(),
                'save_in_address_book' => 0];
            
            // add address to quote
            $quote->getBillingAddress()->addData($billAddress);
            $quote->getShippingAddress()->addData($shipAddress);
            
            // set the shipping method
            $shippingAddressQuote = $quote->getShippingAddress();
            $shippingAddressQuote->setCollectShippingRates(true)
                    ->collectShippingRates()
                    ->setShippingMethod($shipMethod);
            
            // set the payment method
            $msquotePayment = $this->objectManager->create('Pravams\RecurringOrder\Model\MsQuotePayment');
            $msquotePaymentM = $msquotePayment->loadMsQuotePaymentFromMsQuote($msQuoteId, $msquotePayment);
            
            $quote->setPaymentMethod($msquotePaymentM->getMethod());
            $quote->setInventoryProcessed(false);
            $pMethod = ['method' => $msquotePaymentM->getMethod()];
            $quote->getPayment()->importData($pMethod);
            $quote->setCustomerIsGuest(0);
            $quote->save();
            
            $quote->collectTotals();
            
            // create Order from Quote
            $quote = $this->cartRepositoryInterface->get($quote->getId());
            $orderId = $this->cartManagementInterface->placeOrder($quote->getId());
            $order = $this->order->load($orderId);
            
            $emailSender = $this->objectManager->create('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
            $emailSender->send($order);
            $order->setEmailSent(1);

            $incrementId = $order->getRealOrderId();
            if ($order->getEntityId()) {
                $nowDate = date("Y-m-d h:i:s", time());
                
                $msOrder = $this->objectManager->create('Pravams\RecurringOrder\Model\MsOrder');
                $msOrder->setMsquoteId($msQuoteId);
                $msOrder->setOrderId($incrementId);
                $msOrder->setCreatedAt($nowDate);
                $msOrder->save();
                $this->_logger->info("subscription order placed ".$incrementId);
            } else {
                $this->_logger->info("an error occurred in subscription");
            }
        }
    }
}
