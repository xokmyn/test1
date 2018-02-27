<?php
/**
 * Initiator Order resource model
 *
 * @category    Transoft
 * @package     Transoft_Callcenter
 */
class Transoft_Callcenter_Model_Resource_Initiator extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * constructor
     *
     * @access public
     */
    public function _construct()
    {
        $this->_init('transoft_callcenter/initiator', 'entity_id');
    }
}
