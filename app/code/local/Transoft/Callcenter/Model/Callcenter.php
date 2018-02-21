<?php

/**
 * Callcenter Model
 *
 * @category    Transoft
 * @package     Transoft_Callcenter
 */
abstract class Transoft_Callcenter_Model_Callcenter extends Mage_Core_Model_Abstract
{
    /**
     * Is user from callcenter role
     */
    protected $_isCallcenter = null;

    /**
     * Callcenter user
     */
    protected $_callcenterUser = null;

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        if (!$this->_isCallcenter) {
            $this->_initCallcenterUser();
        }
    }

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
            $result = Mage::getResourceSingleton('transoft_callcenter/initiator_order')
                ->getOrderInitiatorRelation($order->getEntityId(), $userId) ? true : false ;
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
     * @return Transoft_Callcenter_Model_Callcenter
     */
    public function saveOrderInitiator($orderId, $status = true, $initiatorId = null)
    {
        if (!$initiatorId) {
            $initiatorId = $this->_callcenterUser->getUserId();
        }
        $data[$initiatorId] = array(
            'status' => $status,
        );
        Mage::getResourceSingleton('transoft_callcenter/initiator_order')
            ->saveOrderRelation($orderId, $data);

        return $this;
    }

    /**
     * Save initiator - order relation
     *
     * @access public
     * @param int $initiator_id
     * @param int $position
     * @return Transoft_Callcenter_Model_Callcenter
     */
    public function saveInitiatorPosition($initiator_id = 0, $position = 0)
    {
        $position = $position ?: $this->getNextPosition();
        $initiator_id = $initiator_id ?: $this->_callcenterUser->getUserId();
        $data[0] = array(
            'position' => $position
        );

        Mage::getResourceSingleton('transoft_callcenter/initiator_order')
            ->saveInitiatorRelation($initiator_id, $data);

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
}
