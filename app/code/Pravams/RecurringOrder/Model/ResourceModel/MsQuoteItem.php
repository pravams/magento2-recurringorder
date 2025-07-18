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

class MsQuoteItem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('msquote_item', 'item_id');
    }
    
    public function getMsQuoteItemFromId($msquoteItemId, $msquoteItem)
    {
        $connection = $this->getConnection();
        $select = parent::_getLoadSelect('item_id', $msquoteItemId, $msquoteItem);
        $data = $connection->fetchRow($select);
        if ($data) {
            $msquoteItem->setData($data);
        }
        
        return $msquoteItem;
    }
}
