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
 * Class OpenNode_Bitcoin_Block_Info
 */
class OpenNode_Bitcoin_Block_Info extends Mage_Payment_Block_Info
{
    /** @var Mage_Core_Model_Store */
    protected $_store;

    /** @var Mage_Sales_Model_Order */
    protected $_order;

    /**
     * Constructor. Set template.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('opennode/bitcoin/info.phtml');
    }

    /**
     * @return OpenNode_Bitcoin_Model_Charge
     * @throws Mage_Core_Exception
     */
    public function getCharge()
    {
        /** @var OpenNode_Bitcoin_Model_Bitcoin $method */
        $method = $this->getInfo()->getMethodInstance();
        return $method->getCharge();
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->_order) {
            /** @var Mage_Sales_Model_Order_Payment $payment */
            $payment = $this->getInfo();
            $this->_order = $payment->getOrder();
        }

        return $this->_order;
    }

    /**
     * @param $timestamp
     * @return Zend_Date
     */
    public function getStoreDate($timestamp)
    {
        if (!$this->_store) {
            $this->_store = $this->getOrder()->getStore();
        }

        return Mage::app()->getLocale()->storeDate($this->_store, $timestamp, true, 'full');
    }
}
