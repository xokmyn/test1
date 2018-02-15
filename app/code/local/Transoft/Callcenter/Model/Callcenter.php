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
    protected $_isCallcenter        = null;

    /**
     * Callcenter user
    */
    protected $_callcenterUser      = null;

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        if(!$this->_isCallcenter)
        {
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
        return  Mage::getSingleton('admin/session')->isAllowed('admin/transoft_callcenter/initiator/' . $action);
    }

    /**
     * Init current users for callcenter
     */
    protected function _initCallcenterUser()
    {
        $role_name  = null;
        if($this->_isAllowedAction('actions'))
        {
            $user = Mage::getSingleton('admin/session')->getUser();
            $role = $user->getRole();
            $callcenter_role_source = Mage::getModel("transoft_callcenter/initiator_source");
            $this->_isCallcenter = (in_array($role->getRoleId(), $callcenter_role_source->getCallcenterRoleIds()));
            $callcenterRoleName = ($this->_isCallcenter) ? $role->getRoleName() : null;
            $user->setData("callcenter_role", $callcenterRoleName);
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
        $result = true;
        if($order->getInitiatorId() && $order->getInitiatorId() != $this->_callcenterUser->getUserId())
        {
            return false;
        }

        return $result;
    }

    /**
     * Save order - initiator relation
     *
     * @access public
     * @param int $orderId
     * @param bool $status
     * @return Transoft_Callcenter_Model_Callcenter
     */
    public function saveOrderInitiator($orderId, $status = true)
    {
        $data[$this->_callcenterUser->getUserId()] = array(
            'status'        =>  $status,
        );

        Mage::dispatchEvent(
            'transoftg_callcenter_adminhtml_order_initiator_save_before',
            array(
                'order' => $orderId,
                'data'  => $data,
            )
        );

        Mage::getResourceSingleton('transoft_callcenter/order_initiator')
            ->saveOrderRelation($orderId, $data);

        return $this;
    }
}
