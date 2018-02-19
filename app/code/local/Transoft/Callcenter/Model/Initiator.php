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

    protected $_initiatorOrderData = [];

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
        return $this->_callcenterUser->getData('callcenter_role');
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
        $this->saveInitiatorPosition();
    }

    /**
     * Get random order with " initiator_id => null " filter
     *
     * @return int $order_id
     */
    protected function _getRandomOrderId()
    {
        $orders = Mage::getResourceModel('transoft_callcenter/order_collection')
            ->addFieldToFilter('initiator_id', ['null' => true])
            ->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_NEW);
        $excludeIds = $this->getExcludeOrderIds();
        if ($excludeIds) {
            $orders->getSelect()->where('main_table.entity_id NOT IN(?)', $excludeIds);
        }
        $orders->getSelect()->order(new Zend_Db_Expr('RAND()'));
        $orders->getSelect()->limit(1);

        return (int)$orders->getColumnValues('entity_id')[0];
    }

    /**
     * Get order ids is from relation tables with status "1"
     */
    protected function getExcludeOrderIds()
    {
        $orderIds = array_unique($this->getOrderInstance()->getAllOrderIdsStatusEnabled(true));

        return $orderIds;
    }

    /**
     * Get order collection for using in callcenter
     */
    public function getNewOrderCollection()
    {
        $orders = Mage::getResourceModel('transoft_callcenter/order_collection')
            ->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_NEW);
        $excludeIds = $this->getExcludeOrderIds();
        if ($excludeIds) {
            $orders->getSelect()->where('main_table.entity_id NOT IN(?)', $excludeIds);
        }

        return $orders;
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
            ->addFieldToSelect(['entity_id', 'increment_id'])
            ->addFieldToFilter('initiator_id', $userId)
            ->addFieldToFilter('status', 'pending')
            ->getFirstItem();

        return $order;
    }

    /**
     * Add order with position for callcenter user
     *
     * @return array
     */
    public function getUserWithTypeInQueue()
    {
        $usersData = Mage::getResourceSingleton('transoft_callcenter/initiator_order')
            ->getInitiatorsOrderWithType();

        return $usersData;
    }

    /**
     * Save order to initiator-order table
     */
    public function saveOrderWithProductSetToInitiator()
    {
        $users = $this->getUserWithTypeInQueue();
        if ($users) {
            $collection = $this->getNewOrderCollection();

            $attributeSetId = Mage::getModel('eav/entity_attribute_set')
                ->getCollection()
                ->addFieldToFilter('attribute_set_name', 'Format type')
                ->getFirstItem()
                ->getAttributeSetId();

            foreach ($users as $user) {
                $data = [];
                $type = (int)$user['callcenter_type'];
                $orderId = $this->_getOrderForInitiator($collection, $attributeSetId, $type) ? : 0;
                $data[$orderId] = ['status' => true, 'position' => 1];
                $this->saveInitiatorOrderRelation($user['initiator_id'], $data);
                $collection->removeItemByKey($orderId);
            }
        }
    }

    /**
     * Get order for user
     *
     * @param  Transoft_Callcenter_Model_Resource_Order_Collection $collection orders
     * @param int $attributeSetId for product
     * @param int $type
     *
     * @return int $order_id
     */
    protected function _getOrderForInitiator($collection, $attributeSetId, $type)
    {
        foreach ($collection as $order) {
            $order_id = $order->getId();
            if (!$type) {
                return $order_id;
            }
            $order = Mage::getModel('sales/order')->load($order_id);
            foreach ($order->getAllItems() as $item) {
                $product = $item->getProduct();
                if ((int)$product->getAttributeSetId() === (int)$attributeSetId
                    && (int)$product->getData('callcenter_format_type') === (int)$type) {
                    return $order_id;
                }
            }
        }
    }

    /**
     * Save initiator order relation
     *
     * @param int $initiatorId
     * @param array $data
     */
    protected function saveInitiatorOrderRelation($initiatorId, $data)
    {
        try {
            Mage::getResourceSingleton('transoft_callcenter/initiator_order')
                ->saveInitiatorRelation($initiatorId, $data);
        } catch (Exception $e) {
            Mage::exception($e);
        }
    }

    /**
     * Get attribute options value
     *
     * @param string $option_code
     * @return int||null
     */
    public function getTypeOptionValue($option_code)
    {
        $attribute_options = Mage::getModel('transoft_callcenter/initiator_type')
            ->getAllOptions();
        $attribute_options = Mage::helper('transoft_callcenter')->convertOptions($attribute_options);

        return array_flip($attribute_options)[$option_code];
    }
}
