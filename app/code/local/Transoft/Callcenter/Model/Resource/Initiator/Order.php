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
                        'position' => isset($info['position']) ?: 1,
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
        $bind = array(
            ':initiator_id' => (int)$initiatorId,
        );
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), array('order_id'))
            ->where('status = ?', ($checkStatus) ? 1 : 0);
        $result = $adapter->fetchOne($select, $bind);
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
}
