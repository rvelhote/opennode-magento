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

/**
 * Handles clipboard copying (so the user can paste the addres in his own
 * wallet) as well as the protocol link (so the user can open his own wallet)
 */
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
      text: this.clipboardText,
    });
  }

  /**
   * Checks if the clipboard text or the link text should be updated in this
   * Wallet component
   * @param {String} ct The new clipboard text to compared to the existing
   * @param {String} lt The new link href to be compared to the existing
   * @return {Boolean} Indicate if an update should occur or not
   */
  shouldUpdate(ct, lt) {
    return ct === null
        || lt === null
        || ct.toString() !== this.clipboardText
        || lt.toString() !== this.linkText;
  }

  /**
   *
   * @param {String} ct
   * @param {String} lt
   */
  update(ct, lt) {
    this.clipboardText = ct;
    this.linkText = lt;
  }

  /**
   * Updates the clipboard text value
   * @param {String} value A new value to store in the clipboard text
   */
  set clipboardText(value) {
    this.clipboardElm.dataset.clipboardText =
        value === null ? '' : value.toString();
  }

  /**
   * Updates the href attribute of the link that opens the user's wallet
   * @param {String} value A new value to store in the link text
   */
  set linkText(value) {
    this.linkElm.href = value === null ? '' : value.toString();
  }

  /**
   * Get the current text that will be copied to the clipboard
   * @return {string} The text that should be copied to the clipboard
   */
  get clipboardText() {
    if (this.clipboardElm === null) {
      return '';
    }

    return this.clipboardElm.dataset.clipboardText.toString();
  }

  /**
   * Get the current href that will be used to open a system wallet as
   * specified in the href's protocol
   * @return {string}
   */
  get linkText() {
    if (this.linkElm === null) {
      return '';
    }

    return this.linkElm.href.toString();
  }

  /**
   * Hides the parent element that contains the clipboard and link features
   */
  hide() {
    this.element.style.display = 'none';
  }

  /**
   * Shows  the parent element that contains the clipboard and link features
   */
  show() {
    this.element.style.display = 'block';
  }
}
