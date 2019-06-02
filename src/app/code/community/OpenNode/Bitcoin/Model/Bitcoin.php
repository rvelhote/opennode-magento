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
 * Class OpenNode_Bitcoin_Model_Bitcoin
 */
class OpenNode_Bitcoin_Model_Bitcoin extends Mage_Payment_Model_Method_Abstract
{
    /** @var string */
    const OPENNODE_TXN_ID_KEY = 'opennode_txn_id';

    /** @var string */
    const OPENNODE_PARAMS_KEY = 'opennode_params';

    /** @var string */
    const OPENNODE_STATUS_PAID = 'paid';

    /** @var string */
    const OPENNODE_STATUS_PROCESSING = 'processing';

    /** @var string */
    const OPENNODE_STATUS_UNPAID = 'unpaid';

    /** @var string */
    protected $_code = 'opennode_bitcoin';

    /** @var string */
    protected $_formBlockType = 'opennode_bitcoin/form';

    /** @var string */
    protected $_infoBlockType = 'opennode_bitcoin/info';

    /** @var OpenNode_Bitcoin_Helper_Config */
    protected $_config;

    /** @var Mage_Sales_Model_Order */
    protected $_order;

    /** @var Mage_Sales_Model_Quote */
    protected $_quote;

    /** @var OpenNode_Bitcoin_Model_Charge */
    protected $_charge;

    /** @var OpenNode_Bitcoin_Helper_Logger */
    protected $_logger;

    /** @var bool */
    protected $_canCapture = true;

    /** @var bool */
    protected $_isInitializeNeeded = true;

