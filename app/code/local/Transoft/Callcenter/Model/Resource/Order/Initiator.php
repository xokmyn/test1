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
     * @return void
     * @see Mage_Core_Model_Resource_Abstract::_construct()
     */
    protected function _construct()
    {
        $this->_init('transoft_callcenter/order_initiator', 'rel_id');
    }

    /**
     * Save order - initiator relations
     *
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
        foreach ($data as $initiatorId => $info) {
            if ($orderId > 0) {
                $bind = array(
                    'status'   => 0,
                    'position' => 1
                );
                $adapter->update(
                    $this->getMainTable(),
                    $bind,
                    ['order_id = '.$orderId, 'initiator_id = '.$initiatorId]
                );
            }
        }
        return $this;
    }

    /**
     * Delete order - initiator relations
     *
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

    /**
     * Get order-initiator relation
     * @param int $orderId
     * @param int $initiatorId
     * @return array
    */
    public function getOrderInitiatorRelation($orderId, $initiatorId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), array('rel_id', 'initiator_id', 'order_id'))
            ->where('order_id = ?', (int)$orderId)
            ->where('initiator_id = ?', (int)$initiatorId);

        return $adapter->fetchAll($select);
    }
}
