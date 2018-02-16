<?php

class Transoft_Callcenter_Block_Adminhtml_Permissions_User_Edit_Tab_Main extends  Mage_Adminhtml_Block_Permissions_User_Edit_Tab_Main
{
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();

        $fieldset = $form->addFieldset('priority', array(
            'legend' => Mage::helper('adminhtml')->__('Приоритет пользователя'),
            'class' => 'fieldset-wide'
        ));

        $fieldset->addField('callcenter_type', 'select', array(
            'name'      => 'callcenter_type',
            'label'     => Mage::helper('adminhtml')->__('Приоритет'),
            'id'        => 'callcenter_type',
            'title'     => Mage::helper('adminhtml')->__('Приоритет'),
            'class'     => 'input-select',
            'style'     => 'width: 80px',
            'options'   => Mage::helper("transoft_callcenter")->convertOptions(
                 Mage::getModel("transoft_callcenter/initiator_type")->getAllOptions(true)
                )
        ));

        return $this;
    }
}