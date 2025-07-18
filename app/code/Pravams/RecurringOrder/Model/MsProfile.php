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

class MsProfile extends \Magento\Framework\Model\AbstractModel
{
    
    const ACTIVE = 'active';
    
    const INACTIVE = 'inactive';
    
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Pravams\RecurringOrder\Model\ResourceModel\MsProfile');
    }
    
    public function loadMsProfileFromMsQuote($msquoteId, $msProfile)
    {
        return $this->_getResource()->getMsQuoteProfileFromMsQuoteId($msquoteId, $msProfile);
    }
}
