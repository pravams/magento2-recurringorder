<?php

/**
 * Pravams RecurringOrder Module
 *
 * @category    Pravams
 * @package     Pravams_RecurringOrder
 * @copyright   Copyright (c) 2018 Pravams. (http://pravams.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pravams\RecurringOrder\Model\Session;

class Storage extends \Magento\Framework\Session\Storage
{
    public function __construct(
        $namespace = 'RecurringOrder',
        array $data = []
    ) {
        parent::__construct($namespace, $data);
    }
}
