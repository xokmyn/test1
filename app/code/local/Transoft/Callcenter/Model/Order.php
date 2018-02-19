<?php

/**
 * Callcenter Order model
 *
 * @category    Transoft
 * @package     Transoft_Callcenter
 */
class Transoft_Callcenter_Model_Order extends Transoft_Callcenter_Model_Callcenter
{
    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_initiatorInstance = null;

    /**
     * constructor
     *
     * @access public
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('sales/order');
    }

    /**
     * Set initiator_id for order to NULL
     *
     * @param int||array $orderId
     * @return void
     */
    public function removeInitiator($orderIds)
    {
        $data = ['initiator_id' => null];
        try {
            Mage::getResourceModel('transoft_callcenter/order')->updateOrderField($orderIds, $data);
            //remove from relation table
            Mage::getResourceSingleton('transoft_callcenter/order_initiator')
                ->deleteOrderRelation($orderIds);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Get default data for flat order table with field initiator_id
     *
     * @return array
     */
    protected function getDefaultData()
    {
        return ['initiator_id' => null];
    }
}
