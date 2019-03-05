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

import Payment from './Payment';
import QrCode from './QrCode';
import Status from './Status';
import Timer from "./Timer";

document.observe("dom:loaded", function () {
    const statusUrl = document.getElementById('__opennodestatusurl__').value;
    const qrcodes = {
        lightning: null,
        onchain: null,
    };

    const paymentMethods = document.querySelectorAll('[data-payment-method]');
    paymentMethods.forEach(method => {
        const qrcode = method.querySelector('[data-qrcode]');
        const clipboard = method.querySelector(['data-clipboard']);
        const wallet = method.querySelector('[data-wallet]');

        qrcodes[method.dataset.paymentMethod] = new QrCode(qrcode, clipboard, wallet);
    });

    const status = new Status(statusUrl);
    const timer = new Timer();

    new Payment(status, timer, qrcodes);

    const clipboard = new ClipboardJS('button[data-clipboard-text]');
    clipboard.on('error', () => {
        alert('Error copying to the clipboard!');
    });
});