<?php

/**
 * Pravams RecurringOrder Module
 *
 * @category    Pravams
 * @package     Pravams_RecurringOrder
 * @copyright   Copyright (c) 2018 Pravams. (http://pravams.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pravams\RecurringOrder\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;

class RecurringOrder extends \Magento\Framework\View\Element\Template
{
    
    protected $_coreRegistry;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::_construct($context, $data);
    }
    
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }
    
    public function getTabLabel()
    {
        return __('My Subscription');
    }
    
    public function getTabTitle()
    {
        return __('My Subscription');
    }
    
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }
    
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }
    
    public function getTabClass()
    {
        return '';
    }
    
    public function getTabUrl()
    {
        return '';
        //return $this->getUrl('RecurringOrder/index/RecurringOrder', ['_current' => true]);
    }
        
    public function isAjaxLoaded()
    {
        return true;
    }
}
