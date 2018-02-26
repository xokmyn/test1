<?php

/**
 * Setting source model for Callcenter_Initiator
 */
class Transoft_Callcenter_Model_Initiator_Source extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Roles names for Callcenter
     */
    const COORDINATOR = 'Координатор колл-центра';
    const OPERATOR = 'Специалист колл-центра';

    /**
     * Get roles name with Role ID
     *
     * @return array
     */
    public function getRolesName()
    {
        $roleNames = [];
        $adminRoles = Mage::getModel('admin/roles')->getCollection();
        if ($coordinatorRoleId = $adminRoles->addFieldToFilter('role_name', [self::COORDINATOR])->getId()) {
            $roleNames[$coordinatorRoleId] = self::COORDINATOR;
        } elseif ($operatorRoleId = $adminRoles->addFieldToFilter('role_name', [self::OPERATOR])->getId()) {
            $roleNames[$operatorRoleId] = self::COORDINATOR;
        }

        return $roleNames;
    }

    /**
     * Get all options
     *
     * @access public
     * @param bool $withEmpty
     * @return array
     */
    public function getAllOptions($withEmpty = false)
    {
        if (null !== $this->_options) {
            $roleIds = $this->getCallcenterRoleIds();
            $adminRoleUsers = Mage::getModel('admin/role')->getCollection()
                ->addFieldToFilter('parent_id', ['in' => $roleIds])
                ->join(['user' => 'admin/user'], 'user.user_id=main_table.user_id')
                ->getItems();
            $this->_options = $this->_toOptionArray($adminRoleUsers);
        }
        $options = $this->_options;
        if ($withEmpty) {
            array_unshift($options, array('value' => '', 'label' => ''));
        }
        return $options;
    }

    /**
     * Get a text for option value
     *
     * @access public
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions(false);
        foreach ($options as $item) {
            if ($item['value'] === $value) {
                return $item['label'];
            }
        }
        return false;
    }

    /**
     * Convert items array to array for select options
     *
     * return items array
     * array(
     *      $index => array(
     *          'value' => mixed
     *          'label' => mixed
     *      )
     * )
     *
     * @param   object
     * @param   string $valueField
     * @param   string $labelField
     * @param   array $additional
     * @return  array
     */
    protected function _toOptionArray($items, $valueField = 'entity_id', $labelField = 'name', $additional = array())
    {
        $res = [];
        $data = [];
        $additional['value'] = $valueField;
        $additional['label'] = $labelField;
        foreach ($items as $item) {
            foreach ($additional as $code => $field) {
                $data[$code] = $item->getData($field);
            }
            $res[] = $data;
        }
        return $res;
    }

    /**
     * Convert to options array
     *
     * @access public
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * Get role ids for Callcenter
     * @return array
     */
    public function getCallcenterRoleIds()
    {
        $adminRoles = Mage::getModel('admin/roles')->getCollection();
        $roleIds = $adminRoles->addFieldToFilter(
            'role_name',
            [self::OPERATOR, self::COORDINATOR]
        )
            ->getColumnValues('role_id');

        return $roleIds;
    }

    /**
     * Get roles for Callcenter
     * @return array
     */
    public function getCallcenterRoles()
    {
        $adminRoles = Mage::getModel('admin/roles')->getCollection();
        $roleIds = $adminRoles->addFieldToFilter(
            'role_name',
            [self::OPERATOR, self::COORDINATOR]
        )
            ->getItems();

        return $roleIds;
    }
}
