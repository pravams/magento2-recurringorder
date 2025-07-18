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

class MsQuoteShippingRate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('msquote_shipping_rate', 'rate_id');
    }
    
    public function getMsQuoteShippingRateFromId($msquoteShippingRateId, $msquoteShippingRate)
    {
        $connection = $this->getConnection();
        $select = parent::_getLoadSelect('address_id', $msquoteShippingRateId, $msquoteShippingRate);
        $data = $connection->fetchRow($select);
        if ($data) {
            $msquoteShippingRate->setData($data);
        }
        
        return $msquoteShippingRate;
    }
}
