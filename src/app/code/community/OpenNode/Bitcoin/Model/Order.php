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
     * Handles the four possible callback statuses:
     *
     * UNDERPAID: Transaction is seen but the charge is only partially paid. Waiting for the user to send the remainder.
     * REFUNDED: Transaction was underpaid but the user canceled it, asking for a refund.
     * PROCESSING: Transaction is seen for the first time on the mempool
     * PAID: Transaction is confirmed on the Bitcoin blockchain.
     *
     * @param OpenNode_Bitcoin_Model_Callback $callback The callback with all the parameters from the HTTP request
     * @return $this
     * @throws Exception Something went wrong with processing the REFUNDED or PAID statuses
     */
    public function handleCallback($callback)
    {
        $format = '[%s] Handling callback status %s';
        $this->logger->info(sprintf($format, $callback->getIncrementId(), $callback->getStatus()));

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

        $statuses = [
            OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_REFUNDED,
            OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_PAID,
            OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_UNDERPAID,
            OpenNode_Bitcoin_Model_Bitcoin::OPENNODE_STATUS_PROCESSING,
        ];

        if (!in_array($callback->getStatus(), $statuses)) {
            $format = '[%s] Callback contains an invalid status %s';
            $this->logger->error(sprintf($format, $callback->getIncrementId(), $callback->getStatus()));
        }

        return $this;
    }

    /**
     * Handles the PAID callback status. This handler will create a Magento Invoice and update the order's state/status
     * to PROCESSING from the PROCESSING_PAYMENT state/status
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
     * Handles the UNDERPAID callback status. This handler will only add a message for each time it receives such
     * status. It does not change the order's status which will remain PENDING_PAYMENT
     * @param string $sats The missing amount in SATS
     * @param string $btc The missing amount in BTC
     * @return $this
     */
    public function handleUnderpaid($sats, $btc)
    {
        $this->addStatusHistoryComment(sprintf('Current PAYMENT status: UNDERPAID by %s ~ %s', $sats, $btc));
        $this->logger->info(sprintf('[%s] UNDERPAID by %s SATS callback received', $sats, $this->getIncrementId()));
        return $this;
    }

    /**
     * Handles the PROCESSING callback status. This handler does not update the order's status and only adds a history
     * message for each request it received with this status code.
     * @return $this
     */
    public function handleProcessing()
    {
        $this->addStatusHistoryComment(sprintf('Current PAYMENT status: PROCESSING'));
        $this->logger->info(sprintf('[%s] PROCESSING callback received', $this->getIncrementId()));
        return $this;
    }

    /**
     * Handles the REFUNDED status callback. This handler will create a Creditmemo automatically for the entire order
     * regardless of the amount refunded. By creating a Creditmemo for the entire order Magento will set the order
     * status/state to CLOSED.
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

        $this->logger->info(sprintf('[%s] REFUNDED Creditmemo issued', $this->getIncrementId()));
        return $this;
    }
}
