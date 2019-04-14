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

use OpenNode\Merchant\Charge;

/**
 * Class OpenNode_Bitcoin_Model_Charge
 * @method string getTransactionId()
 * @method array getAuth()
 * @method string getCreatedAt()
 * @method string getOrderId()
 * @method int getAmount()
 * @method string getStatus()
 * @method float getSourceFiatValue()
 * @method string getCurrency()
 * @method array getParams()
 * @method bool getAutoSettle()
 * @method OpenNode_Bitcoin_Model_Invoice_Ligthning getLightningInvoice()
 * @method OpenNode_Bitcoin_Model_Invoice_OnChain getChainInvoice()
 */
class OpenNode_Bitcoin_Model_Charge extends Varien_Object
{
    /** @var bool|Charge */
    protected $_charge = null;

    /** @var Mage_Sales_Model_Order */
    protected $_order = null;

    /**
     * OpenNode_Bitcoin_Model_Charge constructor.
     * @param $args
     */
    public function __construct($args)
    {
        parent::__construct($args);
    }

    /**
     * @return $this|bool|Charge
     */
    public function getCharge()
    {
        if (!is_null($this->_charge)) {
            return $this->_charge;
        }

        $this->_charge = Charge::find($this->getTransactionId(), [], $this->getAuth());
        $this->mapChargeToObject();
        return $this;
    }

    /**
     * @return $this
     */
    public function create()
    {
        if (!is_null($this->_charge)) {
            return $this;
        }

        $this->_charge = Charge::create($this->getParams(), $this->getAuth());
        $this->mapChargeToObject();
        return $this;
    }

    /**
     * @return $this
     */
    protected function mapChargeToObject()
    {
        if (is_null($this->_charge)) {
            return $this;
        }

        $this->setData('transaction_id', $this->_charge->id);
        $this->setData('description', $this->_charge->description);
        $this->setData('amount', $this->_charge->amount);
        $this->setData('status', $this->_charge->status);
        $this->setData('fiat_value', $this->_charge->fiat_value);
        $this->setData('source_fiat_value', $this->_charge->source_fiat_value);
        $this->setData('currency', $this->_charge->currency);
        $this->setData('created_at', $this->_charge->created_at);
        $this->setData('order_id', $this->_charge->order_id);
        $this->setData('success_url', $this->_charge->success_url);
        $this->setData('notes', $this->_charge->notes);

        $this->setData('lightning_invoice', Mage::getModel('opennode_bitcoin/invoice_ligthning',
            $this->_charge->lightning_invoice
        ));

        $this->setData('chain_invoice', Mage::getModel('opennode_bitcoin/invoice_onChain',
            $this->_charge->chain_invoice
        ));

        return $this;
    }

    /**
     * @return string
     */
    public function getAmountBtc()
    {
        if (function_exists('bcdiv')) {
            return bcdiv($this->getAmount(), 100000000, 8);
        }

        return number_format($this->getAmount() / 100000000, 8, '.', '');
    }

    /**
     * @return bool
     */
    public function isPaid()
    {
        return $this->getStatus() === OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_PAID;
    }

    /**
     * @return bool
     */
    public function isProcessing()
    {
        return $this->getStatus() === OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_PROCESSING;
    }

    /**
     * @return bool
     */
    public function isUnpaid()
    {
        return $this->getStatus() === OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_UNPAID;
    }

    /**
     * @return Mage_Sales_Model_Order|null
     */
    public function getOrder()
    {
        if (is_null($this->_order)) {
            $this->_order = Mage::getModel('sales/order');
            $this->_order = $this->_order->loadByIncrementId($this->getOrderId());
        }
        return $this->_order;
    }

    /**
     * @return Zend_Date
     */
    public function getCreatedAtDate()
    {
        return Mage::app()->getLocale()->date(
            Varien_Date::toTimestamp($this->getCreatedAt()),
            null,
            null,
            true
        );
    }

    /**
     * @return string
     */
    public function asJson()
    {
        /** @var Mage_Core_Helper_Data $core */
        $core = Mage::helper('core');
        $store = $this->getOrder()->getStore();

        $data = [
            'id' => $this->getTransactionId(),
            'status' => [
                'paid' => $this->isPaid(),
                'unpaid' => $this->isUnpaid(),
                'processing' => $this->isProcessing(),
            ],
            'lightning' => [
                'address' => $this->getLightningInvoice()->getAddress(),
                'uri' => $this->getLightningInvoice()->formatUri(),
            ],
            'onchain' => [
                'address' => $this->getChainInvoice()->getAddress(),
                'uri' => $this->getChainInvoice()->formatUri($this->getAmountBtc(), $store->getFrontendName()),
            ],
        ];

        return $core->jsonEncode($data);
    }
}