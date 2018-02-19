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
        $model = Mage::getModel('transoft_callcenter/initiator');
        if ($model->isCallcenterUser()) {
            $this->addButton('callcenter_initiator_get_order', array(
                'label' => Mage::helper('transoft_callcenter')->__('Получить заказ'),
                'onclick' => 'setLocation(\'' . $this->getOrderUrlForInitiator() . '\')',
                'class' => 'add'
            ), 0, 100, 'header', 'header');
            $enabledOrderArr = Mage::getResourceModel('transoft_callcenter/initiator_order')
                ->initiatorStatusFilter(true);
            if ($enabledOrderArr) {
                $enabled_order = (int)array_shift($enabled_orderArr);
                if ($enabled_order === 0) {
                    Mage::getSingleton('core/session')
                        ->addNotice(Mage::helper('transoft_callcenter')->__('Вы находитесь в очереди на заказ!'));

                    $url = $this->getUrl('*/sales_order/');

                    $this->addButton('callcenter_initiator_get_order', array(
                        'label' => Mage::helper('transoft_callcenter')->__('Получить заказ'),
                        'onclick' => 'setLocation(\'' . $url . '\')',
                        'class' => 'add'
                    ));

                    return $this;
                }
                Mage::getSingleton('core/session')
                    ->addNotice(Mage::helper('transoft_callcenter')->__('У вас есть необработанний заказ!'));
                $url = $this->getUrl('*/sales_order/view/', ['order_id' => $enabled_order]);

                $this->addButton('callcenter_initiator_get_order', array(
                    'label' => Mage::helper('transoft_callcenter')->__('Получить необработанний заказ'),
                    'onclick' => 'setLocation(\'' . $url . '\')',
                    'class' => 'add'
                ));
            }
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
}
