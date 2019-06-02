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
 * Class PurchaseTest
 */
class PaymentTest extends TestCase
{
    use OpenNode_Bitcoin_Test_Trait_Order;

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
     * @throws Exception
     */
    public function testPayment()
    {
        $order = $this->createNewOrder();

        $this->assertNotNull($order);

        $this->assertEquals($order->getState(), 'pending_payment');
        $this->assertEquals($order->getStatus(), 'pending_payment');

        $this->assertNotNull($order->getPayment());
        $this->assertNotEmpty($order->getPayment()->getAdditionalInformation());
        $this->assertInstanceOf(OpenNode_Bitcoin_Model_Bitcoin::class, $order->getPayment()->getMethodInstance());

        $additionalInformation = $order->getPayment()->getAdditionalInformation();
        $this->assertArrayHasKey(OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_TXN_ID_KEY, $additionalInformation);
        $this->assertArrayHasKey(OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_PARAMS_KEY, $additionalInformation);

        /** @var OpenNode_Bitcoin_Model_Bitcoin $method */
        $method = $order->getPayment()->getMethodInstance();
        $this->assertEquals($order->getIncrementId(), $method->getOrder()->getIncrementId());

        $this->assertEquals(
            $method->getCharge()->getTransactionId(),
            $additionalInformation[OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_TXN_ID_KEY]
        );

        $this->assertEquals($method->getCharge()->getSourceFiatValue(), $order->getGrandTotal());
        $this->assertEquals($method->getCharge()->getStatus(), OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_UNPAID);
    }

    /**
     * @throws Exception
     */
    public function testPaymentCapture()
    {
        $order = $this->createNewOrder();
        $this->assertNotNull($order);

        /** @var OpenNode_Bitcoin_Model_Bitcoin $method */
        $method = $order->getPayment()->getMethodInstance();
        $this->assertEquals($order->getIncrementId(), $method->getOrder()->getIncrementId());

        $order->getPayment()->capture(null);
        $order->save();

        $this->assertEquals('processing', $order->getState());
        $this->assertEquals('processing', $order->getStatus());
    }
}