/**
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
"use strict";

export default class Payment {
    /**
     *
     * @param status
     * @param timer
     */
    constructor(status, timer) {
        this.status = status;
        this.timer = timer;

        this.status.setOnUnpaid(this.onUnpaid.bind(this));
        this.status.setOnPaid(this.onPaid.bind(this));
        this.status.setOnProcessing(this.onProcessing.bind(this));
        this.status.setOnRequestComplete(this.onComplete.bind(this));

        this.elements = {
            unpaid: document.getElementById('payment-status-unpaid'),
            processing: document.getElementById('payment-status-processing'),
            paid: document.getElementById('payment-status-paid'),
            timer: document.getElementById('lightning-timer'),
        }
    }

    onComplete(response) {
        this.elements.timer.innerHTML = this.timer.getTimeRemaining(response.lightning.expires_at);
    }

    /**
     *
     */
    onUnpaid() {
        this.elements.unpaid.style.display = 'block';
        this.elements.processing.style.display = 'none';
        this.elements.paid.style.display = 'none';
    }

    /**
     *
     */
    onProcessing() {
        this.elements.unpaid.style.display = 'none';
        this.elements.processing.style.display = 'block';
        this.elements.paid.style.display = 'none';
    }

    /**
     *
     */
    onPaid() {
        this.elements.unpaid.style.display = 'none';
        this.elements.processing.style.display = 'none';
        this.elements.paid.style.display = 'block';
    }
}