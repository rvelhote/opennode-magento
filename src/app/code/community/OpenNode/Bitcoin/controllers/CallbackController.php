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

    /** @var OpenNode_Bitcoin_Helper_Config */
    protected $config;

    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->logger = Mage::helper('opennode_bitcoin/logger');
        $this->config = Mage::helper('opennode_bitcoin/config');
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

        /** @var OpenNode_Bitcoin_Model_Callback $callback */
        $callback = Mage::getModel('opennode_bitcoin/callback');
        $callback->setData($this->getRequest()->getParams());

        $format = '[%s] Callback received for TXN %s';
        $this->logger->info(sprintf($format, $callback->getIncrementId(), $callback->getId()));

        if ($this->config->isDebug()) {
            $this->logger->info('REQUEST: ' . Mage::helper('core')->jsonEncode($this->getRequest()->getParams()));
            $this->logger->info('CALLBACK: ' . Mage::helper('core')->jsonEncode($callback->getData()));
        }

        if (!$callback->verify()) {
            $format = '[%s] HMAC verification failed for TXN %s';
            $this->logger->error(sprintf($format, $callback->getIncrementId(), $callback->getId()));

            $this->getResponse()->setHttpResponseCode(403)->sendResponse();
            return;
        }

        /** @var OpenNode_Bitcoin_Model_Order $order */
        $order = Mage::getModel('opennode_bitcoin/order');
        $order->loadByIncrementId($callback->getIncrementId());

        if (!$order->getEntityId()) {
            $format = '[%s] TXN %s contains an order that does not exist in Magento!';
            $this->logger->error(sprintf($format, $callback->getIncrementId(), $callback->getId()));
            $this->getResponse()->setHttpResponseCode(404);
            return;
        }

        try {
            $order->handleCallback($callback)->save();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->logger->log($e->getMessage());
            $this->getResponse()->setHttpResponseCode(503);
        }
    }
}
