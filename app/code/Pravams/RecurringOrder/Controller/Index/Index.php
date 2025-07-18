<?php

/**
 * Pravams RecurringOrder Module
 *
 * @category    Pravams
 * @package     Pravams_RecurringOrder
 * @copyright   Copyright (c) 2018 Pravams. (http://pravams.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pravams\RecurringOrder\Controller\Index;

use Magento\Framework\App\Action\Context;
use Pravams\RecurringOrder\Model\Session;

class Index extends \Magento\Framework\App\Action\Action
{
    
    protected $_resultPageFactory;
    
    /**
     * @var \Magento\Customer\Model\Session $customerSession
     */
    protected $customerSession;
    
    /**
     * @var Session $subscriptionSession
     */
    protected $subscriptionSession;
    
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        Session $subscriptionSession
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->subscriptionSession = $subscriptionSession;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $msquoteId=$this->subscriptionSession->getMsQuoteId();
        // check that the user is logged in
        if (!$this->customerSession->isLoggedIn() || !$msquoteId) {
            return $this->_redirect('customer/account/login/');
        }
        
        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }
}
