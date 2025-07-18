<?php

/**
 * Pravams RecurringOrder Module
 *
 * @category    Pravams
 * @package     Pravams_RecurringOrder
 * @copyright   Copyright (c) 2018 Pravams. (http://pravams.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pravams\RecurringOrder\Controller\Adminhtml\Index;

class Change extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    protected $resultRawFactory;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
            $this->_objectManager = $objectManager;
            $this->resultRawFactory = $resultRawFactory;
            parent::__construct($context);
    }

    public function execute()
    {
            
            $params = $this->getRequest()->getParams();
            $profileId = $params['subscription_id'];
            $status = $params['subscription_status_content'];
            
            $msProfileModel = $this->_objectManager->create('Pravams\RecurringOrder\Model\MsProfile');
            $msProfileModel->getResource()->load($msProfileModel, $profileId);
            $msQuoteId = $msProfileModel->getMsquoteId();
            
        if ($status == "active") {
            $msProfileModel->setStatus('active')
                    ->save();
        } elseif ($status == "inactive") {
            $msProfileModel->setStatus('inactive')
                    ->save();
        } elseif ($status == "delete") {
            $msProfileModel->delete();
        }
            
            $respData = $status;
            $result = $this->resultRawFactory->create();
            $result->setContents($respData);
            return $result;
    }
}
