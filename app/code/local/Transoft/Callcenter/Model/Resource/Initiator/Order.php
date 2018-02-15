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
    protected function  _construct()
    {
        $this->_init('transoft_callcenter/initiator_order', 'rel_id');
    }

    /**
     * Save initiator - order relations
     *
     * @access public
     * @param Transoft_Callcenter_Model_Initiator $initiator
     * @param array $data
     * @return Transoft_Callcenter_Model_Resource_Initiator_Order
     */
    public function saveInitiatorRelation($initiator, $data)
    {
        if (!is_array($data)) {
            $data = array();
        }

        $adapter = $this->_getWriteAdapter();
        $bind    = array(
            ':initiator_id'    => (int)$initiator->getId(),
        );
        $select = $adapter->select()
            ->from($this->getMainTable(), array('rel_id', 'order_id'))
            ->where('initiator_id = :initiator_id');

        $related   = $adapter->fetchPairs($select, $bind);
        $deleteIds = array();
        foreach ($related as $relId => $orderId) {
            if (!isset($data[$orderId])) {
                $deleteIds[] = (int)$relId;
            }
        }
        if (!empty($deleteIds)) {
            $adapter->delete(
                $this->getMainTable(),
                array('rel_id IN (?)' => $deleteIds)
            );
        }

        foreach ($data as $orderId => $info) {
            $adapter->insertOnDuplicate(
                $this->getMainTable(),
                array(
                    'initiator_id'      => $initiator->getId(),
                    'order_id'     => $orderId,
                    'position'      => @$info['position']
                ),
                array('position')
            );
        }
        return $this;
    }

    /**
     * Get order ids with status " 1 "
     *
     * @access public
     * @param bool $checkStatus
     * @return array
     */
    public function initiatorStatusFilter($checkStatus = false)
    {
        $bind = null;
        $initiator_id   = Mage::getModel("transoft_callcenter/initiator")->getCallcenterUserId();
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), array('rel_id', 'order_id'))
            ->where('status = 1');

        if($checkStatus == true)
        {
            $bind    = array(
                ':initiator_id'    => (int)$initiator_id,
            );
            $select->where('initiator_id = :initiator_id');
        }


        $orderIds   = $adapter->fetchPairs($select, $bind);

        return $orderIds;
    }
}
