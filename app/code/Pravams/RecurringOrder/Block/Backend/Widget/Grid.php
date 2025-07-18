<?php

/**
 * Pravams RecurringOrder Module
 *
 * @category    Pravams
 * @package     Pravams_RecurringOrder
 * @copyright   Copyright (c) 2018 Pravams. (http://pravams.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pravams\RecurringOrder\Block\Backend\Widget;

class Grid extends \Magento\Backend\Block\Widget\Grid
{
    
    protected $_template = 'Pravams_RecurringOrder::widget/grid.phtml';
    
    protected function _prepareCollection()
    {
        if ($this->getCollection()) {
            
            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            
            if ($this->getColumn($columnId) && $this->getColumn($columnId)->getIndex()) {
                $dir = strtolower($dir) == 'desc' ? 'desc' : 'asc';
                $this->getColumn($columnId)->setDir($dir);
                $this->_setCollectionOrder($this->getColumn($columnId));
            }
        }

        return $this;
    }
    
    public function getCollection()
    {
        $params = $this->getRequest()->getParams();
        $customerId = $params['id'];
        
        $collection = $this->getData('dataSource');
        
        $rConnection = $collection->getResource()->getConnection();
        
        $select = $rConnection->select();
        $select->from(
            ['msprofile' => $rConnection->getTableName('msprofile')],
            ['msquote_id']
        );
        $select->joinLeft(
            ['msquote', $rConnection->getTableName('msquote')],
            'msprofile.msquote_id = msquote.entity_id',
            []
        )->where('msquote.customer_id='.$customerId);
        
        $result = $rConnection->fetchAll($select);
        
        $collection->addFieldToFilter('msquote_id', ['in' => $result]);
        
        return $collection;
    }
}
