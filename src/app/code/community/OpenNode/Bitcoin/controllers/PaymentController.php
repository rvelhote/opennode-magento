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
 * Class OpenNode_Bitcoin_PaymentController
 */
class OpenNode_Bitcoin_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     *
     */
    public function successAction()
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');

        try {
            $order = $session->getLastRealOrder();
            $session->setData('last_success_quote_id', $order->getQuoteId());
            $this->_redirect('checkout/onepage/success');
            return;
        } catch (Exception $e) {
            Mage::logException($e);
            $session->addError($e->getMessage());
        }

        $this->_redirect('checkout/cart');
    }

    /**
     *
     */
    public function cancelAction()
    {
        /** @var OpenNode_Bitcoin_Helper_Data $helper */
        $helper = Mage::helper('opennode_bitcoin');

        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');

        if (!$this->_validateFormKey()) {
            $session->addError($helper->__('Could not validate your cancelation request!'));
            $this->_redirectReferer();
            return;
        }

        $order = $session->getLastRealOrder();
        if (!$order->getId()) {
            $session->addError($helper->__('You tried to cancel an order that does not exist!'));
            $this->_redirect('checkout/cart');
        }

        /** @var Mage_Core_Model_Resource_Transaction $transaction */
        $transaction = Mage::getModel('core/resource_transaction');

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        $quote->setIsActive(true);

        $comment = $helper->__('User canceled the order during the payment phase');
        $order->cancel()->addStatusHistoryComment($comment);

        $transaction->addObject($quote);
        $transaction->addObject($order);

        try {
            $transaction->save();
            $session->setQuoteId($order->getQuoteId());

            $message = $helper->__('Your order is now canceled. Just keep shopping normally if you wish to resume it.');
            $session->addNotice($message);
        } catch (Exception $e) {
            Mage::logException($e);
            $session->addError($e->getMessage());
        }

        $this->_redirect('checkout/cart');
    }

    /**
     * @throws Mage_Core_Exception
     * @throws Zend_Controller_Response_Exception
     *
     * TODO Handle possible errors getting the charge
     */
    public function statusAction()
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');

        if (!$this->_validateFormKey()) {
            $this->getResponse()->setHttpResponseCode(403);
            return;
        }

        $order = $session->getLastRealOrder();
        if (!$order->getId()) {
            $this->getResponse()->setHttpResponseCode(404);
            return;
        }

        /** @var OpenNode_Bitcoin_Model_Bitcoin $method */
        $method = $order->getPayment()->getMethodInstance();

        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $this->getResponse()->setBody($method->getCharge()->asJson());
    }
}