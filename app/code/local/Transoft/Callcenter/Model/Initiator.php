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
    protected $eventPrefix = 'transoft_callcenter_initiator';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $eventObject = 'initiator';

    /**
     * @var array
     */
    private $processUserOrder = [];

    /**
     * Is user from callcenter role
     */
    protected $isCallcenter;

    /**
     * Callcenter user
     */
    protected $callcenterUser;

    /**
     * @return array
     */
    public function getProcessUserOrder()
    {
        return $this->processUserOrder;
    }

    /**
     * Get callcenter user name
     */
    public function getCallcenterUserRoleName()
    {
        return $this->getCallcenterUser()->getData('callcenter_role');
    }

    /**
     * get callcenter user ID
     */
    public function getCallcenterUserId()
    {
        return $this->getCallcenterUser()->getUserId();
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
     * Check is allowed actions for callcenter
     *
     * @param string $action
     * @return bool
     */
    protected function isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/transoft_callcenter/initiator/' . $action);
    }

    /**
     * Init current users for callcenter
     */
    protected function initCallcenterUser()
    {
        $role_name = null;
        if ($this->isAllowedAction('actions')) {
            $user = Mage::getSingleton('admin/session')->getUser();
            $role = $user->getRole();
            $callcenterRoleSource = Mage::getModel('transoft_callcenter/initiator_source');
            $this->isCallcenter = (in_array($role->getRoleId(), $callcenterRoleSource->getCallcenterRoleIds()));
            $callcenterRoleName = $this->isCallcenter ? $role->getRoleName() : null;
            $user->setData('callcenter_role', $callcenterRoleName);
            $this->callcenterUser = $user;
        }
        return $this;
    }

    /**
     * Get callcenter user
     */
    public function getCallcenterUser()
    {
        if (!$this->callcenterUser) {
            $this->initCallcenterUser();
        }
        return $this->callcenterUser;
    }

    /**
     * Check is user used Callcenter
     */
    public function checkIsCallcenter()
    {
        if (!$this->isCallcenter) {
            $this->initCallcenterUser();
        }
        return $this->isCallcenter;
    }

    /**
     * Check is order in current user
     *
     * @param Mage_Sales_Model_Order
     * @return bool
     */
    public function checkIsOrderInInitiator(Mage_Sales_Model_Order $order)
    {
        $userId = $this->getCallcenterUser()->getUserId();
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
            $initiatorId = $this->getCallcenterUser()->getUserId();
        }
        $data = array(
            'initiator_id' => $initiatorId,
            'status' => $status,
        );
        $item = $this->getCollection();
        $item->addFieldToFilter('initiator_id', $data['initiator_id']);
        $item->addFieldToFilter('order_id', $orderId);
        $model  = $item->getFirstItem();
        $model->addData($data);
        try {
            $model->save();
        } catch (Exception $e) {
            Mage::exception($e);
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
        $initiator_id = $initiator_id ?: $this->getCallcenterUser()->getUserId();
        $data = array(
            'order_id' => 0,
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
        $collection = $this->getCollection()
            ->addFieldToSelect('position')
            ->addFieldToFilter('order_id', 0)
            ->setOrder('position', 'DESC');
        $lastPosition = $collection->getFirstItem()->getData('position');
        return $lastPosition + 1;
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
        return $collection->getAllIds();
    }

    /**
     * Get pending order for user
     *
     * @return Mage_Sales_Model_Order
     */
    public function getPendingOrder()
    {
        $userId = $this->getCallcenterUser()->getUserId();
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
     * @param array $userIds
     * @return array
     */
    public function getUserWithTypeInQueue(array $userIds = [])
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('status', 1);
        $collection->addFieldToFilter('order_id', 0);
        if ($userIds) {
            $collection->addFieldToFilter('initiator_id', array('in' => $userIds));
        }
        $collection->getSelect()->joinLeft(
            array(
                'admin' => 'admin_user'
            ),
            'initiator_id = admin.user_id',
            array('callcenter_type')
        );
        return $collection->getItems();
    }

    /**
     * Save order to initiator-order table
     * @param array $orderIds
     */
    public function saveOrderWithProductSetToInitiator(array $orderIds = [])
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
                $type = (int)$user['callcenter_type'];
                $orderId = $this->getOrderForInitiator($orderIds, $attributeSetId, $type) ?: 0;
                if ($orderId) {
                    $data = [
                        'initiator_id' => $user['initiator_id'],
                        'order_id' => $orderId,
                        'status' => 1,
                        'position' => 1
                    ];
                    $this->saveInitiatorOrderRelation($data);
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
    protected function getOrderForInitiator($orderIds, $attributeSetId, $type)
    {
        foreach ($orderIds as $k => $orderId) {
            /** @var Mage_Sales_Model_Order $order */
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
     * @param array $data
     */
    protected function saveInitiatorOrderRelation($data)
    {
        $item = $this->getCollection();
        $item->addFieldToFilter('initiator_id', $data['initiator_id']);
        $item->addFieldToFilter('order_id', 0);
        $model  = $item->getFirstItem();
        $model->addData($data);
        try {
            $model->save();
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
        /** @var Transoft_Callcenter_Model_Initiator_Type $$sourceModel */
        $sourceModel = Mage::getModel('transoft_callcenter/initiator_type');
        $attributeOptions = $sourceModel->getAllOptions();
        /** @var Transoft_Callcenter_Helper_Data $helper */
        $helper = Mage::helper('transoft_callcenter');
        $attributeOptions = $helper->convertOptions($attributeOptions);
        return array_flip($attributeOptions)[$optionCode];
    }

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
