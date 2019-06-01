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
use PHPUnit\Framework\TestCase;

/**
 * Class ChargeTest
 */
class ChargeTest extends TestCase
{
    /** @var OpenNode_Bitcoin_Helper_Config */
    protected $config;

    /**
     * @throws Exception
     */
    public function testMagentoChargeVsOpeNodeCharge()
    {
        $order = $this->createNewOrder();

        /** @var OpenNode_Bitcoin_Model_Bitcoin $method */
        $method = $order->getPayment()->getMethodInstance();
        $magentoCharge = $method->getCharge();

        /** @var OpenNode_Bitcoin_Model_Charge $charge */
        $charge = Mage::getModel('opennode_bitcoin/charge', [
            'transaction_id' => $magentoCharge->getTransactionId(),
            'auth' => [
                'environment' => $this->config->getEnvironment(),
                'auth_token' => $this->config->getAuthToken(),
                'curlopt_ssl_verifypeer' => true,
            ],
        ]);
        $charge->getCharge();

        $this->assertEquals($charge->getTransactionId(), $magentoCharge->getTransactionId());
        $this->assertEquals($charge->isPaid(), $magentoCharge->isPaid());
        $this->assertEquals($charge->isUnpaid(), $magentoCharge->isUnpaid());
        $this->assertEquals($charge->isProcessing(), $magentoCharge->isProcessing());
        $this->assertEquals($charge->getAmount(), $magentoCharge->getAmount());
        $this->assertEquals($charge->getAmountBtc(), $magentoCharge->getAmountBtc());
        $this->assertEquals($charge->getOrder()->getIncrementId(), $magentoCharge->getOrder()->getIncrementId());
        $this->assertEquals($charge->getCreatedAtDate(), $magentoCharge->getCreatedAtDate());
        $this->assertEquals($charge->asJson(), $magentoCharge->asJson());

        $this->assertInstanceOf(OpenNode_Bitcoin_Model_Invoice_Ligthning::class, $charge->getLightningInvoice());
        $this->assertInstanceOf(OpenNode_Bitcoin_Model_Invoice_OnChain::class, $charge->getChainInvoice());
    }

    /**
     * @return \Varien_Db_Adapter_Interface
     */
    protected function getConnection()
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        return $resource->getConnection('default_write');
    }

    /**
     *
     */
    protected function setUp()
    {
        $this->config = Mage::helper('opennode_bitcoin/config');
        $this->getConnection()->beginTransaction();
    }

    /**
     *
     */
    protected function tearDown()
    {
        $this->getConnection()->rollBack();
    }

    /**
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    protected function createNewOrder()
    {
        $store = Mage::app()->getStore(1);

        $paymentMethod = 'opennode_bitcoin';
        $shippingMethod = 'flatrate_flatrate';

        $address = [
            'customer_address_id' => '',
            'prefix' => '',
            'firstname' => 'Satoshi',
            'middlename' => '',
            'lastname' => 'Nakamoto',
            'suffix' => '',
            'company' => '',
            'street' => [
                '0' => 'Bitcoin Avenue',
                '1' => '9th of January 2009',
            ],
            'city' => 'Culver City',
            'country_id' => 'US',
            'region' => 'California',
            'region_id' => '12',
            'postcode' => '90232',
            'telephone' => '888-888-8888',
            'fax' => '',
            'save_in_address_book' => 1,
        ];

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getModel('sales/quote');
        $quote->setStore($store);

        $quote->setQuoteCurrencyCode($store->getBaseCurrencyCode());
        $quote->setBaseCurrencyCode($store->getBaseCurrencyCode());

        $quote->getBillingAddress()->addData($address);
        $quote->getShippingAddress()->addData($address);

        $quote->setCustomerFirstname('Satoshi');
        $quote->setCustomerLastname('Satoshi');
        $quote->setCustomerEmail('satoshi@nakamoto.io');

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