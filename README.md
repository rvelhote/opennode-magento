# OpenNode Magento 1.9 Module
Magento 1.9 Plugin for OpenNode Bitcoin Payment Gateway

This module is still in development little by little

1. Configure API keys in the backoffice
2. Select the *Bitcoin* payment method during checkout
3. When placing the order you will be redirected to a page where customers are presented with 
a couple of QR Codes with the payment addresses (or links to pay with the wallet)
4. Customers can pay and then move to the default Magento success page. A task is continuously checking for the payment 
status in the background and informs the user of the progress
5. A cronjob will cancel *Pending Payment* orders automatically

The module was only tested with the default theme and Onepage Checkout.

# Requirements

- Magento 1.9 (and all the basic requirements that go with it)
- Only PHP 7.0+ is supported

# Check it Out (For TEST environments only)

1. Run `composer install`
2. Run `npm install` and then `npm run watch`
3. Run `bash shell/docker.bash` to setup some custom docker containers for development
4. Run `docker-compose up` to setup an environment
5. You should add the following lines to your hosts file `127.0.0.1 development.opennode.co db mailhog`
6. [Download](https://anonymousfiles.io/f/magento-sample-data.zip) the Magento Sample Data. You don't have to use the 
sample data of course but that means you wil have to create products and categories yourself
7. Extract the sample data and copy the resulting folder to the *data* folder 
8. Run `bash shell/install.bash` from the root directory of the project. This will setup a default Magento store with 
actual products and categories. All products will be discounted by 99% to make sure you don't spend all your Testnet 
coins. It will also ask you to input your development key which you should get from your account at OpenNode

You can modify the key or check out additional configuration settings in: 
`System » Configuration » Payment Methods » OpenNode Bitcoin`

# Sample Environment

Backend: `http://development.opennode.co/admin/`
User: `admin`
Pass: `password123`

---------------------------------------

Frontend: `http://development.opennode.co/`
User: `janedoe@example.com`
Pass: `password123`

# Cronjob

To test the Crojob that handles the cancellation of pending order run the following command from the project root:
`./bin/n98-magerun.phar --root-dir=src - sys:cron:run opennode_bitcoin`

# Missing

- Add translation files
- What to do if the user navigates away from the payment
- Create a companion module to add the BTC currency to Magento so that payments can be made in BTC
- Better Unit Test coverage (PHP and Javascript)
- Convert the Javascript part to Typescript