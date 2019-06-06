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
 * Class OpenNode_Bitcoin_Helper_Config
 */
class OpenNode_Bitcoin_Helper_Config extends Mage_Core_Helper_Abstract
{
    /** @var string */
    const XML_PATH_OPENNODE_ACTIVE = 'payment/opennode_bitcoin/active';

    /** @var string */
    const XML_PATH_OPENNODE_TEST_MODE = 'payment/opennode_bitcoin/test_mode';

    /** @var string */
    const XML_PATH_OPENNODE_PRODUCTION_KEY = 'payment/opennode_bitcoin/production_api_key';

    /** @var string */
    const XML_PATH_OPENNODE_DEVELOPMENT_KEY = 'payment/opennode_bitcoin/development_api_key';

    /** @var string */
    const XML_PATH_OPENNODE_AUTO_SETTLE = 'payment/opennode_bitcoin/auto_settle';

    /** @var string */
    const XML_PATH_OPENNODE_CANCELLATION_TIMEFRAME = 'payment/opennode_bitcoin/cancelation_timeframe';

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_OPENNODE_ACTIVE);
    }

    /**
     * @return bool
     */
    public function isTestMode()
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_OPENNODE_TEST_MODE);
    }

    /**
     * @return bool
     */
    public function isProductionMode()
    {
        return !$this->isTestMode();
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->isProductionMode() ? 'live' : 'dev';
    }

    /**
     * @return string
     */
    public function getProductionApiKey()
    {
        /** @var Mage_Core_Helper_Data $core */
        $core = Mage::helper('core');
        return $core->decrypt(Mage::getStoreConfig(self::XML_PATH_OPENNODE_PRODUCTION_KEY));
    }

    /**
     * @return string
     */
    public function getDevelopmentApiKey()
    {
        /** @var Mage_Core_Helper_Data $core */
        $core = Mage::helper('core');
        return $core->decrypt(Mage::getStoreConfig(self::XML_PATH_OPENNODE_DEVELOPMENT_KEY));
    }

    /**
     * @return string
     */
    public function getAuthToken()
    {
        return $this->isProductionMode() ? $this->getProductionApiKey() : $this->getDevelopmentApiKey();
    }

    /**
     * @return bool
     */
    public function isAutoSettle()
    {
        return (string)Mage::getStoreConfig(self::XML_PATH_OPENNODE_AUTO_SETTLE);
    }

    /**
     * @return int
     */
    public function getCancelationTimeframe()
    {
        $timeframe = intval(Mage::getStoreConfig(self::XML_PATH_OPENNODE_CANCELLATION_TIMEFRAME));

        if ($timeframe <= 0) {
            $timeframe = 1;
        }

        return $timeframe;
    }

    /**
     * @return int
     */
    public function getCancelationTimeframeInSeconds()
    {
        return $this->getCancelationTimeframe() * 3600;
    }
}