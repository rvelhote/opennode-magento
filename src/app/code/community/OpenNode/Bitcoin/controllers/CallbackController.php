<?php
/*
 * The MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * Class OpenNode_Bitcoin_CallbackController
 */
class OpenNode_Bitcoin_CallbackController extends Mage_Core_Controller_Front_Action
{
    /** @var OpenNode_Bitcoin_Helper_Logger */
    protected $logger;

    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->logger = Mage::helper('opennode_bitcoin/logger');
    }

    /**
     * @throws Zend_Controller_Response_Exception
     */
    public function indexAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->logger->error('Callback request denied because it is not a POST request');
            $this->getResponse()->setHttpResponseCode(405)->sendResponse();
            return;
        }

        $this->logger->info(Mage::helper('core')->jsonEncode($this->getRequest()->getParams()));

        /** @var OpenNode_Bitcoin_Model_Callback $callback */
        $callback = Mage::getModel('opennode_bitcoin/callback');
        $callback->setData($this->getRequest()->getParams());

        if (!$callback->verify()) {
            $this->logger->error('Callback request denied because HMAC verification failed');
            $this->getResponse()->setHttpResponseCode(403)->sendResponse();
            return;
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->loadByIncrementId($callback->getIncrementId());
        if (!$order->getEntityId()) {
            $this->logger->error(sprintf('Order # %s does not exist. Check callback!', $callback->getIncrementId()));
            $this->getResponse()->setHttpResponseCode(404);
            return;
        }

        if ($order->getStatus() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
            $this->logger->error(sprintf('Order # %s is already paid', $callback->getIncrementId()));
            $this->getResponse()->setHttpResponseCode(409);
            return;
        }

        try {
            if ($callback->getStatus() != OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_PAID) {
                $order->addStatusHistoryComment($this->__('Current status: %s', mb_strtoupper($callback->getStatus())));
            }

            if ($callback->getStatus() == OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_PAID) {
                $order->getPayment()->capture(null);

                if (!$order->getEmailSent()) {
                    $order->queueNewOrderEmail()->setEmailSent(true);
                }
            }

            $order->save();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->logger->log($e->getMessage());
            $this->getResponse()->setHttpResponseCode(503);
        }
    }
}
