# OpenNode Magento 1.9 Module
Magento 1.9 Plugin for OpenNode Bitcoin Payment Gateway

This module is still in development. A basic version of the module is now working which will 
allow you to:

1. Configure API keys in the backoffice
2. Select the *Bitcoin* payment method during checkout
3. When placing the order you will be redirected to a page where customers are presented with 
a couple of QR Codes with the payment addresses (or links to pay with the wallet)
4. Customers can pay and then move to the default Magento success page. A task is continuously checking for the payment status in the background.
5. The callback will send the confirmation email once the *paid* status has been received
6. A cronjob will cancel *Pending Payment* orders automatically

The module was only tested with the default theme and Onepage Checkout.

# Missing

- Testing the callbacks (need to install a Ligthning node and get some testnet coins)
- Handling refunds
- Handling expired Lightning Network payment requests
- Configure time-frames for automatic order cancellation
- Send a cancellation email when an order is canceled automatically via cronjob
- What to do if the user navigates away from the payment page
- Validate payment currency during checkout
- Unit Tests
- Testing various scenarios and make sure all possibilities are covered
- More stuff that I forgot to include

# Screenshots

Here are some screenshots of the main sections for this payment method.

## Checkout
After the customer confirms the order he will be redirected to the payment page.

![https://i.imgur.com/fziVnDw.png](https://i.imgur.com/fziVnDw.png)

## Payment Page
I have some concerns about this page. Should we let the user navigate away to the success page without 
payment confirmation? Even though there is a confirmation message some people might be confused and think 
they have paid (perhaps add an email with payment details?). The asynchronous nature of the payment method 
allows for the user to pay whenever he wants (at least on-chain). Timeouts must be specified in the backoffice 
and the interface must make them clear.  

### Waiting for Payment
![https://i.imgur.com/Z1UOHQo.png](https://i.imgur.com/Z1UOHQo.png)

### Payment Received
![https://i.imgur.com/p95vHdI.png](https://i.imgur.com/p95vHdI.png)

## Backoffice
![https://i.imgur.com/bpDVij2.png](https://i.imgur.com/bpDVij2.png)