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
 * Class OpenNode_Bitcoin_Helper_Logger
 */
class OpenNode_Bitcoin_Helper_Logger extends Mage_Core_Helper_Abstract
{
    /** @var string The filename that the log messages will be written to */
    const FILENAME = 'opennodebitcoin.log';

    /**
     * @param string $message
     * @param int $level
     */
    public function log($message, $level = null)
    {
        Mage::log($message, $level, self::FILENAME);
    }

    /**
     * @param string $message
     */
    public function error($message)
    {
        $this->log($message, Zend_Log::ERR);
    }

    /**
     * @param string $message
     */
    public function warn($message)
    {
        $this->log($message, Zend_Log::WARN);
    }

    /**
     * @param string $message
     */
    public function info($message)
    {
        $this->log($message, Zend_Log::INFO);
    }
}