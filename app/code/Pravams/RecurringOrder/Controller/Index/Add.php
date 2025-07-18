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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Pravams\RecurringOrder\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\LocalizedToNormalized;

class Add extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator $_formKeyValidator
     */
    protected $_formKeyValidator;
    
    /**
     * @var CustomerCart $cart
     */
    protected $cart;
    
    /**
     * @var \Magento\Customer\Model\Session $customerSession
     */
    protected $customerSession;
    
    /*
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        ProductRepositoryInterface $productRepository,
        CustomerCart $cart,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_formKeyValidator = $formKeyValidator;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context
        );
    }
    
    /*
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_objectManager->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }
    
    /*
     * Add product to subscription
     */
    public function execute()
    {
        
        if (!$this->customerSession->isLoggedIn()) {
            return $this->_redirect('customer/account/login/');
        }
        
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $params = $this->getRequest()->getParams();
        
        try {
            if (isset($params['qty'])) {
                $filter = new LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get(
                        \Magento\Framework\Locale\ResolverInterface::class
                    )->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }
            
            $product = $this->_initProduct();
            
            /*
             * Check product availability
             */
            if (!$product || !$product->getIsSalable()) {
                return $this->goBack();
            }
            
            $this->cart->addProduct($product, $params);
            //$this->cart->save();
            
        } catch (\Exception $e) {
            return $this->goBack();
        }
        //var_dump("hihi addd");print_r($params);exit;
        $this->_redirect('checkout/cart');
    }
    
    protected function goBack()
    {
        $this->_redirect('checkout/cart');
    }
}
