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

use Pravams\RecurringOrder\Model\MsQuote;
use Pravams\RecurringOrder\Model\Session\Storage;

class Session extends \Magento\Framework\Session\SessionManager
{
    /*
     * MsQuote instance
     * @var MsQuote
     */
    protected $_msquote;
    
    /*
     * Customer Session
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /*
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /*
     * @var \Magento\MsQuote\Model\MsQuoteFactory
     */
    protected $msquoteFactory;
    
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        MsQuote $msquote
        //\Magento\MsQuote\Model\MsQuoteFactory $msquoteFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_msquote = $msquote;
        //$this->msquoteFactory = $msquoteFactory;
        
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState
        );
    }
    
    public function getMsQuote()
    {
        return $this->_msquote;
    }
    
    public function setMsQuoteId($quoteId)
    {
        $this->storage->setData('msquote_id', $quoteId);
        return $this;
    }
    
    public function getMsQuoteId()
    {
        return $this->storage->getData('msquote_id');
    }
}
