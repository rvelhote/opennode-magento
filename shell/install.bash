#!/usr/bin/env bash
# Install Magento + Sample Data

USER="root"
PASS="control"
HOST="db"
DATABASE="magentodb"
URL="http://development.opennode.co"

# Install n98-magerun
echo "Downloading n98-magerun"
php -r "copy('https://files.magerun.net/n98-magerun.phar', 'bin/n98-magerun.phar');"
chmod +x bin/n98-magerun.phar
echo "Installed n98-magerun to the BIN directory"

rm -f src/app/etc/local.xml

echo "Dropping any existing databases named ${DATABASE}"
mysql -u$USER -p$PASS -h$HOST -e "drop database ${DATABASE};"

echo "Create a new database ${DATABASE}"
mysql -u$USER -p$PASS -h$HOST -e "create database ${DATABASE};"

echo "Importing the Magento 1.9 sample database"
mysql -u$USER -p$PASS -h$HOST $DATABASE < data/magento-sample-data/magento_sample_data_for_1.9.2.4.sql
echo "Imported the the sample database"

echo "Copy all sample folders"
cp -R data/magento-sample-data/media src
cp -R data/magento-sample-data/skin src
echo "Finished copying all sample folders for the Magento Demo Store"

echo "Installing Magento 1.9.4.1"
bin/n98-magerun.phar install --dbHost=$HOST --dbUser=$USER --dbPass=$PASS --forceUseDb --dbName=$DATABASE --useDefaultConfigParams=yes --noDownload --installationFolder="src" --baseUrl=$URL
echo "Installed Magento 1.9.4.1"

# Discount everything
php src/shell/opennode.php
echo "Installed a discount rule so that prices can be lower all over the website"

./bin/n98-magerun.phar --root-dir=src customer:change-password janedoe@example.com password123
./bin/n98-magerun.phar --root-dir=src config:set carriers/flatrate/active 0
./bin/n98-magerun.phar --root-dir=src config:set carriers/freeshipping/free_shipping_subtotal 0

./bin/n98-magerun.phar --root-dir=src cache:clean

echo "All Done!"