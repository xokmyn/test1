<?php

class Transoft_Callcenter_Block_Adminhtml_Permissions_User_Edit_Tab_Main
    extends Mage_Adminhtml_Block_Permissions_User_Edit_Tab_Main
{
    /**
     * @return $this|Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $fieldset = $form->addFieldset('priority', array(
            'legend' => Mage::helper('adminhtml')->__('Приоритет пользователя'),
            'class' => 'fieldset-wide'
        ));
        $fieldset->addField('callcenter_type', 'select', array(
            'name' => 'callcenter_type',
            'label' => Mage::helper('transoft_callcenter')->__('Приоритет'),
            'id' => 'callcenter_type',
            'title' => Mage::helper('transoft_callcenter')->__('Приоритет'),
            'class' => 'input-select',
            'style' => 'width: 80px',
            'options' => $this->getCallcenterTypeOptions(),
            'value'   => $this->getCallcenterTypeValue()
        ));
        return $this;
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
