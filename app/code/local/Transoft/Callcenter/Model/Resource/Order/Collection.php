<?php
/**
 * Order collection resource model
 *
 * @category    Transoft
 * @package     Transoft_Callcenter
 */
class Transoft_Callcenter_Model_Resource_Order_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * constructor
     *
     * @access public
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('sales/order');
    }
}