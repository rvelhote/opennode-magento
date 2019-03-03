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
"use strict";

document.observe("dom:loaded", function () {
    const statusUrl = document.getElementById('__opennodestatusurl__').value;

    $$('div[data-address]').forEach(item => {
        if (!item.dataset.address || item.dataset.address.length === 0) {
            // TODO Translate me!!
            item.innerHTML = 'Something wrong';
            return;
        }

        const qrc = qrcodegen.QrCode.encodeText(item.dataset.address, qrcodegen.QrCode.Ecc.HIGH);
        item.innerHTML = qrc.toSvgString(4);
    });

    var lock = false;

    setInterval(function () {
        if (lock) {
            return;
        }

        lock = true;

        new Ajax.Request(statusUrl, {
            onSuccess: function (response) {
                if (response.responseJSON.status === 'processing') {
                    $('payment-status-processing').style.display = 'block';
                    $('payment-status-unpaid').style.display = 'none';
                    $('payment-status-paid').style.display = 'none';
                }

                if (response.responseJSON.status === 'paid') {
                    $('payment-status-processing').style.display = 'none';
                    $('payment-status-unpaid').style.display = 'none';
                    $('payment-status-paid').style.display = 'block';
                }
            },
            onFailure: function (response) {
                console.log(response);
            },
            onComplete: function () {
                lock = false;
            }
        });
    }, 1000);

    new ClipboardJS('button[data-clipboard-text]');
});