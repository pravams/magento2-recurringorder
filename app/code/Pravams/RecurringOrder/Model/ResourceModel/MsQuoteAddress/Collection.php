<?php

/**
 * Pravams RecurringOrder Module
 *
 * @category    Pravams
 * @package     Pravams_RecurringOrder
 * @copyright   Copyright (c) 2018 Pravams. (http://pravams.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Pravams\RecurringOrder\Model\ResourceModel\MsQuoteAddress;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Pravams\RecurringOrder\Model\MsQuoteAddress', 'Pravams\RecurringOrder\Model\ResourceModel\MsQuoteAddress');
    }
    
    /*
     * Setting filter on msquoteid
     */
    public function setMsQuoteFilter($msquoteId)
    {
        $this->addFieldToFilter('msquote_id', $msquoteId ? $msquoteId : ['null' =>1]);
        return $this;
    }
}
