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
 * Class OpenNode_Bitcoin_Model_Callback
 *
 * Payload Example:
 * POST callback_url | application/x-www-form-urlencoded
 * {
 *  id: id,
 *  callback_url: callback_url,
 *  success_url: success_url,
 *  status: status,
 *  order_id: order_id,
 *  description: description,
 *  price: price,
 *  fee: fee,
 *  auto_settle: auto_settle,
 *  hashed_order: hashed_order
 * }
 *
 * @method string getCallbackUrl()
 * @method string getStatus()
 * @method string getOrderId()
 * @method string getDescription()
 * @method float getPrice()
 * @method float getFee()
 * @method boolean getAutoSettle()
 * @method string getHashedOrder()
 * @method string getTransactions()
 *
 */
class OpenNode_Bitcoin_Model_Callback extends Varien_Object
{
    /** @var OpenNode_Bitcoin_Helper_Config */
    protected $config;

    /**
     * OpenNode_Bitcoin_Model_Callback constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->config = Mage::helper('opennode_bitcoin/config');
    }

    /**
     * Alternative method to get the order_id parameter with a more Magento-like idiom
     * @return string The increment_id value of the order so it can be loaded
     */
    public function getIncrementId()
    {
        return $this->getOrderId();
    }

    /**
     * Verifies the callback hashed_order parameter against the order id and the authentication token that generated it
     * @return bool Is the hashed_order parameter valid or not
     */
    public function verify()
    {
        $calculated = hash_hmac('sha256', $this->getId(), $this->config->getAuthToken());

        if (!hash_equals($this->getHashedOrder(), $calculated)) {
            return false;
        }

        return true;
    }
}
