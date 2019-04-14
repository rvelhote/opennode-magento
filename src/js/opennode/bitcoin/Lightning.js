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

import QrCode from './QrCode';
import Wallet from './Wallet';

/**
 * Handle the Lightning Network Payment Method
 */
export default class Lightning {
  /**
   *
   * @param {Element} element
   * @param {Status} status
   */
  constructor(element, status) {
    const qre = element.querySelector('[data-qrcode]');
    const we = element.querySelector('[data-wallet]');

    if (qre === null || we === null) {
      return;
    }

    this.qrcode = new QrCode(qre);
    this.wallet = new Wallet(we);

    status.registerObserver(this.observe.bind(this));
  }

  /**
   * Handle the response returned by the server for a Lightning Network payment
   * @param {Object} r
   */
  observe(r) {
    if (!r.status.unpaid || r.lightning.address === null) {
      this.qrcode.hide();
      this.wallet.hide();
      return;
    }

    this.qrcode.show();
    this.wallet.show();

    if (this.wallet.shouldUpdate(r.lightning.address, r.lightning.uri)) {
      this.wallet.update(r.lightning.address, r.lightning.uri);
    }

    if (this.qrcode.shouldUpdate(r.lightning.uri)) {
      this.qrcode.update(r.lightning.uri);
    }
  }
}
