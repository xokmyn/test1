<?php
/**
 * Callcenter Initiator model
 *
 * @category    Transoft
 * @package     Transoft_Callcenter
 */
class Transoft_Callcenter_Model_Initiator extends Transoft_Callcenter_Model_Callcenter
{
    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_orderInstance = null;

    /**
     * constructor
     *
     * @access public
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('transoft_callcenter/initiator');
    }

    /**
     * Check is user used Callcenter
    */
    public function isCallcenterUser()
    {
        return $this->_isCallcenter;
    }

    /**
     * Get callcenter user name
     */
    public function getCallcenterUserRoleName()
    {
        return $this->_callcenterUser->getData("callcenter_role");
    }

    /**
     * get callcenter user ID
     */
    public function getCallcenterUserId()
    {
        return $this->_callcenterUser->getUserId();
    }

    /**
     * get order relation model
     *
     * @access public
     * @return Transoft_Callcenter_Model_Initiator_Order
     */
    public function getOrderInstance()
    {
        if (!$this->_orderInstance) {
            $this->_orderInstance = Mage::getSingleton('transoft_callcenter/initiator_order');
        }
        return $this->_orderInstance;
    }

    /**
     * Get order id for initiator and save to relation table "transoft_callcenter_initiator_order"
     *
     * @return int
     */
    public function getInitiatorOrderId()
    {
        $orderId    = $this->_getRandomOrderId();
        if($this->_callcenterUser->getUserId())
        {
            $this->saveOrderInitiator($orderId);
        }

        return $orderId;
    }

    /**
     * Get random order with " initiator_id => null " filter
     *
     * @return int $order_id
     */
    protected function _getRandomOrderId()
    {
        $orders = Mage::getResourceModel("transoft_callcenter/order_collection")
            ->addFieldToFilter('initiator_id', ['null' => true])
            ->addFieldToFilter('status', 'new');;
        $excludeIds = $this->getExcludeOrderIds();
        if ($excludeIds) {
            $orders->getSelect()->where('main_table.entity_id NOT IN(?)', $excludeIds);
        }
        $orders->getSelect()->order(new Zend_Db_Expr('RAND()'));
        $orders->getSelect()->limit(1);
        $order_id = (int)$orders->getColumnValues('entity_id')[0];

        return $order_id;
    }

    /**
     * Get order ids is from relation tables with status "1"
     */
    protected function getExcludeOrderIds()
    {
        $orderIds = $this->getOrderInstance()->initiatorOrdersIdsFilterStatus(true);

        return $orderIds;
    }

    /**
     * Get pending order for user
     *
     * @return Mage_Sales_Model_Order
     */
    public function getPendingOrder()
    {
        $userId = $this->_callcenterUser->getUserId();
        $order = Mage::getModel('sales/order')->getCollection()
            ->addFieldToSelect(["entity_id", "increment_id"])
            ->addFieldToFilter('initiator_id', $userId)
            ->addFieldToFilter('status', 'pending')
            ->getFirstItem();

        return $order;
    }

}
