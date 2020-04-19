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
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
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
 * Trait OpenNode_Bitcoin_Test_Trait_Order
 */
trait OpenNode_Bitcoin_Test_Trait_Order {
    /**
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    protected function createNewOrder()
    {
        $store = Mage::app()->getStore(1);

        $paymentMethod = 'opennode_bitcoin';
        $shippingMethod = 'freeshipping_freeshipping';

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getModel('sales/quote');
        $quote->setStore($store);

        $quote->setQuoteCurrencyCode($store->getBaseCurrencyCode());
        $quote->setBaseCurrencyCode($store->getBaseCurrencyCode());

        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer')->load(60);
        $quote->assignCustomer($customer);

        /** @var Mage_Catalog_Model_Product $product */
        foreach ([395, 380] as $productId) {
            $product = Mage::getModel('catalog/product')->load($productId);
            $quote->addProduct($product, 1);
        }

        $quote->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates();
        $quote->getShippingAddress()->setShippingMethod($shippingMethod)->setData('payment_method', $paymentMethod);

        $quote->getPayment()->importData(['method' => $paymentMethod]);
        $quote->collectTotals();
        $quote->save();

        /** @var Mage_Sales_Model_Service_Quote $service */
        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();

        return $service->getOrder();
    }
}