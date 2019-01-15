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
 * Class OpenNode_Bitcoin_Helper_Data
 */
class OpenNode_Bitcoin_Helper_Data extends Mage_Core_Helper_Abstract
{
    /** @var OpenNode_Bitcoin_Helper_Config */
    protected $_config;

    /**
     * OpenNode_Bitcoin_Helper_Data constructor.
     */
    public function __construct()
    {
        $this->_config = Mage::helper('opennode_bitcoin/config');
    }

    /**
     * @param int $sats
     * @return string
     */
    public function satoshiToBtc($sats)
    {
        if (function_exists('bcdiv')) {
            return bcdiv($sats, 100000000, 8);
        }

        return number_format($sats / 100000000, 8, '.', '');
    }

    /**
     * @param $tx
     * @return string
     */
    public function getTransactionExplorerUrl($tx)
    {
        if($this->_config->isTestMode()) {
            return sprintf('https://blockstream.info/testnet/tx/%s', $tx);
        }

        return sprintf('https://blockstream.info/tx/%s', $tx);
    }

    /**
     * @param $address
     * @return string
     */
    public function getAddressExplorerUrl($address)
    {
        if($this->_config->isTestMode()) {
            return sprintf('https://blockstream.info/testnet/address/%s', $address);
        }

        return sprintf('https://blockstream.info/address/%s', $address);
    }
}