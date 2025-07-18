<?php

/**
 * Pravams RecurringOrder Module
 *
 * @category    Pravams
 * @package     Pravams_RecurringOrder
 * @copyright   Copyright (c) 2018 Pravams. (http://pravams.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pravams\RecurringOrder\Model\ResourceModel;

class MsQuoteAddress extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('msquote_address', 'address_id');
    }
    
    public function getMsQuoteAddressFromId($msquoteAddressId, $msquoteAddress)
    {
        $connection = $this->getConnection();
        $select = parent::_getLoadSelect('address_id', $msquoteAddressId, $msquoteAddress);
        $data = $connection->fetchRow($select);
        if ($data) {
            $msquoteAddress->setData($data);
        }
        
        return $msquoteAddress;
    }
    
    public function getMsQuoteAddressFromMsquoteId($msquoteId, $msquoteAddress)
    {
        $connection = $this->getConnection();
        $select = parent::_getLoadSelect('msquote_id', $msquoteId, $msquoteAddress)
                ->where('address_type = ?', $msquoteAddress::ADDRESS_TYPE_SHIPPING);
        $data = $connection->fetchRow($select);
        if ($data) {
            $msquoteAddress->setData($data);
        }
        
        return $msquoteAddress;
    }
    
    public function getMsQuoteBillingAddressFromMsquoteId($msquoteId, $msquoteAddress)
    {
        $connection = $this->getConnection();
        $select = parent::_getLoadSelect('msquote_id', $msquoteId, $msquoteAddress)
                ->where('address_type = ?', $msquoteAddress::ADDRESS_TYPE_BILLING);
        $data = $connection->fetchRow($select);
        if ($data) {
            $msquoteAddress->setData($data);
        }
        
        return $msquoteAddress;
    }
}
