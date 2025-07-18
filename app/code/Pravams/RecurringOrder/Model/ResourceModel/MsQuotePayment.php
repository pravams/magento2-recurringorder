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

class MsQuotePayment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('msquote_payment', 'payment_id');
    }
    
    public function getMsQuotePaymentFromMsQuoteId($msquoteId, $msquotePayment)
    {
        $connection = $this->getConnection();
        $select = parent::_getLoadSelect('msquote_id', $msquoteId, $msquotePayment);
        $data = $connection->fetchRow($select);
        if ($data) {
            $msquotePayment->setData($data);
        }
        return $msquotePayment;
    }
}
