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
            /** @var Transoft_Callcenter_Model_Initiator $initiatorModel */
            $initiatorModel = Mage::getModel('transoft_callcenter/initiator');
            $userId = $initiatorModel->getCallcenterUserId();
            /**
             * if order was reorder
            */
            if (!$orderId) {
                /*$enabledOrderId = (int)Mage::getResourceModel('transoft_callcenter/initiator_order')
                    ->initiatorStatusFilter($userId, true);
                $this->saveOrderInitiator($enabledOrderId, true, $userId);*/
                $this->saveOrderInitiator(0, true, $userId);
                $order->setData('callcenter_user', $userId);
            }
            if ($orderId && !$this->checkIsOrderInInitiator($order)) {
                $error_msg = Mage::helper('transoft_callcenter')->__('Не ваш заказ');
                $error_log = '["user_id" => '.$userId .', "order_id" => '.$orderId.']';
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
        if ($this->_isCallcenter) {
            $order = $observer->getOrder();
            $orderId = $order->getId();
            if ($orderId && $order->getStatus() !== 'pending') {
                $this->saveOrderInitiator($orderId, false);
            } elseif ($order->getData('callcenter_user')) {
                /** @var Transoft_Callcenter_Model_Initiator $initiatorModel */
                $initiatorModel = Mage::getModel('transoft_callcenter/initiator');
                $userId = $initiatorModel->getCallcenterUserId();
                $data[$orderId] = ['status' => true, 'position' => 1];
                Mage::getResourceSingleton('transoft_callcenter/initiator_order')
                    ->saveInitiatorRelation($userId, $data);
            }
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
