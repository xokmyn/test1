<?php

/**
 * Callcenter Initiator model
 *
 * @category    Transoft
 * @package     Transoft_Callcenter
 */
class Transoft_Callcenter_Model_Initiator extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'transoft_callcenter_initiator';
    const CACHE_TAG = 'transoft_callcenter_initiator';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'transoft_callcenter_initiator';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'initiator';

    /**
     * @var array
     */
    private $processUserOrder = [];

    /**
     * @return array
     */
    public function getProcessUserOrder()
    {
        return $this->processUserOrder;
    }

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
     * before save initiator order
     *
     * @access protected
     * @return Transoft_Callcenter_Model_Initiator
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
        return $this;
    }

    /**
     * save initiator order relation
     *
     * @access public
     * @return Transoft_Callcenter_Model_Initiator
     */
    protected function _afterSave()
    {
        return parent::_afterSave();
    }

    /**
     * Is user from callcenter role
     */
    protected $_isCallcenter = null;

    /**
     * Callcenter user
     */
    protected $_callcenterUser = null;

    /**
     * Check is allowed actions for callcenter
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/transoft_callcenter/initiator/' . $action);
    }

    /**
     * Init current users for callcenter
     */
    protected function _initCallcenterUser()
    {
        $role_name = null;
        if ($this->_isAllowedAction('actions')) {
            $user = Mage::getSingleton('admin/session')->getUser();
            $role = $user->getRole();
            $callcenterRoleSource = Mage::getModel('transoft_callcenter/initiator_source');
            $this->_isCallcenter = (in_array($role->getRoleId(), $callcenterRoleSource->getCallcenterRoleIds()));
            $callcenterRoleName = $this->_isCallcenter ? $role->getRoleName() : null;
            $user->setData('callcenter_role', $callcenterRoleName);
            $this->_callcenterUser = $user;
        }

        return $this;
    }

    /**
     * Get callcenter user
     */
    public function callcenterUser()
    {
        $this->_initCallcenterUser();
        return $this->_callcenterUser;
    }

    /**
     * Check if user in callcenter roles
     */
    public function isCallcenter()
    {
        $this->_initCallcenterUser();
        return $this->_isCallcenter;
    }

    /**
     * Check is order in current user
     *
     * @param Mage_Sales_Model_Order
     * @return bool
     */
    public function checkIsOrderInInitiator(Mage_Sales_Model_Order $order)
    {
        $userId = $this->_callcenterUser->getUserId();
        if ((int)$order->getData('callcenter_user') === (int)$userId) {
            $result = true;
        } else {
            $item = $this->getCollection()
                ->addFieldToFilter('order_id', $order->getEntityId())
                ->addFieldToFilter('initiator_id', $userId)
                ->getFirstItem();
            $result = $item ? true : false;
        }
        return $result;
    }

    /**
     * Save order - initiator relation
     *
     * @access public
     * @param int $orderId
     * @param bool $status
     * @param int $initiatorId
     * @return Transoft_Callcenter_Model_Initiator
     */
    public function saveOrderInitiator($orderId, $status = true, $initiatorId = null)
    {
        if (!$initiatorId) {
            $initiatorId = $this->_callcenterUser->getUserId();
        }
        $data[$initiatorId] = array(
            'status' => $status,
        );
        $this->setData('order_id', $orderId);
        $this->addData($data);
        try {
            $this->save();
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
        return $this;
    }

    /**
     * Save initiator - order relation
     *
     * @access public
     * @param int $initiator_id
     * @param int $position
     * @return Transoft_Callcenter_Model_Initiator
     */
    public function saveInitiatorPosition($initiator_id = 0, $position = 0)
    {
        $position = $position ?: $this->getNextPosition();
        $initiator_id = $initiator_id ?: $this->_callcenterUser->getUserId();
        $data[0] = array(
            'position' => $position
        );
        $this->setData('initiator_id', $initiator_id);
        $this->addData($data);
        try {
            $this->save();
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
        return $this;
    }

    /**
     * Get next position in queue for user
     *
     * @return int
     */
    protected function getNextPosition()
    {
        $lastPosition = Mage::getResourceSingleton('transoft_callcenter/initiator_order')->getLastPosition();
        return $lastPosition + 1;
    }

    /////////////////////////

    /**
     * Check is user used Ca
     * llcenter
     */
    public function isCallcenterUser()
    {
        return $this->isCallcenter();
    }

    /**
     * Get callcenter user name
     */
    public function getCallcenterUserRoleName()
    {
        return $this->callcenterUser()->getData('callcenter_role');
    }

    /**
     * get callcenter user ID
     */
    public function getCallcenterUserId()
    {
        return $this->callcenterUser()->getUserId();
    }

    /**
     * Save initiator with position value
     * @param int
     */
    public function addInitiatorToPosition($initiatorId = 0)
    {
        $this->saveInitiatorPosition($initiatorId);
    }

    /**
     * Get order ids is from relation tables with status "1"
     */
    protected function getExcludeOrderIds()
    {
        $orderIds = array_unique(
            $this->getCollection()->addFieldToFilter('status', 1)->addFieldToSelect('order_id')
        );
        return $orderIds;
    }

    /**
     * Get order collection for using in callcenter
     *
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    public function getNewOrderCollection()
    {
        /** @var Mage_Sales_Model_Resource_Order_Collection $orders */
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_NEW);
        $excludeIds = $this->getExcludeOrderIds();
        if ($excludeIds) {
            $orders->getSelect()->where('main_table.entity_id NOT IN(?)', $excludeIds);
        }
        return $orders;
    }

    /**
     * Get order ids for callcenter using
     * @return array
     */
    public function getNewOrderIds()
    {
        $collection = $this->getNewOrderCollection();
        $orderIds = $collection->getAllIds();
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
     * @param array $orderIds
     */
    public function saveOrderWithProductSetToInitiator($orderIds = [])
    {
        $processData = [];
        $users = $this->getUserWithTypeInQueue();
        $orderIds = $orderIds ?: $this->getNewOrderIds();
        if ($users) {
            $attributeSetId = Mage::getModel('eav/entity_attribute_set')
                ->getCollection()
                ->addFieldToFilter('attribute_set_name', 'Format type')
                ->getFirstItem()
                ->getAttributeSetId();
            foreach ($users as $user) {
                $data = [];
                $type = (int)$user['callcenter_type'];
                $orderId = $this->_getOrderForInitiator($orderIds, $attributeSetId, $type) ?: 0;
                if ($orderId) {
                    $data[$orderId] = ['status' => true, 'position' => 1];
                    $this->saveInitiatorOrderRelation($user['initiator_id'], $data);
                    $processData[$orderId] = $user['initiator_id'];
                }
                unset($orderIds[array_search($orderId, $orderIds)]);
            }
        }
        $this->processUserOrder = $processData;
    }

    /**
     * Get order for user
     *
     * @param  array $orderIds
     * @param int $attributeSetId for product
     * @param int $type
     *
     * @return int $order_id
     */
    protected function _getOrderForInitiator($orderIds, $attributeSetId, $type)
    {
        foreach ($orderIds as $k => $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if (!$type) {
                return $orderId;
            }
            foreach ($order->getAllItems() as $item) {
                $product = $item->getProduct();
                if ((int)$product->getAttributeSetId() === (int)$attributeSetId
                    && (int)$product->getData('callcenter_format_type') === (int)$type) {
                    return $orderId;
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
     * @param string $optionCode
     * @return int||null
     */
    public function getTypeOptionValue($optionCode)
    {
        $attributeOptions = Mage::getModel('transoft_callcenter/initiator_type')
            ->getAllOptions();
        $attributeOptions = Mage::helper('transoft_callcenter')->convertOptions($attributeOptions);

        return array_flip($attributeOptions)[$optionCode];
    }

    /**
     * Remove pairs initiator-order from table
     *
     * @param int||array $orderId
     * @return void
     */
    public function removeInitiator($orderIds)
    {
        try {
            Mage::getResourceSingleton('transoft_callcenter/initiator_order')
                ->deleteOrderRelation($orderIds);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }




    /////////////////////////////////////

    /**
     * get default values
     *
     * @access public
     * @return array
     */
    public function getDefaultValues()
    {
        $values = array();
        $values['status'] = 1;
        $values['position'] = '1';

        return $values;
    }
}
