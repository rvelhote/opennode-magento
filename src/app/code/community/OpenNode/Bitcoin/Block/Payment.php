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

use OpenNode_Bitcoin_Model_Bitcoin as PaymentMethod;

/**
 * Class OpenNode_Bitcoin_Block_Payment
 */
class OpenNode_Bitcoin_Block_Payment extends Mage_Core_Block_Template
{
    /** @var OpenNode_Bitcoin_Helper_Config */
    protected $_config;

    /** @var Mage_Sales_Model_Order */
    protected $_order;

    /** @var \OpenNode\Merchant\Charge */
    protected $_charge = null;

    /**
     * OpenNode_Bitcoin_Block_Payment constructor.
     * @param array $args
     * @throws Mage_Core_Exception
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);

        if (!isset($args['order'])) {
            Mage::throwException('The payment block must contain an \'order\' key');
        }

        if (!($args['order'] instanceof Mage_Sales_Model_Order)) {
            Mage::throwException('The payment block must contain an order key with an Mage_Sales_Model_Order object');
        }

        $this->_config = Mage::helper('opennode_bitcoin/config');
        $this->_order = $args['order'];

        $this->setTemplate('opennode/bitcoin/checkout/redirect.phtml');
    }

    /**
     * @return array|mixed|null
     */
    public function getParams()
    {
        /** @var Mage_Core_Helper_Data $core */
        $core = Mage::helper('core');
        $payment = $this->getOrder()->getPayment();

        return $core->jsonDecode($payment->getAdditionalInformation(PaymentMethod::OPENNODE_PARAMS_KEY));
    }

    /**
     * @return Mage_Sales_Model_Order|mixed
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return string
     */
    public function getStoreName()
    {
        return $this->getOrder()->getStore()->getFrontendName();
    }

    /**
     * @return bool|\OpenNode\Merchant\Charge
     * @throws Mage_Core_Exception
     */
    public function getCharge()
    {
        /** @var OpenNode_Bitcoin_Model_Bitcoin $method */
        $method = $this->getOrder()->getPayment()->getMethodInstance();
        return $method->getCharge();
    }

    /**
     * Get the URL that's used to verify the status of the charge in the Blockchain or the Lightning Network.
     * @return string A URL with an FormKey for validation
     */
    public function getStatusUrl()
    {
        /** @var Mage_Core_Model_Session $session */
        $session = Mage::getSingleton('core/session');
        return Mage::getUrl('opennode_bitcoin/payment/status', ['form_key' => $session->getFormKey()]);
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        /** @var Mage_Core_Model_Session $session */
        $session = Mage::getSingleton('core/session');
        return Mage::getUrl('opennode_bitcoin/payment/cancel', ['form_key' => $session->getFormKey()]);
    }
}