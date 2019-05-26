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
 * Handles all Time calculation tasks
 */
export default class Timer {
  /**
   *
   * @return {Number}
   */
  static now() {
    const date = new Date();
    return Math.round(date.getTime() / 1000);
  }

  /**
   * Calculate the time remaining in the Hours:Minutes:Seconds format from a
   * timestamp value
   * @param {Number} timestamp The timestamp to convert to the H:M:S format
   * @return {String} A timestamp converted into the H:M:S format
   */
  static getTimeRemaining(timestamp) {
    const timeRemaining = timestamp - Timer.now();

    const hours = Math.round(timeRemaining / 60).toFixed(0);
    const minutes = Math.round(timeRemaining % 60).toFixed(0);

    return `${hours}:${minutes}`;
  }
}
