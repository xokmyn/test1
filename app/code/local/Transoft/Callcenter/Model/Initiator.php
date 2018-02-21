<?php

/**
 * Callcenter Initiator model
 *
 * @category    Transoft
 * @package     Transoft_Callcenter
 */
class Transoft_Callcenter_Model_Initiator extends Transoft_Callcenter_Model_Callcenter
{
    /**
     * Check is user used Callcenter
     */
    public function isCallcenterUser()
    {
        return $this->isCallcenter();
    }

    /**
     * Get callcenter user name
     */
    public function getCallcenterUserRoleName()
    {
        return $this->callcenterUser()->getData('callcenter_role');
    }

    /**
     * get callcenter user ID
     */
    public function getCallcenterUserId()
    {
        return $this->callcenterUser()->getUserId();
    }

    /**
     * Save initiator to relation table "transoft_callcenter_initiator_order" with position value
     */
    public function addInitiatorToPosition()
    {
        $this->saveInitiatorPosition();
    }

    /**
     * Get order ids is from relation tables with status "1"
     */
    protected function getExcludeOrderIds()
    {
        $orderIds = array_unique(
            Mage::getResourceSingleton('transoft_callcenter/initiator_order')
                ->getAllOrderIdsStatusEnabled()
        );
        return $orderIds;
    }

    /**
     * Get order collection for using in callcenter
     */
    public function getNewOrderCollection()
    {
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_NEW);
        $excludeIds = $this->getExcludeOrderIds();
        if ($excludeIds) {
            $orders->getSelect()->where('main_table.entity_id NOT IN(?)', $excludeIds);
        }

        return $orders;
    }

    /**
     * Get pending order for user
     *
     * @return Mage_Sales_Model_Order
     */
    public function getPendingOrder()
    {
        $userId = $this->_callcenterUser->getUserId();
        $order = Mage::getModel('sales/order')->getCollection()
            ->addFieldToSelect(['entity_id', 'increment_id'])
            ->addFieldToFilter('initiator_id', $userId)
            ->addFieldToFilter('status', 'pending')
            ->getFirstItem();

        return $order;
    }

    /**
     * Add order with position for callcenter user
     *
     * @return array
     */
    public function getUserWithTypeInQueue()
    {
        $usersData = Mage::getResourceSingleton('transoft_callcenter/initiator_order')
            ->getInitiatorsOrderWithType();

        return $usersData;
    }

    /**
     * Save order to initiator-order table
     */
    public function saveOrderWithProductSetToInitiator()
    {
        $users = $this->getUserWithTypeInQueue();
        if ($users) {
            $collection = $this->getNewOrderCollection();

            $attributeSetId = Mage::getModel('eav/entity_attribute_set')
                ->getCollection()
                ->addFieldToFilter('attribute_set_name', 'Format type')
                ->getFirstItem()
                ->getAttributeSetId();

            foreach ($users as $user) {
                $data = [];
                $type = (int)$user['callcenter_type'];
                $orderId = $this->_getOrderForInitiator($collection, $attributeSetId, $type) ?: 0;
                if ($orderId) {
                    $data[$orderId] = ['status' => true, 'position' => 1];
                    $this->saveInitiatorOrderRelation($user['initiator_id'], $data);
                }
                $collection->removeItemByKey($orderId);
            }
        }
    }

    /**
     * Get order for user
     *
     * @param  Mage_Sales_Model_Resource_Order_Collection $collection orders
     * @param int $attributeSetId for product
     * @param int $type
     *
     * @return int $order_id
     */
    protected function _getOrderForInitiator($collection, $attributeSetId, $type)
    {
        foreach ($collection as $order) {
            $orderId = $order->getId();
            if (!$type) {
                return $orderId;
            }
            $order = Mage::getModel('sales/order')->load($orderId);
            foreach ($order->getAllItems() as $item) {
                $product = $item->getProduct();
                if ((int)$product->getAttributeSetId() === (int)$attributeSetId
                    && (int)$product->getData('callcenter_format_type') === (int)$type) {
                    return $orderId;
                }
            }
        }
    }

    /**
     * Save initiator order relation
     *
     * @param int $initiatorId
     * @param array $data
     */
    protected function saveInitiatorOrderRelation($initiatorId, $data)
    {
        try {
            Mage::getResourceSingleton('transoft_callcenter/initiator_order')
                ->saveInitiatorRelation($initiatorId, $data);
        } catch (Exception $e) {
            Mage::exception($e);
        }
    }

    /**
     * Get attribute options value
     *
     * @param string $optionCode
     * @return int||null
     */
    public function getTypeOptionValue($optionCode)
    {
        $attributeOptions = Mage::getModel('transoft_callcenter/initiator_type')
            ->getAllOptions();
        $attributeOptions = Mage::helper('transoft_callcenter')->convertOptions($attributeOptions);

        return array_flip($attributeOptions)[$optionCode];
    }

    /**
     * Remove pairs initiator-order from table
     *
     * @param int||array $orderId
     * @return void
     */
    public function removeInitiator($orderIds)
    {
        try {
            Mage::getResourceSingleton('transoft_callcenter/initiator_order')
                ->deleteOrderRelation($orderIds);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
