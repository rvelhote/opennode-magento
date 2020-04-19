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
    /**
     * @throws Zend_Controller_Response_Exception
     */
    public function indexAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->getResponse()->setHttpResponseCode(405)->sendResponse();
            return;
        }

        /** @var OpenNode_Bitcoin_Model_Callback $callback */
        $callback = new OpenNode_Bitcoin_Model_Callback();
        $callback->setData($this->getRequest()->getParams());



        /** @var OpenNode_Bitcoin_Model_Sales_Order $order */
        $order = Mage::getModel('opennode_bitcoin/sales_order')->loadByIncrementId($callback->getIncrementId());

        try {
            if (!$order->getEntityId()) {
                throw new Exception('Order does not exist', 4);
            }

            $order->verifyCallback($callback)->getPayment()->capture(null);

            if (!$order->getEmailSent()) {
                $order->queueNewOrderEmail();
                $order->setEmailSent(true);
            }

            $order->save();
        } catch (Exception $e) {
            Mage::logException($e);

            /** @var OpenNode_Bitcoin_Helper_Logger $config */
            $logger = Mage::helper('opennode_bitcoin/logger');
            $logger->error($e->getMessage());

            $this->getResponse()->setHttpResponseCode(404);
        }
    }
}