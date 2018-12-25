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
     * TODO Add a configuration to determine if the order should still remain after the lightning payreq expires
     * TODO Add a configuration to specify when orders should be canceled for lack of payment (on-chain)
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

        $orders->addFieldToFilter('payment.method', 'opennode_bitcoin');
        $orders->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);

        if ($orders->getSize()) {
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

            // TODO Type hint this entire line
            $charge = $order->getPayment()->getMethodInstance()->getCharge();

            if (!$charge) {
                $result->errors++;
                $this->_logger->warn(sprintf('Could not obtain a CHARGE object for #%s', $order->getIncrementId()));
                continue;
            }

            $expiresAt = $charge->lightning_invoice['expires_at'];

            if ($now < $expiresAt) {
                $result->skipped++;
                continue;
            }

            $comment = $helper->__('Order automatically canceled after the lightning invoice expired');
            $order->addStatusHistoryComment($comment);
            $order->cancel();

            try {
                // TODO Send an email to the user? Perhaps catch the event with the order is canceled including a reason
                $order->save();
                $result->canceled++;
            } catch (Exception $e) {
                $result->errors++;
                Mage::logException($e);
                $this->_logger->error($e->getMessage());
            }
        }

        $format = 'Finished with %d SKIPPED, %d CANCELED and %s ERRORS';
        $this->_logger->info(sprintf($format, $result->skipped, $result->canceled, $result->errors));
    }
}