<?php
/**
 * Initiator collection resource model
 *
 * @category    Transoft
 * @package     Transoft_Callcenter
 */
class Transoft_Callcenter_Model_Resource_Initiator_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * constructor
     *
     * @access public
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('admin/user');
    }

    /**
     * Add filter "callcenter_type" to user
     *
     * @param int $type
     * @return mixed
    */
    public function getUserIdsWithType($type)
    {
        return $this->addFieldToFilter('callcenter_type', $type);
    }
}
