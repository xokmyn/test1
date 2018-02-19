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
     */
    public function addInitiatorId($observer)
    {
        if ($this->_isCallcenter) {
            $order = $observer->getOrder();
            $orderId = $order->getId();
            /**
             * if order was reorder
            */
            if (!$orderId) {
                $orderId = Mage::getSingleton('admin/session')->getCallcenterOrderId();
                $this->saveOrderInitiator($orderId, false);
            }
            if (!$this->checkIsOrderInInitiator($order)) {
                $user_id   = Mage::getSingleton('admin/session')->getUser()->getId();
                $error_msg = Mage::helper('transoft_callcenter')->__('Не ваш заказ');
                $error_log = '["user_id" => '.$user_id .', "order_id" => '.$orderId.']';
                Mage::log($error_log, null, 'callcenter.log');
                Mage::throwException($error_msg);
            }
        }
    }

    /**
     * Save order - initiator relation after order save
     * if  order status == pending, status  = 1
     *
     * @param Varien_Event_Observer $observer
     */
    public function afterSaveOrder($observer)
    {
        $order = $observer->getOrder();
        $orderId = $order->getId();
        if ($orderId && $order->getStatus() !== 'pending') {
            $this->saveOrderInitiator($orderId, false);
        }
    }

    /**
     * Assignment order ID to callcenter users
     *
     * @param Mage_Cron_Model_Schedule $schedule
     */
    public function cronAssignmentOrderId($schedule)
    {
        Mage::getModel('transoft_callcenter/initiator')->saveOrderWithProductSetToInitiator();
    }
}
