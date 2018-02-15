<?php
/**
 * Initiator order model
 *
 * @category    Transoft
 * @package     Transoft_Callcenter
 */
class Transoft_Callcenter_Model_Initiator_Order extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource
     *
     * @access protected
     * @return void
     */
    protected function _construct()
    {
        $this->_init('transoft_callcenter/initiator_order');
    }

    /**
     * Get order ids with filter by status initiator
     *
     * @param int $status
     * @return array
    */
    public function initiatorOrdersIdsFilterStatus($status = 1)
    {
        $orderIds = Mage::getResourceModel('transoft_callcenter/initiator_order')
            ->initiatorStatusFilter($status);
        return $orderIds;
    }
}
