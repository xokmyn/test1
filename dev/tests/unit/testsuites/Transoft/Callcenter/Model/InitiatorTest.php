<?php

class Transoft_Callcenter_Model_InitiatorTest extends PHPUnit_Framework_TestCase
{
    public $app = null;
    /**
     * @var Transoft_Callcenter_Model_Initiator
     */
    public $initiatorModel;

    public function setUp()
    {
        $this->app = Mage::app('default');
        $this->initiatorModel = Mage::getSingleton('transoft_callcenter/initiator');
    }

    public function tearDown()
    {
        unset($this->app);
    }

    /**
     * Check work saving order id to relation table initiator-order
    */
    public function testSaveOrderWithProductSetToInitiator()
    {
        $saveResult = $this->initiatorModel->saveOrderWithProductSetToInitiator();
        $this->assertNull($saveResult);
    }

    /**
     * Get orders collection with status "NEW" and exclude order Ids
    */
    public function testGetNewOrderCollection()
    {
        $orderCollection = $this->initiatorModel->getNewOrderCollection();
        $this->assertInstanceOf(Mage_Sales_Model_Resource_Order_Collection::class, $orderCollection);
    }

    /**
     * Check if order with status "new" are in table transoft_callcenter_initiator_order
    */
    public function testIsOrderIdsInInitiatorId()
    {
        $arrData = $this->initiatorModel->getProcessUserOrder();
        /** @var Transoft_Callcenter_Model_Resource_Initiator_Order $model */
        $model = Mage::getResourceModel('transoft_callcenter/initiator_order');
        foreach ($arrData as $k => $data) {
            $dataTable = $model->getOrderInitiatorRelation($k, $data);
            if ($dataTable) {
                unset($arrData[$k]);
            }
        }
        $this->assertEmpty($arrData);
    }
}
