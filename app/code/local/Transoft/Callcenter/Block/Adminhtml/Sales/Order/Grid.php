<?php

class Transoft_Callcenter_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    protected $isCallcenter;

    public function __construct()
    {
        parent::__construct();
        /** @var Transoft_Callcenter_Model_Initiator $initiatorModel */
        $initiatorModel = Mage::getSingleton('transoft_callcenter/initiator');
        $this->isCallcenter = $initiatorModel->checkIsCallcenter();
    }

    protected function _prepareCollection()
    {
        if ($this->isCallcenter) {
            /** @var Transoft_Callcenter_Model_Initiator $initiatorModel */
            $initiatorModel = Mage::getSingleton('transoft_callcenter/initiator');
            $user_id    = $initiatorModel->getCallcenterUserId();
            $collection = Mage::getResourceModel($this->_getCollectionClass());
            $cond       = null;
            //we changed mysql query, we added inner join to order item table
            if ($initiatorModel->getCallcenterUserRoleName() === Transoft_Callcenter_Model_Initiator_Source::OPERATOR) {
                $cond = 'related.order_id = main_table.entity_id AND related.initiator_id = ' . $user_id;
            } elseif ($initiatorModel->getCallcenterUserRoleName() === Transoft_Callcenter_Model_Initiator_Source::COORDINATOR) {
                $cond = 'related.order_id = main_table.entity_id';
            }
            $collection->join(
                array('related' => $initiatorModel->getResourceName()),
                $cond,
                array('initiator_id' => 'initiator_id')
            );
            $this->setCollection($collection);
            //Call the grand parent instead of parent::_prepareCollection()
            return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
        }
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        if ($this->isCallcenter) {
            $this->addColumn('initiator_id', array(
                'header'=> Mage::helper('transoft_callcenter')->__('Initiator ID'),
                'width' => '80px',
                'type'  => 'text',
                'index' => 'initiator_id',
            ));
        }
        return parent::_prepareColumns();
    }

    /**
     * Set mass remove initiator actions
    */
    protected function _prepareMassaction()
    {
        if ($this->isCallcenter) {
            $this->setMassactionIdField('entity_id');
            $this->getMassactionBlock()->setFormFieldName('order_ids');
            $this->getMassactionBlock()->setUseSelectAll(false);
            $this->setMassactionIdField('entity_id');
            if (Mage::getSingleton('admin/session')
                ->isAllowed('transoft_callcenter/initiator/actions/removeinitiator')) {
                $this->getMassactionBlock()->addItem('initiator_id', array(
                    'label'=> Mage::helper('transoft_callcenter')->__('Remove Initiator'),
                    'url'  => $this->getUrl('*/callcenter_initiator/massRemoveInitiator'),
                ));
            }
            return $this;
        }
        return parent::_prepareMassaction();
    }
}
