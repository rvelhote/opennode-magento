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
 * @method Varien_Data_Collection getTransactions()
 */
class OpenNode_Bitcoin_Model_Charge extends Varien_Object
{
    /** @var bool|Charge */
    protected $_charge = null;

    /** @var Mage_Sales_Model_Order */
    protected $_order = null;

    /** @var OpenNode_Bitcoin_Helper_Data */
    protected $_helper = null;

    /**
     * OpenNode_Bitcoin_Model_Charge constructor.
     * @param $args
     */
    public function __construct($args)
    {
        parent::__construct($args);
        $this->_helper = Mage::helper('opennode_bitcoin');
    }

    /**
     * @return $this|bool|Charge
     * @throws Exception
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
     * Creates a new CHARGE using the OpenNode API
     * @return $this
     * @throws Exception
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
     * Takes the OpenNode\Merchant\Charge object and converts it to a Magento compatible object so we can it Magento
     * style in style!
     * @return $this
     * @throws Exception
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

        $this->setData('transactions', new Varien_Data_Collection());
        if ($this->_charge->transactions && is_array($this->_charge->transactions)) {
            $transactions = new Varien_Data_Collection();

            foreach ($this->_charge->transactions as $transaction) {
                $item = Mage::getModel('opennode_bitcoin/transaction', $transaction);
                $transactions->addItem($item);
            }

            $this->setData('transactions', $transactions);
        }

        $this->setData('lightning_invoice', Mage::getModel('opennode_bitcoin/invoice_ligthning',
            $this->_charge->lightning_invoice
        ));

        $this->setData('chain_invoice', Mage::getModel('opennode_bitcoin/invoice_onChain',
            $this->_charge->chain_invoice
        ));

        return $this;
    }

    /**
     * Converts the transaction amount from SATS to BTC
     * @return string The transaction amount converted from SATS to BTC
     */
    public function getAmountBtc()
    {
        if (function_exists('bcdiv')) {
            return bcdiv($this->getAmount(), 100000000, 8);
        }

        return number_format($this->getAmount() / 100000000, 8, '.', '');
    }

    /**
     * Checks if the current CHARGE status is PAID
     * This status means that the transaction is confirmed on the Bitcoin blockchain
     * @return bool
     */
    public function isPaid()
    {
        return $this->getStatus() === OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_PAID;
    }

    /**
     * Checks if the current CHARGE status is PROCESSING
     * This status means that the transaction is seen for the first time on the mempool
     * @return bool
     */
    public function isProcessing()
    {
        return $this->getStatus() === OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_PROCESSING;
    }

    /**
     * Checks if the current CHARGE status is UNPAID
     * This status is the first status after a CHARGE is created and remains this way until a transaction is seen in
     * the mempool
     * @return bool
     */
    public function isUnpaid()
    {
        return $this->getStatus() === OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_UNPAID;
    }

    /**
     * Checks if the current CHARGE status is UNDERPAID
     * This status means that the transaction is seen but the charge is only partially paid. Waiting for the user to
     * send the remainder
     * @return bool
     */
    public function isUnderpaid()
    {
        return $this->getStatus() === OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_UNDERPAID;
    }

    /**
     * Checks if the current CHARGE status is UNDERPAID
     * This status means that the transaction was underpaid but the user canceled it, asking for a refund
     * @return bool
     */
    public function isRefunded()
    {
        return $this->getStatus() === OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_REFUNDED;
    }

    /**
     * @return string
     */
    public function getStatusLabel()
    {
        if ($this->isPaid()) {
            return $this->_helper->__('Paid');
        }

        if ($this->isProcessing()) {
            return $this->_helper->__('Processing');
        }

        if ($this->isUnderpaid()) {
            return $this->_helper->__('Underpaid');
        }

        return $this->_helper->__('Unpaid');
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
                'underpaid' => $this->isUnderpaid(),
            ],
            'lightning' => [
                'address' => $this->getLightningInvoice()->getAddress(),
                'uri' => $this->getLightningInvoice()->formatUri(),
            ],
            'onchain' => [
                'address' => $this->getChainInvoice()->getAddress(),
                'uri' => $this->getChainInvoice()->formatUri($this->getAmountBtc(), $store->getFrontendName()),
                'tx' => $this->getChainInvoice()->getTx(),
            ],
        ];

        return $core->jsonEncode($data);
    }

    /**
     * @return int
     */
    public function getTotalTransactions()
    {
        return count($this->getTransactions());
    }
}
