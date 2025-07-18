<?php

/**
 * Pravams RecurringOrder Module
 *
 * @category    Pravams
 * @package     Pravams_RecurringOrder
 * @copyright   Copyright (c) 2018 Pravams. (http://pravams.wordpress.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pravams\RecurringOrder\Model\ResourceModel;

class MsProfile extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('msprofile', 'profile_id');
    }
    
    public function getMsQuoteProfileFromMsQuoteId($msquoteId, $msProfile)
    {
        $connection = $this->getConnection();
        $select = parent::_getLoadSelect('msquote_id', $msquoteId, $msProfile);
        $data = $connection->fetchRow($select);
        if ($data) {
            $msProfile->setData($data);
        }
        return $msProfile;
    }
}
