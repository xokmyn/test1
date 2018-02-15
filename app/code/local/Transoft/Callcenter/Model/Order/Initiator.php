<?php
/**
 * Order initiator model
 *
 * @category    Transoft
 * @package     Transoft_Callcenter
 */
class Transoft_Callcenter_Model_Order_Initiator extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource
     *
     * @access protected
     * @return void
     */
    protected function _construct()
    {
        $this->_init('transoft_callcenter/order_initiator');
    }
}
