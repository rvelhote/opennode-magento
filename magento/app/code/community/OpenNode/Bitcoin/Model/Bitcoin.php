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

    /** @var bool */
    protected $_canCapture = true;

    /** @var bool */
    protected $_isInitializeNeeded = true;

    /** @var bool */
    protected $_isGateway = true;

    /**
     * OpenNode_Bitcoin_Model_Bitcoin constructor.
     */
    public function __construct()
    {
        $this->_config = Mage::helper('opennode_bitcoin/config');
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
        Mage::log('Calling INITIALIZE');

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
            'amount' => $this->getOrder()->getGrandTotal(),
            'currency' => $this->getOrder()->getOrderCurrencyCode(),
            'order_id' => $this->getOrder()->getIncrementId(),
            'email' => $this->getOrder()->getCustomerEmail(),
            'name' => $this->getOrder()->getCustomerName(),
            'callback_url' => Mage::getUrl('opennode_bitcoin/callback/index'),
            'auto_settle' => $this->_config->isAutoSettle(),
        );

        $this->_charge = \OpenNode\Merchant\Charge::create($params, $authentication);

        $this->getOrder()->getPayment()->setAdditionalInformation(self::OPENNODE_TXN_ID_KEY, $this->_charge->id);
        $this->getOrder()->getPayment()->setAdditionalInformation(self::OPENNODE_PARAMS_KEY, json_encode($params));

        return parent::initialize($paymentAction, $stateObject);
    }

    /**
     * Capture payment through api
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return Phoenix_Moneybookers_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $payment->setStatus(self::STATUS_APPROVED)
            ->setTransactionId($this->getTransactionId())
            ->setIsTransactionClosed(0);

        return $this;
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
        echo "z";
        exit;
        return parent::refund($payment, $amount);
    }

    /**
     * Cancel payment abstract method
     *
     * @param Varien_Object $payment
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function cancel(Varien_Object $payment)
    {
        echo "t";
        exit;
        return parent::cancel($payment);
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


    /**
     * Return url for redirection after order placed
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('opennode_bitcoin/payment/payment');
    }


//    /**
//     * @return string
//     */
//    public function getOrderPlaceRedirectUrl()
//    {
//        return Mage::getUrl('custompaymentmethod/payment/redirect', array('_secure' => false));
//    }
}