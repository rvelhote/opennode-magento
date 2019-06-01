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
 * Class OnChainTest
 */
class OnChainTest extends TestCase
{
    /**
     *
     */
    public function testLightningInvoice()
    {
        $dataset = [
            'v' => 'ADDRESS',
            'tx' => 'TX',
            'created_at' => '2019-12-31 15:00:00',
        ];

        $amount = 0.1;
        $message = 'TEST_MESSAGE';

        /** @var OpenNode_Bitcoin_Model_Invoice_OnChain $instance */
        $instance = Mage::getModel('opennode_bitcoin/invoice_onChain', $dataset);

        $this->assertEquals($dataset['address'], $instance->getAddress());
        $this->assertEquals($dataset['tx'], $instance->getTx());
        $this->assertEquals($dataset['settled_at'], $instance->getSettledAt());

        // FIXME Does not check if the formatted data is invalid. Check timezones.
        $this->assertInstanceOf(Zend_Date::class, $instance->getSettledAtDate());

        $result = 'amount=' . $amount . '&label=' . $message;
        $this->assertEquals('bitcoin:' . $dataset['address'] . '?' . $result, $instance->formatUri($amount, $message));
    }
}