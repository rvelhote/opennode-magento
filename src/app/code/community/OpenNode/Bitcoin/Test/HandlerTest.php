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
 * Class HandlerTest
 */
class HandlerTest extends TestCase
{
    use OpenNode_Bitcoin_Test_Trait_Order;

    /**
     * @return Varien_Db_Adapter_Interface
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
    protected function setUp(): void
    {
        //$this->getConnection()->beginTransaction();
    }

    /**
     *
     */
    protected function tearDown(): void
    {
        //$this->getConnection()->rollBack();
    }

    /**
     * @throws Exception
     */
    public function testHandleProcessing()
    {
        /** @var OpenNode_Bitcoin_Model_Order $order */
        $order = Mage::getModel('opennode_bitcoin/order');
        $order->loadByIncrementId($this->createNewOrder()->getIncrementId());
        $this->assertNotNull($order);

        $this->assertEquals(OpenNode_Bitcoin_Model_Order::STATE_PENDING_PAYMENT, $order->getState());
        $this->assertEquals(OpenNode_Bitcoin_Model_Order::STATE_PENDING_PAYMENT, $order->getStatus());

        $order->handleProcessing()->save();

        $this->assertEquals(OpenNode_Bitcoin_Model_Order::STATE_PENDING_PAYMENT, $order->getState());
        $this->assertEquals(OpenNode_Bitcoin_Model_Order::STATE_PENDING_PAYMENT, $order->getStatus());
    }

    /**
     * @throws Exception
     */
    public function testHandlePaid()
    {
        /** @var OpenNode_Bitcoin_Model_Order $order */
        $order = Mage::getModel('opennode_bitcoin/order');
        $order->loadByIncrementId($this->createNewOrder()->getIncrementId());
        $this->assertNotNull($order);

        $this->assertEquals(OpenNode_Bitcoin_Model_Order::STATE_PENDING_PAYMENT, $order->getState());
        $this->assertEquals(OpenNode_Bitcoin_Model_Order::STATE_PENDING_PAYMENT, $order->getStatus());

        $order->handlePaid()->save();

        $this->assertEquals(OpenNode_Bitcoin_Model_Order::STATE_PROCESSING, $order->getState());
        $this->assertEquals(OpenNode_Bitcoin_Model_Order::STATE_PROCESSING, $order->getStatus());
    }

    /**
     * @throws Exception
     */
    public function testHandleUnderpaid()
    {
        /** @var OpenNode_Bitcoin_Model_Order $order */
        $order = Mage::getModel('opennode_bitcoin/order');
        $order->loadByIncrementId($this->createNewOrder()->getIncrementId());
        $this->assertNotNull($order);

        $this->assertEquals(OpenNode_Bitcoin_Model_Order::STATE_PENDING_PAYMENT, $order->getState());
        $this->assertEquals(OpenNode_Bitcoin_Model_Order::STATE_PENDING_PAYMENT, $order->getStatus());

        $order->handleUnderpaid(10, 20)->save();

        $this->assertEquals(OpenNode_Bitcoin_Model_Order::STATE_PENDING_PAYMENT, $order->getState());
        $this->assertEquals(OpenNode_Bitcoin_Model_Order::STATE_PENDING_PAYMENT, $order->getStatus());
    }

    /**
     * @throws Exception
     */
    public function testHandleRefunded()
    {
        /** @var OpenNode_Bitcoin_Model_Order $order */
        $order = Mage::getModel('opennode_bitcoin/order');
        $order->loadByIncrementId($this->createNewOrder()->getIncrementId());

        $this->assertNotNull($order);

        $order->handlePaid()->save();
        $this->assertTrue($order->canCreditmemo());

        /** @var OpenNode_Bitcoin_Model_Order $orderToRefund */
        $orderToRefund = Mage::getModel('opennode_bitcoin/order');
        $orderToRefund->loadByIncrementId($order->getIncrementId());

        $orderToRefund->handleRefund()->save();
        $this->assertGreaterThan(0, $orderToRefund->getCreditmemosCollection()->getSize());

        $this->assertEquals(OpenNode_Bitcoin_Model_Order::STATE_CLOSED, $orderToRefund->getState());
        $this->assertEquals(OpenNode_Bitcoin_Model_Order::STATE_CLOSED, $orderToRefund->getStatus());
    }
}
