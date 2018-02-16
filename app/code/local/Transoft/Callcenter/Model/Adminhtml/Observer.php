<?php
/**
 * Adminhtml observer
 *
 * @category    Transoft
 * @package     Transoft_Callcenter
 */
class Transoft_Callcenter_Model_Adminhtml_Observer extends Transoft_Callcenter_Model_Callcenter
{

    /**
     * Save order - initiator relation before order save
     *
     * @param Varien_Event_Observer $observer
     * @access public
     */
    public function addInitiatorId($observer)
    {
        if($this->_isCallcenter)
        {
            $order = $observer->getOrder();
            if(!$order->getId())                                        // if order was reorder
            {
                $orderId = Mage::getSingleton('admin/session')->getCallcenterOrderId();
                Mage::getResourceModel("transoft_callcenter/order")
                    ->updateOrderField($orderId, ['initiator_id' => $this->_callcenterUser->getUserId()]);
                $this->saveOrderInitiator($orderId, false);
            }else{
                $orderId = $order->getId();
            }
            if($this->checkIsOrderInInitiator($order) || !$order->getId())
            {
                $order->setData('initiator_id', $this->_callcenterUser->getUserId());
            }elseif($order->getInitiatorId() && $orderId)               //if initiator_id !== current callcenter user
            {
                $user_id   = Mage::getSingleton('admin/session')->getUser()->getId();
                $error_msg = Mage::helper('transoft_callcenter')->__('Не ваш заказ');
                $error_log = '["user_id" => '.$user_id .', "order_id" => '.$orderId.']';
                Mage::log($error_log, null, 'callcenter.log');
                Mage::throwException($error_msg);
            };
        }
    }

    /**
     * Save order - initiator relation after order save
     * if  order status == pending, status  = 1
     *
     * @param Varien_Event_Observer $observer
     * @access public
     */
    public function afterSaveOrder($observer)
    {
        $order = $observer->getOrder();
        $orderId = $order->getId();
        if($orderId && $order->getInitiatorId() && $order->getStatus() !== 'pending')
        {
            $this->saveOrderInitiator($orderId, false);
        }
    }

    /**
     * Assignment order ID to callcenter users
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return Transoft_Callcenter_Model_Adminhtml_Observer
     */
    public function cronAssignmentOrderId($schedule)
    {

        return "Assignment Order for Callcenter User";
    }
}
