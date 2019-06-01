<?php
/**
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
 * Class OpenNode_Bitcoin_Model_Cron
 */
class OpenNode_Bitcoin_Model_Cron
{
    /** @var OpenNode_Bitcoin_Helper_Logger */
    protected $_logger;

    /**
     * OpenNode_Bitcoin_Model_Cron constructor.
     */
    public function __construct()
    {
        $this->_logger = Mage::helper('opennode_bitcoin/logger');
    }

    /**
     * TODO Add a configuration to specify when orders should be canceled
     */
    public function cancel()
    {
        /** @var OpenNode_Bitcoin_Helper_Data $helper */
        $helper = Mage::helper('opennode_bitcoin');

        /** @var Mage_Sales_Model_Resource_Order_Collection $orders */
        $orders = Mage::getModel('sales/order')->getCollection();

        $orders->join(
            ['payment' => 'sales/order_payment'],
            'main_table.entity_id = payment.parent_id',
            ['payment_method' => 'payment.method']
        );

        $orders->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
        $orders->addFieldToFilter('status', Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
        $orders->addFieldToFilter('payment.method', 'opennode_bitcoin');

        if ($orders->getSize() === 0) {
            $this->_logger->info('No orders with the opennode_bitcoin payment method were found!');
            return;
        }

        $this->_logger->info(sprintf('%d orders found with the OPENNODE_BITCOIN payment method', $orders->getSize()));

        $now = time();
        $result = new class
        {
            public $errors = 0;
            public $canceled = 0;
            public $skipped = 0;
        };

        /** @var Mage_Sales_Model_Order $order */
        foreach ($orders as $order) {
            if (!$order->canCancel()) {
                $result->skipped++;
                continue;
            }

            /** @var Mage_Sales_Model_Order_Payment $payment */
            $payment = $order->getPayment();

            /** @var OpenNode_Bitcoin_Model_Bitcoin $method */
            $method = $payment->getMethodInstance();

            /** @var OpenNode_Bitcoin_Model_Charge $charge */
            $charge = $method->getCharge();

            if (!$charge) {
                $result->errors++;
                $this->_logger->warn(sprintf('Could not obtain a CHARGE object for #%s', $order->getIncrementId()));
                continue;
            }

            if (!$charge->isUnpaid()) {
                $result->skipped++;

                $format = 'The CHARGE for ORDER %s is already in the %s STATUS';
                $this->_logger->warn(sprintf($format, $order->getIncrementId(), $charge->getStatus()));
                continue;
            }

            $createdAtTimestamp = $order->getCreatedAtDate()->toString('U');
            $timeRemaining = ($now - $createdAtTimestamp);
            if ($timeRemaining < 3600) {
                $result->skipped++;

                $format = 'ORDER %s still has %s seconds left before cancelation';
                $this->_logger->warn(sprintf($format, $order->getIncrementId(), $timeRemaining));
                continue;
            }

            $comment = $helper->__('Order automatically CANCELED after 1 hour without PAYMENT');
            $order->addStatusHistoryComment($comment);
            $order->cancel();

            $result->canceled++;
        }

        $orders->save();

        $format = 'Finished with %d SKIPPED, %d CANCELED and %s ERRORS';
        $this->_logger->info(sprintf($format, $result->skipped, $result->canceled, $result->errors));
    }
}