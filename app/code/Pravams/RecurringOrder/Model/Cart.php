<?php

/**
 * Pravams RecurringOrder Module
 *
 * @category    Pravams
 * @package     Pravams_RecurringOrder
 * @copyright   Copyright (c) 2018 Pravams. (http://pravams.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pravams\RecurringOrder\Model;

use Magento\Framework\DataObject;
use Pravams\RecurringOrder\Model\Cart\CartInterface;
use Pravams\RecurringOrder\Model\Session;

/*
 * RecurringOrder Cart
 */

class Cart extends DataObject implements CartInterface
{
    /*
     * MsQuote instance
     * @var MsQuote
     */
    protected $_msquote;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Session $subsriptionSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->subsriptionSession = $subsriptionSession;
        $this->_msquote = $this->subsriptionSession->getMsQuote();
        if ($this->customerSession->getCustomerId()) {
            $this->_initCart();
        }
    }
    
    private function _initCart()
    {
        $msquoteId=$this->subsriptionSession->getMsQuoteId();
        if ($msquoteId) {
            $this->_msquote->loadMsQuote($msquoteId, $this->_msquote);
        } else {
            $currencyCode = $this->_storeManager->getStore()->getBaseCurrency()->getCurrencyCode();
            $msquoteModel = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuote');
            $msquoteModel->setIsActive("0");
            $msquoteModel->setStoreId($this->_storeManager->getStore()->getId());
            $msquoteModel->setCustomerId($this->customerSession->getCustomerId());
            $msquoteModel->setGlobalCurrencyCode($currencyCode);
            $msquoteModel->setStoreCurrencyCode($currencyCode);
            $msquoteModel->setQuoteCurrencyCode($currencyCode);
            $msquoteModel->setBaseCurrencyCode($currencyCode);
            $msquoteModel->save();
            $quoteId = $msquoteModel->getId();
            $this->subsriptionSession->setMsQuoteId($quoteId);
            $this->_msquote->loadMsQuote($quoteId, $this->_msquote);
           
            $msquoteAddModel = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteAddress');
            $msquoteAddModel->setMsquoteId($quoteId);
            $msquoteAddModel->setCustomerId($this->customerSession->getCustomerId());
            $msquoteAddModel->setAddressType($msquoteAddModel::ADDRESS_TYPE_SHIPPING);
            $msquoteAddModel->save();
        }
    }
    
    public function getMsQuote()
    {
        return $this->_msquote;
    }
    
    public function addProduct($productInfo, $requestInfo = null)
    {
        
        if ($productInfo->getTypeId() == "simple") {
            $this->saveSimpleProduct($productInfo, $requestInfo);
        }
        return $this;
    }
    
    private function saveSimpleProduct($productInfo, $requestInfo)
    {
        $msquote = $this->_msquote;
        $msquoteId = $msquote->getEntityId();
        
        if ($msquoteId) {
            $msquoteItemModel = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteItem');
            $msquoteItemColl = $msquoteItemModel->getCollection()
                    ->setMsQuoteFilter($msquoteId)
                    ->addFieldToFilter('product_id', $productInfo->getId());
            if ($msquoteItemColl->count()==0) {
                $imageUrl = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product'.$productInfo->getImage();
                $rowTotal = $productInfo->getPrice() * $requestInfo['qty'];
                $msquoteItemModel = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteItem');
                $msquoteItemModel->setMsquoteId($msquoteId);
                $msquoteItemModel->setProductId($productInfo->getId());
                $msquoteItemModel->setStoreId($this->_storeManager->getStore()->getId());
                $msquoteItemModel->setIsVirtual('0');
                $msquoteItemModel->setSku($productInfo->getSku());
                $msquoteItemModel->setName($productInfo->getName());
                $msquoteItemModel->setDescription($productInfo->getDescription());
                $msquoteItemModel->setQty($requestInfo['qty']);
                $msquoteItemModel->setPrice($productInfo->getPrice());
                $msquoteItemModel->setBasePrice($productInfo->getPrice());
                $msquoteItemModel->setCustomPrice($productInfo->getPrice());
                $msquoteItemModel->setProductType($productInfo->getTypeId());
                $msquoteItemModel->setRowTotal($rowTotal);
                $msquoteItemModel->setUrl($productInfo->getUrlModel()->getUrl($productInfo));
                $msquoteItemModel->setImage($imageUrl);
                $msquoteItemModel->save();
            } else {
                $msquoteItems = $msquoteItemColl->getItems();
                foreach ($msquoteItems as $_msquoteItem) {
                    $msquoteItemId = $_msquoteItem->getItemId();
                }
                if ($msquoteItemId) {
                    $msquoteItemModel = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteItem');
                    $msquoteItemModel->loadMsQuoteItem($msquoteItemId, $msquoteItemModel);
                    $rowTotal = $msquoteItemModel->getRowTotal() + ($productInfo->getPrice() * $requestInfo['qty']);
                    $msquoteItemModel->setQty($requestInfo['qty']+$msquoteItemModel->getQty());
                    $msquoteItemModel->setRowTotal($rowTotal);
                    $msquoteItemModel->save();
                }
            }
            $this->updateQuote($msquote);
        }
    }
    
    public function updateQuote($msquote)
    {
        $msquoteId = $msquote->getEntityId();
        //update the quote
        if ($msquoteId) {
            $msquoteItemModel = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsQuoteItem');
            $msquoteItemColl = $msquoteItemModel->getCollection()
                    ->setMsQuoteFilter($msquoteId);
            $grandTotal = 0;
            $subTotal = 0;
            foreach ($msquoteItemColl as $item) {
                $grandTotal += $item->getRowTotal();
                $subTotal += $item->getRowTotal();
            }
            $msquote->setBaseSubtotal(0);
            $msquote->setSubtotal($subTotal);
            $msquote->setGrandTotal($grandTotal);
            $msquote->save();
        }
    }
    
    
    public function save()
    {
    }
}
