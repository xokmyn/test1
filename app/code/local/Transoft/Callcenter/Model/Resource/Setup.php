<?php

class Transoft_Callcenter_Model_Resource_Setup extends Mage_Sales_Model_Resource_Setup
{
    /**
     * Get default array with actions for using in Transoft_Callcenter
     */
    public function getDefaultRoles()
    {
        $resources = array(
            0 => '__root__',
            1 => 'admin/sales',
            2 => 'admin/sales/order',
            3 => 'admin/sales/order/actions',
            4 => 'admin/sales/order/actions/hold',
            5 => 'admin/sales/order/actions/creditmemo',
            6 => 'admin/sales/order/actions/unhold',
            7 => 'admin/sales/order/actions/ship',
            8 => 'admin/sales/order/actions/emails',
            9 => 'admin/sales/order/actions/comment',
            10 => 'admin/sales/order/actions/invoice',
            11 => 'admin/sales/order/actions/capture',
            12 => 'admin/sales/order/actions/email',
            13 => 'admin/sales/order/actions/view',
            14 => 'admin/sales/order/actions/reorder',
            15 => 'admin/sales/order/actions/edit',
            16 => 'admin/sales/order/actions/review_payment',
            17 => 'admin/sales/order/actions/cancel',
            //18 => 'admin/sales/order/actions/create',
            19 => 'admin/sales/invoice',
            20 => 'admin/sales/shipment',
            21 => 'admin/sales/creditmemo',
            22 => 'admin/sales/checkoutagreement',
            23 => 'admin/sales/transactions',
            24 => 'admin/sales/transactions/fetch',
            25 => 'admin/sales/recurring_profile',
            26 => 'admin/sales/billing_agreement',
            27 => 'admin/sales/billing_agreement/actions',
            28 => 'admin/sales/billing_agreement/actions/view',
            29 => 'admin/sales/billing_agreement/actions/manage',
            30 => 'admin/sales/billing_agreement/actions/use',
            31 => 'admin/sales/tax',
            32 => 'admin/sales/tax/classes_customer',
            33 => 'admin/sales/tax/classes_product',
            34 => 'admin/sales/tax/import_export',
            35 => 'admin/sales/tax/rates',
            36 => 'admin/sales/tax/rules',
        );

        return $resources;
    }

    /**
     * Get array with actions for using in role  'Специалист колл-центра' Transoft_Callcenter
     */
    public function getOrderResource()
    {
        $default = $this->getDefaultRoles();
        $resources = [
            'admin/transoft_callcenter',
            'admin/transoft_callcenter/initiator',
            'admin/transoft_callcenter/initiator/actions',
            'admin/transoft_callcenter/initiator/actions/getorder'
        ];
        $resources = array_merge($default, $resources);
        return $resources;
    }

    /**
     * Get array with actions for using in role  'Координатор колл-центра' Transoft_Callcenter
     */
    public function getRemoveInitiatorResource()
    {
        $default = $this->getDefaultRoles();
        $resources = [
            'admin/transoft_callcenter',
            'admin/transoft_callcenter/initiator',
            'admin/transoft_callcenter/initiator/actions',
            'admin/transoft_callcenter/initiator/actions/removeinitiator'
        ];
        $resources = array_merge($default, $resources);

        return $resources;
    }
}
