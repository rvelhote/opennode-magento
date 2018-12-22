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
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);

        $this->_config = Mage::helper('opennode_bitcoin/config');
        $this->_order = $args['order'];

        $this->setTemplate('opennode/bitcoin/checkout/redirect.phtml');
    }

    /**
     * @return array|mixed|null
     */
    public function getParams()
    {
        return json_decode($this->_order->getPayment()->getAdditionalInformation(OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_PARAMS_KEY));
    }

    /**
     * @return bool|\OpenNode\Merchant\Charge
     *
     * TODO Check or load transaction here?
     */
    public function getCharge()
    {
        if (!$this->_charge) {
            $authentication = array(
                'environment' => $this->_config->getEnvironment(),
                'auth_token' => $this->_config->getAuthToken(),
                'curlopt_ssl_verifypeer' => true,
            );

            $transactionId = $this->_order->getPayment()->getAdditionalInformation(OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_TXN_ID_KEY);
            $this->_charge = \OpenNode\Merchant\Charge::find($transactionId, [], $authentication);
        }

        return $this->_charge;
    }

    /**
     * @return Mage_Core_Model_Store
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getStore()
    {
        return Mage::app()->getStore();
    }
}
