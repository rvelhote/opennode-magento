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
require_once Mage::getBaseDir('lib') . DS . 'opennode' . DS . 'init.php';

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
     * @param $tx
     * @return string
     */
    public function getTransactionExplorerUrl($tx)
    {
        if ($this->_config->isTestMode()) {
            return sprintf('https://blockstream.info/testnet/tx/%s', $tx);
        }

        return sprintf('https://blockstream.info/tx/%s', $tx);
    }

    /**
     * @throws Zend_Cache_Exception
     * @throws Zend_Http_Client_Exception
     */
    public function getAcceptedCurrencies()
    {
        /** @var Mage_Core_Helper_Data $core */
        $core = Mage::helper('core');
        $cache = Mage::app()->getCache();

        $currencies = $cache->load('opennode_bitcoin_currencies');
        if (!$currencies) {
            $http = new Varien_Http_Client('https://api.opennode.co/v1/currencies');

            $response = $http->request(Varien_Http_Client::GET)->getBody();
            $response = $core->jsonDecode($response);

            $currencies = $core->jsonEncode($response['data']);
            $cache->save($currencies, 'opennode_bitcoin_currencies', ['OPENNODE']);
        }

        return $core->jsonDecode($currencies);
    }

    /**
     * @param $address
     * @return string
     */
    public function getAddressExplorerUrl($address)
    {
        if ($this->_config->isTestMode()) {
            return sprintf('https://blockstream.info/testnet/address/%s', $address);
        }

        return sprintf('https://blockstream.info/address/%s', $address);
    }
}