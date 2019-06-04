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
 * Class OpenNode_Bitcoin_Helper_Database
 */
class OpenNode_Bitcoin_Helper_Database extends Mage_Core_Helper_Abstract
{
    /**
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    public function getPendingPaymentOrders()
    {
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

        return $orders;
    }
}