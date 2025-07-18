<?php

/**
 * Pravams RecurringOrder Module
 *
 * @category    Pravams
 * @package     Pravams_RecurringOrder
 * @copyright   Copyright (c) 2018 Pravams. (http://pravams.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Pravams\RecurringOrder\Block;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Pravams\RecurringOrder\Model\Session;

class Checkout extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        Session $subsriptionSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->subsriptionSession = $subsriptionSession;
        $this->_msquote = $this->subsriptionSession->getMsQuote();
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }
    
    public function getCustomerAddress()
    {
        $customerSession = $this->customerSession;
        $customerId = $customerSession->getId();
        $addresses = $this->customerRepository->getById($customerId)->getAddresses();
        return $addresses;
    }
    
    public function getMsQuote()
    {
        $msquoteId=$this->subsriptionSession->getMsQuoteId();
        if ($msquoteId) {
            $this->_msquote->loadMsQuote($msquoteId, $this->_msquote);
            return $this->_msquote;
        }
    }
    
    public function getMsQuoteAddress()
    {
        $msquoteId=$this->subsriptionSession->getMsQuoteId();
        if ($msquoteId) {
            $msquoteAddressModel = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteAddress');
            $msquoteAddressModel->loadMsQuoteAddressFromMsQuote($msquoteId, $msquoteAddressModel);
            return $msquoteAddressModel;
        }
    }
    
    public function getMsQuoteItems($msquoteId)
    {
        $msquoteItemModel = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteItem');
        $msquoteItemColl = $msquoteItemModel->getCollection()
                ->setMsQuoteFilter($msquoteId);
        
        return $msquoteItemColl;
    }
    
    public function displayPrice($price)
    {
        return $this->storeManager->getStore()->getBaseCurrency()->format($price, [], true);
    }
}
