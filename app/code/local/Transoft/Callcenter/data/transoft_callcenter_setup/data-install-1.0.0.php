<?php
/**
 * 2 Roles Creation
 */
$roles = array(0 => 'Специалист колл-центра', 1 => 'Координатор колл-центра');
$resources = Mage::getResourceModel('transoft_callcenter/setup')
    ->getDefaultRoles();
foreach ($roles as $k => $role) {
    try {
        $col = Mage::getModel('admin/role')->setRoleName($role)->setRoleType('G')->setTreeLevel(1)->save();
        if ($col->getRoleId()) {
            if ($k === 0) {
                $resources = Mage::getResourceModel('transoft_callcenter/setup')
                    ->getOrderResource();
            } elseif ($k === 1) {
                $resources = Mage::getResourceModel('transoft_callcenter/setup')
                    ->getRemoveInitiatorResource();
            }
            Mage::getModel('admin/rules')
                ->setRoleId($col->getRoleId())
                ->setResources($resources)
                ->saveRel();
        }
    } catch (Exception $e) {
        Mage::logException($e);
    }
}