<?php

class Transoft_Callcenter_Model_InitiatorTest extends PHPUnit_Framework_TestCase
{
    public $app;
    private $productAttributeSetId;
    private $optionsProduct;
    private $products;
    private $storeId;
    private $groupId;
    private $websiteId;
    private $customer;
    private $customerEmail = 'jdsdf1@ex.com';
    private $orders = [];
    private $callcenterUsers = [];

    /**
     * @return array
     */
    public function getCallcenterUsers()
    {
        return $this->callcenterUsers;
    }


    /**
     * @var Transoft_Callcenter_Model_Initiator
     */
    public $initiatorModel;

    public function setUp()
    {
        $this->app = Mage::app('default');
    }

    public function tearDown()
    {
        unset($this->app);
    }

    /**
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->websiteId = Mage::getModel('core/website')
            ->getCollection()
            ->addFieldToFilter('is_default', 1)
            ->getFirstItem()
            ->getId();
    }

    /**
     * Get default store id
     * @return int
     */
    public function getDefaultStoreId()
    {
        $iDefaultStoreId = $this->app
            ->getWebsite(true)
            ->getDefaultGroup()
            ->getDefaultStoreId();
        return $this->storeId = $iDefaultStoreId;
    }

    /**
     * @return int
     */
    public function getDefaultGroupId()
    {
        $storeId = $this->storeId ?: $this->getDefaultStoreId();
        return $this->groupId = Mage::getStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID, $storeId);
    }

    /**
     * @return array
     */
    public function getOptionsProduct()
    {
        /** @var Transoft_Callcenter_Model_Initiator_Type $typeModel */
        $typeModel = Mage::getModel('transoft_callcenter/initiator_type');
        /** @var Transoft_Callcenter_Helper_Data $helper */
        $helper = Mage::helper('transoft_callcenter');
        $this->optionsProduct = $helper->convertOptions($typeModel->getAllOptions(true));
        return $this->optionsProduct;
    }

    /**
     * @return int
     */
    public function getProductAttributeSetId()
    {
        $this->productAttributeSetId = Mage::getModel('eav/entity_attribute_set')
            ->getCollection()
            ->addFieldToFilter('attribute_set_name', 'Format type')
            ->getFirstItem()
            ->getAttributeSetId();
        return $this->productAttributeSetId;
    }

    /**
     * Create test products
     * @param $attributeSetId
     * @param $sku
     * @param $name
     * @param $attributeCallcenterTypeValue
     */
    public function createProduct($attributeSetId, $sku, $name, $attributeCallcenterTypeValue)
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $websiteId = $this->getWebsiteId();
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product');
        if (!$product->getIdBySku($sku)) :
            try {
                $product
                    ->setWebsiteIds(array($websiteId))//website ID the product is assigned to, as an array
                    ->setAttributeSetId($attributeSetId)
                    ->setTypeId('simple')
                    ->setSku($sku)
                    ->setName($name)
                    ->setWeight(4.0000)
                    ->setStatus(1)//product status (1 - enabled, 2 - disabled)
                    ->setTaxClassId(0)//tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
                    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                    ->setPrice(11.22)
                    ->setMetaTitle('test meta title 2')
                    ->setMetaKeyword('test meta keyword 2')
                    ->setMetaDescription('test meta description 2')
                    ->setDescription('This is a long description')
                    ->setShortDescription('This is a short description')
                    ->setData('callcenter_format_type', $attributeCallcenterTypeValue)
                    ->setStockData(array(
                        'use_config_manage_stock' => 0, //'Use config settings' checkbox
                        'manage_stock' => 1, //manage stock
                        'is_in_stock' => 1, //Stock Availability
                        'qty' => 999
                    ));
                $product->save();
                $this->products[] = $product;
            } catch (Exception $e) {
                Mage::log($e->getMessage());
            }
        else :
            $this->products[] = $product->loadByAttribute('sku', $sku);
        endif;
    }

    /**
     * @return array
     */
    public function createProducts()
    {
        if (!$this->products) {
            $_data = $this->getDataProductAttributeOptions();
            foreach ($_data as $data) {
                $this->createProduct(
                    $data['attribute_set_id'],
                    $data['sku'],
                    $data['name'],
                    $data['callcenter_format_type']
                );
            }
        }
        return $this->products;
    }

    public function getDefaultProductData()
    {
        $defaultAttrSetId = Mage::getModel('catalog/product')->getDefaultAttributeSetId();
        $data = [
            'attribute_set_id' => $defaultAttrSetId,
            'sku' => 'testSkuDefaultCallcenter',
            'name' => 'Callcenter Default',
            'callcenter_format_type' => null
        ];
        return $data;
    }

    /**
     * @return array
     */
    public function getDataProductAttributeOptions()
    {
        $dataProduct[] = $this->getDefaultProductData();
        $attributeSetId = $this->getProductAttributeSetId();
        $options = $this->getOptionsProduct();
        foreach ($options as $k => $option) {
            $dataProduct[] = [
                'attribute_set_id' => $attributeSetId,
                'sku' => 'testSkuCallcenterTypeOpt' . $k,
                'name' => 'Callcenter type option ' . $option,
                'callcenter_format_type' => $k
            ];
        }
        return $dataProduct;
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array products for order
     */
    public function getProductsForOrder($product)
    {
        $data = [];
        $product['qty'] = 1;
        $data[$product->getId()] = $product;
        return $data;
    }

    /**
     * Create customer
     * @return Mage_Customer_Model_Customer
     */
    public function createCustomer()
    {
        $websiteId = $this->app->getWebsite()->getId();
        $store = $this->app->getStore();
        $email = $this->customerEmail;
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($email);
        if (!$customer->getId()) {
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname('Callcenter')
                ->setLastname('Test')
                ->setEmail($email)
                ->setPassword('123456');

            try {
                $customer = $customer->save();
            } catch (Exception $e) {
                Zend_Debug::dump($e->getMessage());
            }
        }
        return $this->customer = $customer;
    }

    /**
     * Create customer address and add it to him
     */
    public function createCustomerAddress()
    {
        if ($customer = $this->customer) {
            /** @var Mage_Customer_Model_Address $address */
            $address = Mage::getModel('customer/address');
            $address->setCustomerId($customer->getId())
                ->setFirstname($customer->getFirstname())
                ->setMiddleName($customer->getMiddlename())
                ->setLastname($customer->getLastname())
                ->setCountryId('HR')
                ->setPostcode('31000')
                ->setCity('Lviv')
                ->setTelephone('0503451212')
                ->setStreet('Shevchenko')
                ->setIsDefaultBilling('1')
                ->setIsDefaultShipping('1')
                ->setSaveInAddressBook('1');

            try {
                $address->save();
            } catch (Exception $e) {
                Zend_Debug::dump($e->getMessage());
            }
            $this->customer = Mage::getModel('customer/customer')->load($customer->getId());
        }
    }

    /**
     * Creates order
     * @param array $products
     */
    public function createOrder($products)
    {
        $customer = $this->customer;
        $transaction = Mage::getModel('core/resource_transaction');
        $storeId = $customer->getStoreId();
        $reservedOrderId = Mage::getSingleton('eav/config')->getEntityType('order')->fetchNewIncrementId($storeId);

        $order = Mage::getModel('sales/order')
            ->setIncrementId($reservedOrderId)
            ->setStoreId($storeId)
            ->setQuoteId(0);

        // set Customer data
        $order->setCustomer_email($customer->getEmail())
            ->setCustomerFirstname($customer->getFirstname())
            ->setCustomerLastname($customer->getLastname())
            ->setCustomerGroupId($customer->getGroupId())
            ->setCustomer_is_guest(0)
            ->setCustomer($customer);

        // set Billing Address
        $billing = $customer->getDefaultBillingAddress();
        $billingAddress = Mage::getModel('sales/order_address')
            ->setStoreId($storeId)
            ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
            ->setCustomerId($customer->getId())
            ->setCustomerAddressId($customer->getDefaultBilling())
            ->setFirstname($billing->getFirstname())
            ->setLastname($billing->getLastname())
            ->setStreet($billing->getStreet())
            ->setCity($billing->getCity())
            ->setCountry_id($billing->getCountryId())
            ->setPostcode($billing->getPostcode())
            ->setTelephone($billing->getTelephone());
        $order->setBillingAddress($billingAddress);

        $shipping = $customer->getDefaultShippingAddress();
        $shippingAddress = Mage::getModel('sales/order_address')
            ->setStoreId($storeId)
            ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
            ->setCustomerId($customer->getId())
            ->setCustomerAddressId($customer->getDefaultShipping())
            ->setFirstname($shipping->getFirstname())
            ->setLastname($shipping->getLastname())
            ->setStreet($shipping->getStreet())
            ->setCity($shipping->getCity())
            ->setCountry_id($shipping->getCountryId())
            ->setPostcode($shipping->getPostcode())
            ->setTelephone($shipping->getTelephone());

        $order->setShippingAddress($shippingAddress)
            ->setShipping_method('flatrate_flatrate');

        //you can set your payment method name here as per your need
        $orderPayment = Mage::getModel('sales/order_payment')
            ->setStoreId($storeId)
            ->setCustomerPaymentId(0)
            ->setMethod('checkmo');
        $order->setPayment($orderPayment);

        $subTotal = 0;
        foreach ($products as $k => $product) {
            $productId = $product->getId();
            $rowTotal = $product->getPrice() * $product['qty'];
            $orderItem = Mage::getModel('sales/order_item')
                ->setStoreId($storeId)
                ->setQuoteItemId(0)
                ->setQuoteParentItemId(null)
                ->setProductId($productId)
                ->setProductType($product->getTypeId())
                ->setQtyBackordered(null)
                ->setTotalQtyOrdered($product['rqty'])
                ->setQtyOrdered($product['qty'])
                ->setName($product->getName())
                ->setSku($product->getSku())
                ->setPrice($product->getPrice())
                ->setBasePrice($product->getPrice())
                ->setOriginalPrice($product->getPrice())
                ->setRowTotal($rowTotal)
                ->setBaseRowTotal($rowTotal);

            $subTotal += $rowTotal;
            $order->addItem($orderItem);
            $order->setData('callcenter_format_type', $product->getData('callcenter_format_type'));
        }

        $order->setSubtotal($subTotal)
            ->setBaseSubtotal($subTotal)
            ->setGrandTotal($subTotal)
            ->setBaseGrandTotal($subTotal);

        $transaction->addObject($order);
        $transaction->addCommitCallback(array($order, 'place'));
        $transaction->addCommitCallback(array($order, 'save'));
        try {
            $transaction->save();
        } catch (Exception $e) {
            Mage::log('Order save error...');
        }
        $this->orders[] = $order;
    }

    /**
     * Check work saving order id to relation table initiator-order
     */
    public function testSaveOrderWithProductSetToInitiator()
    {
        $this->createCallcenterUser();
        $this->createCustomer();
        $this->createCustomerAddress();
        $this->createProducts();
        $products = $this->getProducts();
        foreach ($products as $product) {
            $_products = $this->getProductsForOrder($product);
            $this->createOrder($_products);
        }
        /** @var Transoft_Callcenter_Model_Initiator initiatorModel */
        $this->initiatorModel = Mage::getSingleton('transoft_callcenter/initiator');
        $callcenterUsers = $this->getCallcenterUsers();
        foreach ($callcenterUsers as $callcenterUser) {
            /** @var Transoft_Callcenter_Model_Initiator_Source $sourceModel */
            $sourceModel = Mage::getModel('transoft_callcenter/initiator_source');
            /** @var Mage_Admin_Model_Role $roleModel */
            $roleModel =  $callcenterUser->getRole();
            $roleName  = $roleModel->getRoleName();
            if($roleName === $sourceModel::OPERATOR) {
                $this->initiatorModel->addInitiatorToPosition($callcenterUser->getId());
            }
        }
        $rightData = $this->getRightOrderInitiator();
        $orderIds  = array_keys($rightData);
        $this->initiatorModel->saveOrderWithProductSetToInitiator($orderIds);
        $arrUserOrder = $this->initiatorModel->getProcessUserOrder();
        //comparison two arrays
        $this->assertEquals($arrUserOrder, $this->getRightOrderInitiator());
        foreach ($arrUserOrder as $orderId => $initiatorId) {
            echo 'Callcenter User ' . $initiatorId . ' created get Order ID ' . $orderId . PHP_EOL;
        }
        $this->removeAllData();
    }

    /**
     * Get right value for order initiator array
     * @return array as ['order_id' => 'initiator_id']
     */
    public function getRightOrderInitiator()
    {
        $data     = [];
        $userData = [];
        $orders = $this->orders;
        $users = $this->callcenterUsers;
        foreach ($users as $user) {
            /** @var Transoft_Callcenter_Model_Initiator_Source $sourceModel */
            $sourceModel = Mage::getModel('transoft_callcenter/initiator_source');
            /** @var Mage_Admin_Model_Role $roleModel */
            $roleModel =  $user->getRole();
            $roleName  = $roleModel->getRoleName();
            if($roleName === $sourceModel::OPERATOR) {
                $userType = $user->getData('callcenter_type') ?: 0;
                $userData[$userType] = $user->getUserId();
            }
        }
        foreach ($orders as $order) {
            $id = $order->getIncrementId();
            $orderType = $order->getData('callcenter_format_type') ?: 0;
            /** @var Mage_Sales_Model_Order $orderModel */
            $orderModel = Mage::getModel('sales/order')->loadByIncrementId($id);
            $orderId = $orderModel->getEntityId();
            if ($userData[$orderType]) {
                $data[$orderId] = $userData[$orderType];
            }
            unset($userData[$orderType]);
        }
        return $data;
    }

    /**
     * Created callcenter admin users
     */
    public function createCallcenterUser()
    {
        /** @var Transoft_Callcenter_Model_Initiator_Source $sourceModel */
        $sourceModel = Mage::getModel('transoft_callcenter/initiator_source');
        $callcenterRoles = $sourceModel->getCallcenterRoles();
        foreach ($callcenterRoles as $k => $role) {
            if ($role->getRoleName() === $sourceModel::OPERATOR) {
                $typeIds = $this->getOptionsProduct();
                foreach ($typeIds as $typeId => $label) {
                    $data = [
                        'email' => 'Operator' . $typeId . '@example.com',
                        'label' => $label,
                        'type_id' => $typeId,
                        'role_id' => $k
                    ];
                    $this->saveCallcenterAdminUser($data);
                }
            } elseif ($role->getRoleName() === $sourceModel::COORDINATOR) {
                $data = [
                    'email' => 'Coordinator@example.com',
                    'label' => 'coordinator',
                    'type_id' => null,
                    'role_id' => $k
                ];
                $this->saveCallcenterAdminUser($data);
            }
        }
    }

    /**
     * @param array $data callcenter admin user
     */
    public function saveCallcenterAdminUser($data)
    {
        try {
            $user = Mage::getModel('admin/user')
                ->setData(array(
                    'username' => 'Operator ' . $data['label'],
                    'firstname' => 'OperatorFirst ' . $data['label'],
                    'lastname' => 'OperatorLast ' . $data['label'],
                    'email' => $data['email'],
                    'password' => '3lP4ass3or$',
                    'is_active' => 1,
                    'callcenter_type' => $data['type_id']
                ))->save();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
        //Assign Role Id
        try {
            $user->setRoleIds(array($data['role_id']))
                ->saveRelations();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
        $this->callcenterUsers[] = $user;
        echo 'Callcenter User ' . $data['label'] . ' created successfully' . PHP_EOL;
    }

    /**
     * Remove data for its test
     */
    public function removeAllData()
    {
        foreach ($this->orders as $order) {
            $id = $order->getIncrementId();
            try {
                /** @var Mage_Sales_Model_Order $orderModel */
                $orderModel = Mage::getModel('sales/order')->loadByIncrementId($id);
                $orderModel->delete();
                echo 'Order # ' . $id . ' is removed' . PHP_EOL;
            } catch (Exception $e) {
                echo 'Order # ' . $id . ' could not be removed: ' . $e->getMessage() . PHP_EOL;
            }
        }
        $websiteId = $this->app->getWebsite()->getId();
        $email = $this->customerEmail;
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($email);
        try {
            $customer->delete();
            echo 'Customer with email : ' . $email . ' is removed' . PHP_EOL;
        } catch (Exception $e) {
            echo 'Customer with email : ' . $email . ' could not be removed: ' . $e->getMessage() . PHP_EOL;
        }
        $products = $this->getProducts();
        foreach ($products as $product) {
            $productId = $product->getId();
            try {
                $product->delete();
                echo 'Product # ' . $productId . ' is removed' . PHP_EOL;
            } catch (Exception $e) {
                echo 'Product # ' . $productId . ' could not be removed: ' . $e->getMessage() . PHP_EOL;
            }
        }
        $callcenterUsers = $this->getCallcenterUsers();
        foreach ($callcenterUsers as $user) {
            try {
                $email = $user->getEmail();
                $user->delete();
                echo 'User with email : ' . $email . ' is removed' . PHP_EOL;
            } catch (Exception $e) {
                echo 'User with email : ' . $email . ' could not be removed: ' . $e->getMessage() . PHP_EOL;
            }
        }
    }
}
