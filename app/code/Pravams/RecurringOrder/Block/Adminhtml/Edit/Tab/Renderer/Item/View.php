<?php

/**
 * Pravams RecurringOrder Module
 *
 * @category    Pravams
 * @package     Pravams_RecurringOrder
 * @copyright   Copyright (c) 2018 Pravams. (http://pravams.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pravams\RecurringOrder\Block\Adminhtml\Edit\Tab\Renderer\Item;

class View extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $item)
    {
        $this->setItem($item);
        $url = $this->getUrl('RecurringOrder/index/view/id/'.$item->getId());
        $html = "<a href=\"#\" class=\"view_subscription\" id=\"".$item->getId()."\" url=\"".$url."\">view</a>";
        return $html;
    }
}
