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

class MsQuoteAddress extends \Magento\Framework\Model\AbstractModel
{
    
    const ADDRESS_TYPE_BILLING = 'billing';
    
    const ADDRESS_TYPE_SHIPPING = 'shipping';
    
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Pravams\RecurringOrder\Model\ResourceModel\MsQuoteAddress');
    }
    
    public function loadMsQuoteAddress($msquoteAddressId, $msquoteAddress)
    {
        return $this->_getResource()->getMsQuoteAddressFromId($msquoteAddressId, $msquoteAddress);
    }
    
    public function loadMsQuoteAddressFromMsQuote($msquoteId, $msquoteAddress)
    {
        return $this->_getResource()->getMsQuoteAddressFromMsquoteId($msquoteId, $msquoteAddress);
    }
    
    public function loadMsQuoteBillingAddressFromMsQuote($msquoteId, $msquoteAddress)
    {
        return $this->_getResource()->getMsQuoteBillingAddressFromMsquoteId($msquoteId, $msquoteAddress);
    }
}
