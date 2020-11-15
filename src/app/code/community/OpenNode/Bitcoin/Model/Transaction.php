<?php

/**
 * Class OpenNode_Bitcoin_Model_Transaction
 * @method string getAddress()
 * @method int getCreatedAt()
 * @method int getSettledAt()
 * @method string getTx()
 * @method string getStatus()
 * @method int getAmount
 */
class OpenNode_Bitcoin_Model_Transaction extends Varien_Object
{
    /**
     * Converts the transaction amount from SATS to BTC
     * @return string The transaction amount converted from SATS to BTC
     */
    public function getAmountBtc()
    {
        if (function_exists('bcdiv')) {
            return bcdiv($this->getAmount(), 100000000, 8);
        }

        return number_format($this->getAmount() / 100000000, 8, '.', '');
    }
}