    /**
     * OpenNode_Bitcoin_Model_Bitcoin constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // FIXME Perhaps use getConfigData instead of this helper?
        $this->_config = Mage::helper('opennode_bitcoin/config');
        $this->_logger = Mage::helper('opennode_bitcoin/logger');
    }

    /**
     * Get order model
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->_order) {
            /** @var Mage_Sales_Model_Order_Payment $payment */
            $payment = $this->getInfoInstance();
            $this->_order = $payment->getOrder();
        }

        return $this->_order;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (!$this->_quote) {
            /** @var \Mage_Sales_Model_Quote_Payment $payment */
            $payment = $this->getInfoInstance();
            $this->_quote = $payment->getQuote();
        }

        return $this->_quote;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        if (!$this->getOrder()) {
            return $this->getQuote()->getQuoteCurrencyCode();
        }

        return $this->getOrder()->getOrderCurrencyCode();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        /** @var Mage_Core_Helper_Data $core */
        $core = Mage::helper('core');

        $store = $this->getOrder()->getStore()->getFrontendName();
        $amount = $core->currency($this->getOrder()->getGrandTotal(), true, false);

        return sprintf('Pay %s to %s', $amount, $store);
    }


    /**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return Mage_Payment_Model_Method_Abstract
     * @throws Exception
     */
    public function initialize($paymentAction, $stateObject)
    {
        /** @var Mage_Core_Helper_Data $core */
        $core = Mage::helper('core');

        $order = $this->getOrder();
        $payment = $this->getOrder()->getPayment();

        $stateObject->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
        $stateObject->setIsNotified(false);

        $authentication = [
            'environment' => $this->_config->getEnvironment(),
            'auth_token' => $this->_config->getAuthToken(),
            'curlopt_ssl_verifypeer' => true,
        ];

        $params = [
            'description' => $this->getDescription(),
            'amount' => $order->getGrandTotal(),
            'currency' => $order->getOrderCurrencyCode(),
            'order_id' => $order->getIncrementId(),
            'email' => $order->getCustomerEmail(),
            'name' => $order->getCustomerName(),
            'callback_url' => Mage::getUrl('opennode_bitcoin/callback/index'),
            'auto_settle' => $this->_config->isAutoSettle(),
        ];

        /** @var OpenNode_Bitcoin_Model_Charge _charge */
        $this->_charge = Mage::getModel('opennode_bitcoin/charge', [
            'auth' => $authentication,
            'params' => $params,
        ]);
        $this->_charge->create();

        $payment->setAdditionalInformation(self::OPENNODE_TXN_ID_KEY, $this->_charge->getTransactionId());
        $payment->setAdditionalInformation(self::OPENNODE_PARAMS_KEY, $core->jsonEncode($params));

        return parent::initialize($paymentAction, $stateObject);
    }

    /**
     * @param Varien_Object $payment
     * @param float $amount
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $transactionId = $this->getOrder()->getPayment()->getAdditionalInformation(OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_TXN_ID_KEY);
        $payment->setData('transaction_id', $transactionId);

        return parent::capture($payment, $amount);
    }

    /**
     * @return bool
     */
    public function canUseCheckout()
    {
        if ($this->_config->isProductionMode() && !$this->_config->getProductionApiKey()) {
            $this->_logger->warn('PRODUCTION mode is ON but an API Key is missing. Payment method disabled.');
            return false;
        }

        if ($this->_config->isTestMode() && !$this->_config->getDevelopmentApiKey()) {
            $this->_logger->warn('DEVELOPMENT mode is ON but an API Key is missing. Payment method disabled.');
            return false;
        }

        if ($this->_config->getProductionApiKey() == $this->_config->getDevelopmentApiKey()) {
            $this->_logger->warn('DEVELOPMENT and PRODUCTION API Keys are the same. Payment method disabled.');
            return false;
        }

        return true;
    }

    /**
     * @return Mage_Payment_Model_Method_Abstract
     * @throws Zend_Cache_Exception
     * @throws Zend_Http_Client_Exception
     * @throws Mage_Core_Exception
     */
    public function validate()
    {
        /** @var Mage_Core_Helper_Data $core */
        $core = Mage::helper('core');
        $cache = Mage::app()->getCache();

        $currencies = $cache->load('opennode_bitcoin_currencies');
        if (!$currencies) {
            $http = new Varien_Http_Client('https://api.opennode.co/v1/currencies');

            $response = $http->request(Varien_Http_Client::GET)->getBody();
            $response = $core->jsonDecode($response);

            $currencies = $core->jsonEncode($response['data']);
            $cache->save($currencies, 'opennode_bitcoin_currencies', ['OPENNODE']);
        }

        $currencies = $core->jsonDecode($currencies);

        if (!in_array($this->getCurrencyCode(), $currencies)) {
            $message = 'The selected currency is not accepted by the payment gateway';
            Mage::throwException(Mage::helper('opennode_bitcoin')->__($message));
        }

        return parent::validate();
    }

    /**
     * Return url for redirection after order placed
     * @return string
     *
     * TODO Good or bad idea to have a form key for validation here? Maybe the session will expire before the payment?
     */
    public function getOrderPlaceRedirectUrl()
    {
        /** @var Mage_Core_Model_Session $session */
        $session = Mage::getSingleton('core/session');
        return Mage::getUrl('opennode_bitcoin/payment/payment', ['form_key' => $session->getFormKey()]);
    }

    /**
     * @return OpenNode_Bitcoin_Model_Charge
     */
    public function getCharge()
    {
        if ($this->_charge) {
            return $this->_charge;
        }

        $payment = $this->getOrder()->getPayment();
        $transactionId = $payment->getAdditionalInformation(self::OPENNODE_TXN_ID_KEY);

        $authentication = [
            'environment' => $this->_config->getEnvironment(),
            'auth_token' => $this->_config->getAuthToken(),
            'curlopt_ssl_verifypeer' => true,
        ];

        /** @var OpenNode_Bitcoin_Model_Charge _charge */
        $this->_charge = Mage::getModel('opennode_bitcoin/charge', [
            'auth' => $authentication,
            'transaction_id' => $transactionId,
        ]);
        $this->_charge->getCharge();

        return $this->_charge;
    }
}