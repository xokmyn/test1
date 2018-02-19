<?php

/**
 * Order resource model
 *
 * @category    Transoft
 * @package     Transoft_Callcenter
 */
class Transoft_Callcenter_Model_Resource_Order extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * constructor
     */
    public function _construct()
    {
        $this->_init('sales/order', 'entity_id');
    }
}
