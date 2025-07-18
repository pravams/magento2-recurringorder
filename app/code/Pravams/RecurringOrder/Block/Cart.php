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

use Pravams\RecurringOrder\Model\Session;

class Cart extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Session $subsriptionSession,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->subsriptionSession = $subsriptionSession;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }
    
    public function getMsQuote()
    {
        $subsriptionSession = $this->subsriptionSession;
        $msquote = $subsriptionSession->getMsQuote();
        $msquoteId = $subsriptionSession->getMsQuoteId();
        $msquote->loadMsQuote($msquoteId, $msquote);
        return $msquote;
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
