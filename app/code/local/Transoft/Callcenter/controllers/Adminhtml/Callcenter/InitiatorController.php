<?php

class Transoft_Callcenter_Adminhtml_Callcenter_InitiatorController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check if admin has permissions to visit related pages
     *
     * @access protected
     * @return boolean
     */
    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());
        switch ($action) {
            case 'getOrder':
                $aclResource = 'transoft_callcenter/initiator/actions/getorder';
                break;
            case 'removeInitiator':
                $aclResource = 'transoft_callcenter/initiator/actions/removeinitiator';
                break;
            default:
                $aclResource = 'transoft_callcenter/initiator';
                break;
        }
        return Mage::getSingleton('admin/session')->isAllowed($aclResource);
    }

    /**
     * init the initiator
     *
     * @access protected
     * @return false||Transoft_Callcenter_Model_Initiator
     */
    protected function _initInitiator()
    {
        $initiator = $this->_checkCallcenterUser() ? Mage::getModel('transoft_callcenter/initiator') : false;
        return $initiator;
    }

    /**
     * Check is user in callcenter role
     *
     * @return bool
     */
    protected function _checkCallcenterUser()
    {
        /** @var Transoft_Callcenter_Model_Initiator $initiatorModel */
        $initiatorModel = Mage::getModel('transoft_callcenter/initiator');
        return $initiatorModel->isCallcenterUser();
    }

    /**
     * Redirect to sales order view action
     */
    public function getOrderAction()
    {
        $initiator = $this->_initInitiator();
        if (!$initiator) {
            $this->_getSession()->addError(
                Mage::helper('transoft_callcenter')->__('Ви не являетесь пользователем коллцентра.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $this->_getSession()->addNotice(
            Mage::helper('transoft_callcenter')->__('Вы добавлены в очередь на полечение заказа')
        );
        $this->_redirect('*/sales_order/');
    }

    /**
     * Remove initiator from order
     */
    public function removeInitiatorAction()
    {
        if ($orderId = (int)$this->getRequest()->getParam('order_id')) {
            try {
                Mage::getModel('transoft_callcenter/initiator')->removeInitiator($orderId);
                $this->_getSession()->addSuccess(
                    Mage::helper('transoft_callcenter')->__('The initiator has been deleted.')
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->getResponse()->setRedirect(
            $this->getUrl('*/sales_order/', array('store' => $this->getRequest()->getParam('store')))
        );
    }

    /**
     * mass delete initiator
     *
     * @access public
     * @return void
     */
    public function massRemoveInitiatorAction()
    {
        $orderIds = $this->getRequest()->getParam('order_ids');
        if (!is_array($orderIds)) {
            $this->_getSession()->addError($this->__('Please select initiators.'));
        } else {
            try {
                Mage::getModel('transoft_callcenter/initiator')->removeInitiator($orderIds);
                $this->_getSession()->addSuccess(
                    Mage::helper('transoft_callcenter')->__('The initiator has been deleted.')
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/sales_order/');
    }
}
