<?php
/**
 * Order - Initiator relation model
 *
 * @category    Transoft
 * @package     Transoft_Callcenter
 */
class Transoft_Callcenter_Model_Resource_Order_Initiator extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * initialize resource model
     *
     * @access protected
     * @return void
     * @see Mage_Core_Model_Resource_Abstract::_construct()
     */
    protected function  _construct()
    {
        $this->_init('transoft_callcenter/order_initiator', 'rel_id');
    }

    /**
     * Save order - initiator relations
     *
     * @access public
     * @param int $orderId
     * @param array $data
     * @return Transoft_Callcenter_Model_Resource_Order_Initiator
     */
    public function saveOrderRelation($orderId, $data)
    {
        if (!is_array($data)) {
            $data = array();
        }

        $adapter = $this->_getWriteAdapter();
        $bind    = array(
            ':order_id'    => (int)$orderId,
        );
        $select = $adapter->select()
            ->from($this->getMainTable(), array('rel_id', 'initiator_id'))
            ->where('order_id = :order_id');

        $related   = $adapter->fetchPairs($select, $bind);
        $deleteIds = array();
        foreach ($related as $relId => $initiatorId) {
            if (!isset($data[$initiatorId])) {
                $deleteIds[] = (int)$relId;
            }
        }
        if (!empty($deleteIds)) {
            $adapter->delete(
                $this->getMainTable(),
                array('rel_id IN (?)' => $deleteIds)
            );
        }

        foreach ($data as $initiatorId => $info) {
            $adapter->insertOnDuplicate(
                $this->getMainTable(),
                array(
                    'order_id'      => $orderId,
                    'initiator_id'     => $initiatorId,
                    'status'      => @$info['status']
                ),
                array('status')
            );
        }
        return $this;
    }

    /**
     * Delete order - initiator relations
     *
     * @access public
     * @param array||int $orderIds
     * @return Transoft_Callcenter_Model_Resource_Order_Initiator
     */
    public function deleteOrderRelation($orderIds)
    {
        if (!is_array($orderIds)) {
            $orderIds = [$orderIds];
        }
        $adapter = $this->_getWriteAdapter();
        if (!empty($orderIds)) {
            $adapter->delete(
                $this->getMainTable(),
                array('order_id IN (?)' => $orderIds)
            );
        }
        return $this;
    }


}
