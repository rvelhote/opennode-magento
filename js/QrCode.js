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
     * @returns {boolean}
     */
    shouldUpdate(address) {
        return this.address !== address
    }

    /**
     *
     * @param {string} address
     */
    update(address) {
        this.address = address;
        this.qrcode = qrcodegen.QrCode.encodeText(address, qrcodegen.QrCode.Ecc.HIGH);
        this.item.innerHTML = this.getQrCodeAsSvg();
    }

    /**
     *
     * @returns {string}
     */
    getQrCodeAsSvg() {
        return this.qrcode.toSvgString(4);
    }
}