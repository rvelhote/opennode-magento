<?php

/**
 * Class OpenNode_Bitcoin_Model_Sales_Order
 * @method
 */
class OpenNode_Bitcoin_Model_Sales_Order extends Mage_Sales_Model_Order
{
    /** @var OpenNode_Bitcoin_Helper_Config */
    protected $config;

    /**
     * OpenNode_Bitcoin_Model_Sales_Order constructor.
     * @param $args
     */
    public function __construct($args)
    {
        $this->config = isset($args['config']) ? $args['config'] : Mage::helper('opennode_bitcoin/config');
    }

    /**
     * @param OpenNode_Bitcoin_Model_Callback $callback
     * @return string
     */
    public function verifyCallback($callback)
    {
        $id = $this->getPayment()->getAdditionalInformation(OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_TXN_ID_KEY);
        if ($callback->getId() != $id) {
            throw new Exception('Mismatch callback transaction key with the transaction key from the Magento order', 1);
        }

        if ($callback->getStatus() !== OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_PAID) {
            throw new Exception('Callback does not have the PAID status yet', 2);
        }

        $calculated = hash_hmac('sha256', $callback->getId(), $this->config->getAuthToken());
        if (!hash_equals($callback->getHashedOrder(), $calculated)) {
            throw new Exception('Callback HMAC mismatch', 3);
        }

        return $this;
    }
}