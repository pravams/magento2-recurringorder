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

class MsQuote extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('msquote', 'entity_id');
    }
    
    public function getQuoteFromId($quoteId, $msquote)
    {
        $connection = $this->getConnection();
        $select = parent::_getLoadSelect('entity_id', $quoteId, $msquote);
        $data = $connection->fetchRow($select);
        if ($data) {
            $msquote->setData($data);
        }
        
        return $msquote;
    }
}
