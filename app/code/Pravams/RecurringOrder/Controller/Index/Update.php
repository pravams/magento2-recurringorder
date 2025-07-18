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

class Update extends \Magento\Framework\App\Action\Action
{
    
    protected $subscriptionSession;
    
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
        $subscriptionSession = $this->subscriptionSession;
        $cart = $this->cart;
        $msquoteId = $subscriptionSession->getMsQuoteId();
        $msquote = $cart->getMsQuote();
        if ($msquoteId) {
            $msquoteItemModel = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteItem');
            $msquoteItemColl = $msquoteItemModel->getCollection()
                    ->setMsQuoteFilter($msquoteId);
            
            foreach ($msquoteItemColl as $_msquoteItemColl) {
                $msquoteItemId = $_msquoteItemColl->getItemId();
                if (array_key_exists($msquoteItemId, $params['cart'])) {
                    $msquoteItemQty = $params['cart'][$msquoteItemId]['qty'];
                    if ($msquoteItemQty>0) {
                        $msquoteItemModel = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteItem');
                        $msquoteItemModel->loadMsQuoteItem($msquoteItemId, $msquoteItemModel);
                        $rowTotal = $msquoteItemModel->getPrice() * $msquoteItemQty;
                        $msquoteItemModel->setQty($msquoteItemQty);
                        $msquoteItemModel->setRowTotal($rowTotal);
                        $msquoteItemModel->save();
                    } elseif ($msquoteItemQty == 0) {
                        // delete the item
                        $msquoteItemModel = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteItem');
                        $msquoteItemModel->loadMsQuoteItem($msquoteItemId, $msquoteItemModel);
                        $msquoteItemModel->delete();
                    }
                }
            }
            //update the quote
            $cart->updateQuote($msquote);
        }
        $this->_redirect('checkout/cart');
    }
}
