<?php

/**
 * Adminhtml observer
 *
 * @category    Transoft
 * @package     Transoft_Callcenter
 */
class Transoft_Callcenter_Model_Adminhtml_Observer extends Transoft_Callcenter_Model_Initiator
{

    /**
     * Save order - initiator relation before order save
     *
     * @param Varien_Event_Observer $observer
     */
    public function addInitiatorId($observer)
    {
        if ($this->checkIsCallcenter()) {
            $order = $observer->getOrder();
            $orderId = $order->getId();
            /** @var Transoft_Callcenter_Model_Initiator $initiatorModel */
            $initiatorModel = Mage::getSingleton('transoft_callcenter/initiator');
            $userId = $initiatorModel->getCallcenterUserId();
            /**
             * if order was reorder
             */
            if (!$orderId) {
                $order->setData('callcenter_user', $userId);
            }
            if ($orderId && !$this->checkIsOrderInInitiator($order)) {
                $errorMsg = Mage::helper('transoft_callcenter')->__('Не ваш заказ');
                $errorLog = '["user_id" => ' . $userId . ', "order_id" => ' . $orderId . ']';
                Mage::log($errorLog, null, 'callcenter.log');
                Mage::throwException($errorMsg);
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
        if ($this->checkIsCallcenter()) {
            $order = $observer->getOrder();
            $orderId = $order->getId();
            if ($orderId && $order->getStatus() &&$order->getStatus() !== 'pending') {
                $this->saveOrderInitiator($orderId, false);
            } elseif ($order->getData('callcenter_user')) {
                /** @var Transoft_Callcenter_Model_Initiator $initiatorModel */
                $initiatorModel = Mage::getSingleton('transoft_callcenter/initiator');
                $userId = $initiatorModel->getCallcenterUserId();
                $initiatorModel->addData([
                    'initiator_id'  => $userId,
                    'order_id' => $orderId,
                    'status' => 1,
                    'position' => 1
                ]);
                try {
                    $initiatorModel->save();
                } catch (Exception $e) {
                    Mage::log($e->getMessage());
                }
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
        Mage::getSingleton('transoft_callcenter/initiator')->saveOrderWithProductSetToInitiator();
    }

    /**
     * Add callcenter type fields to admin user form
     *
     * @param Varien_Event_Observer $observer
     * @return Transoft_Callcenter_Model_Adminhtml_Observer
     */
    public function addCallcenterTypeField(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (null === $block) {
            return $this;
        }
        if ($block->getType() === 'adminhtml/permissions_user_edit_tab_main') {
            $form = $block->getForm();
            $fieldset = $form->addFieldset('priority', array(
                'legend' => Mage::helper('adminhtml')->__('Priority user'),
                'class' => 'fieldset-wide'
            ));
            $fieldset->addField('callcenter_type', 'select', array(
                'name' => 'callcenter_type',
                'label' => Mage::helper('transoft_callcenter')->__('Priority'),
                'id' => 'callcenter_type',
                'title' => Mage::helper('transoft_callcenter')->__('Priority'),
                'class' => 'input-select',
                'style' => 'width: 80px',
                'options' => $this->getCallcenterTypeOptions(),
                'value'   => $this->getCallcenterTypeValue()
            ));
        }
    }

    /**
     * Get options for callcenter_type
     */
    public function getCallcenterTypeOptions()
    {
        $options = Mage::helper('transoft_callcenter')->convertOptions(
            Mage::getModel('transoft_callcenter/initiator_type')->getAllOptions(true)
        );
        return $options;
    }

    /**
     * Get options for callcenter_type
     */
    public function getCallcenterTypeValue()
    {
        return Mage::registry('permissions_user')->getCallcenterType();
    }
}
