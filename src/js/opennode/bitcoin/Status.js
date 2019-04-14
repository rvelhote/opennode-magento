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
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
'use strict';

const PROCESSING = 'processing';
const PAID = 'paid';
const UNPAID = 'unpaid';
const INTERVAL = 1000;

export default class Status {
  /**
   *
   * @param endpoint
   */
  constructor(endpoint) {
    this.lock = false;
    this.endpoint = endpoint;
    this.observers = [];

    this.elements = {
      unpaid: document.getElementById('payment-status-unpaid'),
      processing: document.getElementById('payment-status-processing'),
      paid: document.getElementById('payment-status-paid'),
    };

    setInterval(this.onInterval.bind(this), INTERVAL);
  }

  /**
   *
   * @param observer
   * @return {Status}
   */
  registerObserver(observer) {
    this.observers.push(observer);
    return this;
  }

  /**
   *
   */
  onInterval() {
    if (this.lock) {
      return;
    }

    this.lock = true;

    new Ajax.Request(this.endpoint, {
      onSuccess: this.onSuccess.bind(this),
      onFailure: this.onFailure.bind(this),
      onComplete: this.onComplete.bind(this),
      onException: this.onException.bind(this),
    });
  }

  /**
   *
   * @param response
   */
  onSuccess(response) {
    const r = response.responseJSON;

    this.observers.forEach((o) => {
      o(r);
    });

    switch (r.status) {
      case PROCESSING: {
        this.elements.unpaid.style.display = 'none';
        this.elements.processing.style.display = 'block';
        this.elements.paid.style.display = 'none';
        break;
      }

      case PAID: {
        this.elements.unpaid.style.display = 'none';
        this.elements.processing.style.display = 'none';
        this.elements.paid.style.display = 'block';
        break;
      }

      case UNPAID:
      default: {
        this.elements.unpaid.style.display = 'block';
        this.elements.processing.style.display = 'none';
        this.elements.paid.style.display = 'none';
      }
    }
  }

  /**
   *
   * @param response
   */
  onFailure(response) {
    console.log(response);
  }

  /**
   *
   * @param r
   * @param e
   */
  onException(r, e) {
    throw e;
  }

  /**
   *
   */
  onComplete() {
    this.lock = false;
  }
}
