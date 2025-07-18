<?php

/**
 * Pravams RecurringOrder Module
 *
 * @category    Pravams
 * @package     Pravams_RecurringOrder
 * @copyright   Copyright (c) 2018 Pravams. (http://pravams.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pravams\RecurringOrder\Block\Adminhtml\Edit\Tab;

class View extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Backend\Model\UrlInterface $urlInterface,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->urlInterface = $urlInterface;
        $this->_objectManager = $objectManager;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }
    
    public function getProfile()
    {
        $params = $this->getRequest()->getParams();
        $profileId = $params['id'];
        
        $msProfileModel = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsProfile');
        $msProfileModel->getResource()->load($msProfileModel, $profileId);
        
        return $msProfileModel;
    }
    
    public function getMsQuoteShippingAddress($msQuoteId)
    {
        $msQuoteAddress = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteAddress');
        $msQuoteAddress->loadMsQuoteAddressFromMsQuote($msQuoteId, $msQuoteAddress);
        
        return $msQuoteAddress;
    }
    
    public function getMsQuoteBillingAddress($msQuoteId)
    {
        $msQuoteAddress = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteAddress');
        $msQuoteAddress->loadMsQuoteBillingAddressFromMsQuote($msQuoteId, $msQuoteAddress);
        
        return $msQuoteAddress;
    }
    
    public function getPaymentMethod($msQuoteId)
    {
        $msQuotePayment = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuotePayment');
        $msQuotePayment->loadMsQuotePaymentFromMsQuote($msQuoteId, $msQuotePayment);
        
        return $msQuotePayment;
    }
    
    public function getMsQuote($msQuoteId)
    {
        $msQuote = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuote');
        $msQuote->loadMsQuote($msQuoteId, $msQuote);
        
        return $msQuote;
    }
    
    public function getMsQuoteItems($msQuoteId)
    {
        $msquoteItemModel = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteItem');
        $msquoteItemColl = $msquoteItemModel->getCollection()
                ->setMsQuoteFilter($msQuoteId);
        
        return $msquoteItemColl;
    }
    
    public function getMsOrders($msQuoteId)
    {
        $msOrder = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsOrder');
        $msOrderColl = $msOrder->getCollection()
                ->addFieldToFilter('msquote_id', $msQuoteId);
        
        return $msOrderColl;
    }
    
    public function displayPrice($storeId, $price)
    {
        $store = $this->_storeManager;
        $store->getStore($storeId);
        return $store->getStore()->getBaseCurrency()->format($price, [], true);
    }
}
