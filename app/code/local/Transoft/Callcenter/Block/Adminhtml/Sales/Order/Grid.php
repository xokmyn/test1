<?php

class Transoft_Callcenter_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid {

    protected $_isCallcenter;

    public function __construct()
    {
        parent::__construct();

        $this->_isCallcenter = Mage::getModel("transoft_callcenter/initiator")->isCallcenterUser();
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_grid_collection';
    }

    protected function _prepareCollection()
    {
        if($this->_isCallcenter)
        {
            $model   = Mage::getModel("transoft_callcenter/initiator");
            $user_id = $model->getCallcenterUserId();
            $collection = Mage::getResourceModel($this->_getCollectionClass());

            //we changed mysql query, we added inner join to order item table
            $collection->join(
                array('related' => 'sales/order'),
                'related.entity_id = main_table.entity_id',
                array('initiator_id'=>'initiator_id'),
                null,
                'left'
            );
            if($model->getCallcenterUserRoleName() == Transoft_Callcenter_Model_Initiator_Source::OPERATOR)
            {
                $collection->addFieldToFilter('initiator_id', $user_id);
            }

            $this->setCollection($collection);

            //Call the grand parent instead of parent::_prepareCollection()
            return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
        }

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        if($this->_isCallcenter)
        {
            $this->addColumn('initiator_id', array(
                'header'=> Mage::helper('sales')->__('Initiator ID'),
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
        if($this->_isCallcenter)
        {
            $this->setMassactionIdField('entity_id');
            $this->getMassactionBlock()->setFormFieldName('order_ids');
            $this->getMassactionBlock()->setUseSelectAll(false);
            $this->setMassactionIdField('entity_id');

            if (Mage::getSingleton('admin/session')->isAllowed('transoft_callcenter/initiator/actions/removeinitiator')) {
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