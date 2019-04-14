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

export default class QrCode {
  /**
   *
   * @param {Element} item
   */
  constructor(item) {
    this.item = item;
    this.update(item.dataset.address);
  }

  /**
   *
   * @param {string} address
   * @return {boolean}
   */
  shouldUpdate(address) {
    return address === null || this.address.toString() !== address.toString();
  }

  /**
   *
   * @param {string} address
   */
  update(address) {
    if (address === null) {
      address = '';
    }

    this.address = address.toString();
    this.qrcode = qrcodegen.QrCode.encodeText(this.address, qrcodegen.QrCode.Ecc.HIGH);
    this.item.innerHTML = this.getQrCodeAsSvg();
  }

  /**
   *
   * @return {string}
   */
  getQrCodeAsSvg() {
    return this.qrcode.toSvgString(4);
  }

  /**
   * Hides the parent element that contains the QRCode
   */
  hide() {
    this.item.style.display = 'none';
  }

  /**
   * Shows the parent element that contains the QRCode
   */
  show() {
    this.item.style.display = 'block';
  }
}
