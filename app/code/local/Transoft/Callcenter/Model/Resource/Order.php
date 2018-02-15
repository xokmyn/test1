<?php
/**
 * Order resource model
 *
 * @category    Transoft
 * @package     Transoft_Callcenter
 */
class Transoft_Callcenter_Model_Resource_Order extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * constructor
     *
     * @access public
     */
    public function _construct()
    {
        $this->_init('sales/order', 'entity_id');
    }

    /**
     * Update order field
     *
     * @param array||int $orderIds
     * @param array $data format ["field" => "value" ]
    */
    public function updateOrderField($orderIds, $data = [])
    {
        if(empty($data))
        {
            $data = $this->getDefaultData();
        }
        if(!is_array($orderIds))
        {
            $orderIds = [$orderIds];
        }
        try{
            $adapter = $this->_getWriteAdapter();
            $bind    = $data;
            $adapter->update(
                $this->getMainTable(),
                $bind,
                array('entity_id IN (?)' => $orderIds)
            );
        }catch (Exception $e)
        {
            Mage::logException($e);
        }
    }

    /**
     * Get default data for flat order table with attribute initiator_id
     *
     * @return array
    */
    protected function getDefaultData()
    {
        return Mage::getModel("transoft_callcenter/order")->getDefaultData();
    }
}
