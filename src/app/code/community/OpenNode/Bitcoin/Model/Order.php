<?php

/**
 * Class OpenNode_Bitcoin_Model_Sales_Order
 */
class OpenNode_Bitcoin_Model_Order extends Mage_Sales_Model_Order
{
    /** @var OpenNode_Bitcoin_Helper_Logger */
    protected $logger;

    /**
     * OpenNode_Bitcoin_Model_Order constructor.
     * @param array $args
     */
    public function __construct($args)
    {
        parent::__construct();
        $this->logger = $args['logger'] ?? Mage::helper('opennode_bitcoin/logger');
    }

    /**
     * @param OpenNode_Bitcoin_Model_Callback $callback
     * @return $this
     * @throws Exception
     */
    public function handleCallback($callback)
    {
        $this->logger->info(sprintf('[%s] Handling callback for order', $this->getIncrementId()));

        if ($callback->getStatus() == OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_REFUNDED) {
            $this->handleRefund();
        }

        if ($callback->getStatus() == OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_PAID) {
            $this->handlePaid();
        }

        if ($callback->getStatus() == OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_UNDERPAID) {
            $this->handleUnderpaid($callback->getMissingAmt(), $callback->getMissingAmtBtc());
        }

        if ($callback->getStatus() == OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_PROCESSING) {
            $this->handleProcessing();
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function handlePaid()
    {
        if (!$this->canInvoice()) {
            $this->addStatusHistoryComment('Cannot issue an invoice for this order. Probably the order is already ' .
                'paid by some other means or this callback is duplicated');
            $this->logger->error(sprintf('[%s] PAID CALLBACK but cannot issue a Creditmemo', $this->getIncrementId()));
            return $this;
        }

        $this->getPayment()->capture(null);
        $this->logger->info(sprintf('[%s] payment CAPTURED and invoice issued', $this->getIncrementId()));

        if (!$this->getEmailSent()) {
            $this->queueNewOrderEmail()->setEmailSent(true);
            $this->logger->info(sprintf('[%s] email UNSENT so it was QUEUED for sending', $this->getIncrementId()));
        }

        return $this;
    }

    /**
     * @param string $sats
     * @param string $btc
     * @return $this
     */
    public function handleUnderpaid($sats, $btc)
    {
        $this->addStatusHistoryComment(sprintf('Current PAYMENT status: UNDERPAID by %s ~ %s', $sats, $btc));
        return $this;
    }

    /**
     * @return $this
     */
    public function handleProcessing()
    {
        $this->addStatusHistoryComment(sprintf('Current PAYMENT status: PROCESSING'));
        $this->logger->info(sprintf('[%s] PROCESSING callback received', $this->getIncrementId()));
        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function handleRefund()
    {
        if (!$this->canCreditmemo()) {
            $this->logger->info(sprintf('[%s] REFUNDED but cannot issue a Creditmemo', $this->getIncrementId()));
            return $this;
        }

        $service = Mage::getModel('sales/service_order', $this);

        // TODO Perhaps create a transaction like core Magento does in the backoffice
        $creditmemo = $service->prepareCreditmemo();
        $creditmemo->register()->save();

        return $this;
    }
}
