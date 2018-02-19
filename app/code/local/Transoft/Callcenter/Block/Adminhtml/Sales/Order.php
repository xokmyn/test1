<?php

class Transoft_Callcenter_Block_Adminhtml_Sales_Order extends Mage_Adminhtml_Block_Sales_Order
{

    public function __construct()
    {
        parent::__construct();
        $this->checkAndAddButtons();
    }

    protected function checkAndAddButtons()
    {
        $model       = Mage::getModel('transoft_callcenter/initiator');
        if ($model->isCallcenterUser()
            && $model->getCallcenterUserRoleName() === Transoft_Callcenter_Model_Initiator_Source::OPERATOR) {
            $buttonData = $this->getButtonData();
            $enabledOrderId = (int)Mage::getResourceModel('transoft_callcenter/initiator_order')
                ->initiatorStatusFilter($model->getCallcenterUserId(), true);
            if ($enabledOrderId === 0) {
                $buttonData['label']   = Mage::helper('transoft_callcenter')->__('В ожидание заказа');
                $buttonData['class'] = 'disabled';
                unset($buttonData['onclick']);
            } elseif($enabledOrderId > 0) {
                $url = $this->getUrl('*/sales_order/view/', ['order_id' => $enabledOrderId]);
                $buttonData['onclick'] = 'setLocation(\'' . $url . '\')';
                $buttonData['label']   = Mage::helper('transoft_callcenter')->__('Получить необработанний заказ');
            }
            $this->addButton(
                'callcenter_initiator_get_order',
                $buttonData,
                0,
                100,
                'header'
            );
        }
    }

    /**
     * Get order url for callcenter initiator
     *
     * @return string
     */
    public function getOrderUrlForInitiator()
    {
        return $this->getUrl('*/callcenter_initiator/getOrder/');
    }

    /**
     * Get button data
     *
     * @return array
    */
    public function getButtonData()
    {
        return [
            'label'   =>  Mage::helper('transoft_callcenter')->__('Получить заказ'),
            'onclick' => 'setLocation(\'' . $this->getOrderUrlForInitiator() . '\')',
            'class'   => 'add',
        ];
    }
}
