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
 * Class OpenNode_Bitcoin_Model_Invoice_OnChain
 * @method string getTx()
 * @method string getSettledAt()
 */
class OpenNode_Bitcoin_Model_Invoice_OnChain extends Varien_Object
{
    /**
     * @return string
     */
    public function getAddress()
    {
        if (is_null($this->getData('address'))) {
            return '';
        }
        return (string)$this->getData('address');
    }

    /**
     * @param string $amount
     * @param string $message
     * @return string
     */
    public function formatUri($amount, $message)
    {
        $query = [
            'amount' => $amount,
            'label' => $message,
        ];

        return sprintf('bitcoin:%s?%s', $this->getAddress(), http_build_query($query));
    }

    /**
     * @return Zend_Date
     */
    public function getSettledAtDate()
    {
        return Mage::app()->getLocale()->date(
            Varien_Date::toTimestamp($this->getSettledAt()),
            null,
            null,
            true
        );
    }
}