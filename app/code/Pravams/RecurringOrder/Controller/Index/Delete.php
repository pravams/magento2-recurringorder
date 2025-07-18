<?php

/**
 * Pravams RecurringOrder Module
 *
 * @category    Pravams
 * @package     Pravams_RecurringOrder
 * @copyright   Copyright (c) 2018 Pravams. (http://pravams.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pravams\RecurringOrder\Controller\Index;

use Magento\Framework\App\Action\Context;

use Pravams\RecurringOrder\Model\Session;
use Pravams\RecurringOrder\Model\Cart as CustomerCart;

class Delete extends \Magento\Framework\App\Action\Action
{
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Session $subscriptionSession,
        CustomerCart $cart
    ) {
        parent::__construct($context);
        $this->subscriptionSession = $subscriptionSession;
        $this->cart = $cart;
    }
    
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $itemId = $params['id'];
        $msquoteId = $this->subscriptionSession->getMsQuoteId();
        $cart = $this->cart;
        $msquote = $cart->getMsQuote();
        if ($msquoteId) {
            $msquoteItemModel = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteItem');
            $msquoteItemColl = $msquoteItemModel->getCollection()
                    ->setMsQuoteFilter($msquoteId);
            foreach ($msquoteItemColl as $_msquoteItemColl) {
                $msquoteItemsId = $_msquoteItemColl->getItemId();
                if ($msquoteItemsId == $itemId) {
                    $msquoteItemModel = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteItem');
                    $msquoteItemModel->loadMsQuoteItem($itemId, $msquoteItemModel);
                    $msquoteItemModel->delete();
                    //$this->messageManager->addSuccess("Subscription Item removed.");
                }
            }
            // update the quote
            $cart->updateQuote($msquote);
        }
        
        $this->_redirect('checkout/cart');
    }
}
