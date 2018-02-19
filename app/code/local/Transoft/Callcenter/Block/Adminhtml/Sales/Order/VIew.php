<?php

class Transoft_Callcenter_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View {

    public function __construct()
    {
        parent::__construct();

        $this->checkAndAddButton();
    }

    /**
     * Check and add button for callcenter user
    */
    protected function checkAndAddButton()
    {
        $model = Mage::getModel('transoft_callcenter/initiator');
        if ($model->getCallcenterUserRoleName() === Transoft_Callcenter_Model_Initiator_Source::COORDINATOR) {
            $url = $this->getUrl(
                '*/callcenter_initiator/removeInitiator/',
                ['order_id' => Mage::registry('current_order')->getId()]
            );
            $this->_addButton('button_id', array(
                'label'     => Mage::helper('transoft_callcenter')->__('Remove Initiator'),
                'onclick'   => 'setLocation(\'' . $url . '\')',
                'class'     => 'delete'
            ), 0, 100, 'header', 'header');
        }
    }
}
