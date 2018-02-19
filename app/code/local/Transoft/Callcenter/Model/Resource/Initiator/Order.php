<?php

/**
 * Initiator - Order relation model
 *
 * @category    Transoft
 * @package     Transoft_Callcenter
 */
class Transoft_Callcenter_Model_Resource_Initiator_Order extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * initialize resource model
     *
     * @access protected
     * @return void
     * @see Mage_Core_Model_Resource_Abstract::_construct()
     */
    protected function _construct()
    {
        $this->_init('transoft_callcenter/initiator_order', 'rel_id');
    }

    /**
     * Save initiator - order relations
     *
     * @access public
     * @param int $initiatorId
     * @param array $data
     * @return Transoft_Callcenter_Model_Resource_Initiator_Order
     */
    public function saveInitiatorRelation($initiatorId, $data)
    {
        if (!is_array($data)) {
            $data = array();
        }
        $adapter = $this->_getWriteAdapter();
        foreach ($data as $orderId => $info) {
            if ($orderId > 0) {
                $bind = array(
                    'order_id' => (int)$orderId,
                    'position' => isset($info['position']) ? $info['position'] : 1,
                    'status'   => isset($info['status']) ? $info['status'] : 1
                );
                $adapter->update(
                    $this->getMainTable(),
                    $bind,
                    ['order_id = 0', 'initiator_id = '.$initiatorId]
                );
            } else {
                $adapter->insertOnDuplicate(
                    $this->getMainTable(),
                    array(
                        'initiator_id' => $initiatorId,
                        'order_id' => $orderId,
                        'position' => isset($info['position']) ? $info['position'] : 1,
                    ),
                    array('position')
                );
            }
        }
        return $this;
    }

    /**
     * Get order id with status " 1 " for initiator
     *
     * @param int $initiatorId
     * @param bool $checkStatus
     * @return int
     */
    public function initiatorStatusFilter($initiatorId, $checkStatus = false)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), array('order_id'))
            ->where('status = ?', ($checkStatus) ? 1 : 0)
            ->where('initiator_id = ?', $initiatorId);
        $result = $adapter->fetchOne($select);
        $orderId = ($result !== false) ? $result : -1;
        return $orderId;
    }

    /**
     * Get order ids with status " 1 "
     *
     * @return array
     */
    public function getAllOrderIdsStatusEnabled()
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), array('order_id'))
            ->where('status = 1');

        return $adapter->fetchCol($select);
    }

    /**
     * Get last position for used order
     *
     * @return int
     */
    public function getLastPosition()
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), array('position'))
            ->where('order_id = 0')
            ->order('position DESC')
            ->limit(1);

        return $adapter->fetchOne($select);
    }

    /**
     * Get initiators users
     *
     * @param array $userIds
     * @return array
     */
    public function getInitiatorsOrderWithType(array $userIds = [])
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), array('*'))
            ->where('order_id = 0');
        if ($userIds) {
            $select->where('initiator_id IN (?)', $userIds);
        }
        $select->order('position ASC');
        $select->joinLeft(
            array(
                'admin' => 'admin_user'
            ),
            'initiator_id = admin.user_id',
            array('callcenter_type')
        );

        return $adapter->fetchAll($select);
    }

    /**
     * Save order - initiator relations
     *
     * @param int $orderId
     * @param array $data
     * @return Transoft_Callcenter_Model_Resource_Initiator_Order
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
     * @return Transoft_Callcenter_Model_Resource_Initiator_Order
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
