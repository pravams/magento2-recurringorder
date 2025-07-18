<?php

/**
 * Pravams RecurringOrder Module
 *
 * @category    Pravams
 * @package     Pravams_RecurringOrder
 * @copyright   Copyright (c) 2018 Pravams. (http://pravams.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Pravams\RecurringOrder\Block\Adminhtml\Edit\Tab\Renderer;

class Item extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $item)
    {
        $this->setItem($item);
        if ($item->getType() == "everyxy") {
            return "Every ".$item->getTypeX()." ".$item->getTypeY();
        } else {
            return $item->getType();
        }
    }
}
