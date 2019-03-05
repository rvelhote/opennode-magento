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

const PROCESSING = 'processing';
const PAID = 'paid';
const UNPAID = 'unpaid';

export default class Status {
    /**
     *
     * @returns {number}
     */
    constructor(endpoint) {
        this.lock = false;
        this.endpoint = endpoint;

        this.onPaid = () => {
        };
        this.onProcessing = () => {
        };
        this.onUnpaid = () => {
        };

        setInterval(this.onInterval.bind(this), 1000);
    }

    onInterval() {
        if (this.lock) {
            return;
        }

        this.lock = true;

        new Ajax.Request(this.endpoint, {
            onSuccess: this.onSuccess.bind(this),
            onFailure: this.onFailure.bind(this),
            onComplete: this.onComplete.bind(this),
        });
    }

    /**
     *
     * @param response
     */
    onSuccess(response) {
        switch (response.responseJSON.status) {
            case PROCESSING: {
                this.onProcessing();
                break;
            }

            case PAID: {
                this.onPaid();
                break;
            }

            default:
            case UNPAID: {
                this.onUnpaid();
                break;
            }
        }
    }

    /**
     *
     * @param response
     */
    onFailure(response) {
    }

    /**
     *
     */
    onComplete() {
        this.lock = false;
    }

    /**
     *
     * @param fn
     */
    setOnUnpaid(fn) {
        this.onUnpaid = fn;
    }

    /**
     *
     * @param fn
     */
    setOnPaid(fn) {
        this.onPaid = fn;
    }

    /**
     *
     * @param fn
     */
    setOnProcessing(fn) {
        this.onProcessing = fn;
    }
}