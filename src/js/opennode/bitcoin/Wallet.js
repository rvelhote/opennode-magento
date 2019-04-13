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

export default class Wallet {
    /**
     *
     * @param {Node} element
     */
    constructor(element) {
        this.element = element;

        this.clipboardElm = this.element.querySelector('[data-clipboard-text]');
        this.linkElm = this.element.querySelector('[data-wallet-link]');

        this.clipboard = new ClipboardJS(this.clipboardElm, {
            text: trigger => {
                return trigger.dataset.clipboardText;
            }
        });
    }

    /**
     *
     * @param ct
     * @param lt
     */
    shouldUpdate(ct, lt) {
        const currentClipboardText = this.clipboardElm.dataset.clipboardText.toString();
        const currentLinkText = this.linkElm.href.toString();

        // console.log(currentClipboardText !== ct.toString());
        // console.log(currentLinkText !== lt.toString());




        return ct.toString() !== currentClipboardText || lt.toString() !== currentLinkText;
    }

    /**
     *
     * @param {String} ct
     * @param {String} lt
     * @returns {Wallet}
     */
    update(ct, lt) {
        this.clipboardElm.dataset.clipboardText = ct;
        this.linkElm.href = lt;

       // console.log(ct);
       // console.log(lt);

        return this;
    }
}