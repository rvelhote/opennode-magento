#!/usr/bin/env bash
# Install Magento + Sample Data

USER="root"
PASS="control"
HOST="db"
DATABASE="magentodb"
URL="http://development.opennode.co"

# Install n98-magerun
php -r "copy('https://files.magerun.net/n98-magerun.phar', 'bin/n98-magerun.phar');"
chmod +x bin/n98-magerun.phar
echo "INSTALLED n98-magerun to the BIN directory"

rm -f src/app/etc/local.xml

mysql -u$USER -p$PASS -h$HOST -e "drop database ${DATABASE};"
mysql -u$USER -p$PASS -h$HOST -e "create database ${DATABASE};"

mysql -u$USER -p$PASS -h$HOST $DATABASE < data/magento-sample-data/magento_sample_data_for_1.9.2.4.sql
echo "IMPORTED the SAMPLE DATABASE"

cp -R data/magento-sample-data/media src
cp -R data/magento-sample-data/skin src
echo "COPIED all SAMPLE folders for the Magento Demo Store"

bin/n98-magerun.phar install --dbHost=$HOST --dbUser=$USER --dbPass=$PASS --forceUseDb --dbName=$DATABASE --useDefaultConfigParams=yes --noDownload --installationFolder="src" --baseUrl=$URL
echo "Installed the SAMPLE DATABASE"

php src/shell/opennode.php
echo "Installed BITCOIN discount rule so that prices can be lower all over the website"

echo "Backend: ${URL}/admin"
echo "User: admin"
echo "Pass: password123"
echo "Add your OpenNode Development Key in System » Configuration » Payment Methods » OpenNode Bitcoin"