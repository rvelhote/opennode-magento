# OpenNode Magento 1.9 Module
Magento 1.9 Plugin for OpenNode Bitcoin Payment Gateway

This module is still in development little by little

1. Configure API keys in the backoffice
2. Select the *Bitcoin* payment method during checkout
3. When placing the order you will be redirected to a page where customers are presented with 
a couple of QR Codes with the payment addresses (or links to pay with the wallet)
4. Customers can pay and then move to the default Magento success page. A task is continuously checking for the payment status in the background.
5. The callback will send the confirmation email once the *paid* status has been received
6. A cronjob will cancel *Pending Payment* orders automatically

The module was only tested with the default theme and Onepage Checkout.

# Missing

- Configure time-frames for automatic order cancellation
- Send a cancellation email when an order is canceled automatically via cronjob
- What to do if the user navigates away from the payment page
- Validate payment currency during checkout
- Unit Tests
- Create a companion module to add the BTC currency to Magento so that payments can be made in BTC
- Use an autoloader for the module because for sure most Magento installs don't use firegento/magento with composer
- Add the OpenNode PHP library to the lib dir
- Add modman files
- Create a build script to generate a release worthy package rather than the source code
- Include Docker related configurations and allow a full environment to the created with docker-compose
- Test various PHP versions