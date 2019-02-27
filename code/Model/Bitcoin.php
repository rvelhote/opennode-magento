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

    /** @var \OpenNode\Merchant\Charge */
    protected $_charge;

    /** @var OpenNode_Bitcoin_Helper_Logger */
    protected $_logger;

    /** @var bool */
    protected $_canCapture = true;

    /** @var bool */
    protected $_isInitializeNeeded = true;

//    /** @var bool */
//    protected $_isGateway = true;

    /**
     * OpenNode_Bitcoin_Model_Bitcoin constructor.
     */
    public function __construct()
    {
        // FIXME Perhaps use getConfigData instead of this helper?
        $this->_config = Mage::helper('opennode_bitcoin/config');
        $this->_logger = Mage::helper('opennode_bitcoin/logger');
        parent::__construct();
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
     * @return string
     */
    public function getDescription()
    {
        $description = [];

        /** @var Mage_Sales_Model_Order_Item $item */
        foreach ($this->getOrder()->getAllVisibleItems() as $item) {
            $description[] = sprintf('%sx%s', $item->getQtyOrdered(), $item->getName());
        }

        return implode('<br>', $description);
    }


    /**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return Mage_Payment_Model_Abstract
     * @throws Mage_Core_Exception
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

        $authentication = array(
            'environment' => $this->_config->getEnvironment(),
            'auth_token' => $this->_config->getAuthToken(),
            'curlopt_ssl_verifypeer' => true,
        );

        $params = array(
            'description' => $this->getDescription(),
            'amount' => $order->getGrandTotal(),
            'currency' => $order->getOrderCurrencyCode(),
            'order_id' => $order->getIncrementId(),
            'email' => $order->getCustomerEmail(),
            'name' => $order->getCustomerName(),
            'callback_url' => Mage::getUrl('opennode_bitcoin/callback/index'),
            'auto_settle' => $this->_config->isAutoSettle(),
        );

        $this->_charge = \OpenNode\Merchant\Charge::create($params, $authentication);

        $payment->setAdditionalInformation(self::OPENNODE_TXN_ID_KEY, $this->_charge->id);
        $payment->setAdditionalInformation(self::OPENNODE_PARAMS_KEY, $core->jsonEncode($params));

        return parent::initialize($paymentAction, $stateObject);
    }

    /**
     * Refund specified amount for payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function refund(Varien_Object $payment, $amount)
    {
        echo "refund";
        return parent::refund($payment, $amount);
    }

    /**
     * Validate payment method information object
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function validate()
    {
        return parent::validate();
    }

    public function capture(Varien_Object $payment, $amount)
    {
        $transactionId = $this->getOrder()->getPayment()->getAdditionalInformation(OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_TXN_ID_KEY);
        $payment->setTransactionId($transactionId);

        return parent::capture($payment, $amount); // TODO: Change the autogenerated stub
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
     * @return bool|\OpenNode\Merchant\Charge
     */
    public function getCharge()
    {
        if ($this->_charge) {
            return $this->_charge;
        }

        $authentication = array(
            'environment' => $this->_config->getEnvironment(),
            'auth_token' => $this->_config->getAuthToken(),
            'curlopt_ssl_verifypeer' => true,
        );

        $transactionId = $this->getOrder()->getPayment()->getAdditionalInformation(OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_TXN_ID_KEY);
        $this->_charge = \OpenNode\Merchant\Charge::find($transactionId, [], $authentication);

        return $this->_charge;
    }
}