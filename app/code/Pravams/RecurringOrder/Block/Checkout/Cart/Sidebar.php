<?php

/**
 * Pravams RecurringOrder Module
 *
 * @category    Pravams
 * @package     Pravams_RecurringOrder
 * @copyright   Copyright (c) 2018 Pravams. (http://pravams.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pravams\RecurringOrder\Block\Checkout\Cart;

use Pravams\RecurringOrder\Model\Session;

class Sidebar extends \Magento\Framework\View\Element\Template
{
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        Session $subsriptionSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->subsriptionSession = $subsriptionSession;
        parent::__construct($context, $data);
    }
    
    public function getMsQuote()
    {
        $msquoteId=$this->subsriptionSession->getMsQuoteId();
        return $msquoteId;
    }
}
