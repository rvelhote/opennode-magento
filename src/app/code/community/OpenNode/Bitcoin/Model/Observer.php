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
 * Class OpenNode_Bitcoin_Model_Observer
 */
class OpenNode_Bitcoin_Model_Observer extends Mage_Core_Helper_Abstract
{
    /**
     * Warn the user if there is some basic misconfiguration related to API Keys and Environment
     * @param Varien_Event_Observer $observer
     */
    public function systemConfigSectionChangeAfter($observer)
    {
        /** @var Varien_Event $event */
        $event = $observer->getData('event');
        if ($event->getData('name') !== 'admin_system_config_changed_section_payment') {
            return;
        }

        /** @var Mage_Admin_Model_Session $session */
        $session = Mage::getSingleton('adminhtml/session');

        /** @var OpenNode_Bitcoin_Helper_Config $config */
        $config = Mage::helper('opennode_bitcoin/config');

        /** @var OpenNode_Bitcoin_Helper_Data $helper */
        $helper = Mage::helper('opennode_bitcoin');

        if ($config->isProductionMode() && !$config->getProductionApiKey()) {
            $session->addError($helper->__('PRODUCTION mode is ON but an API Key is missing! Watch out!'));
        }

        if ($config->isTestMode() && !$config->getDevelopmentApiKey()) {
            $session->addError($helper->__('DEVELOPMENT mode is ON but an API Key is missing! Watch out!'));
        }

        if ($config->getProductionApiKey() == $config->getDevelopmentApiKey()) {
            $session->addError($helper->__('DEVELOPMENT and PRODUCTION API Keys are the same! Watch out!'));
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @throws Exception
     */
    public function checkoutSaveOrderAfter($observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getData('order');

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getData('quote');

        if (!$order || !$quote) {
            return;
        }

        $txnId = $order->getPayment()->getAdditionalInformation(OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_TXN_ID_KEY);
        $env = $order->getPayment()->getAdditionalInformation(OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_PARAMS_ENV);

        $quote->getPayment()->setAdditionalInformation(OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_TXN_ID_KEY, $txnId);
        $quote->getPayment()->setAdditionalInformation(OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_PARAMS_ENV, $env);
    }
}