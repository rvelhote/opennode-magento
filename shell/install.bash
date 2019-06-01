#!/usr/bin/env bash
# Install Magento + Sample Data

USER="root"
PASS="control"
HOST="db"
DATABASE="magentodb"
URL="http://development.opennode.co"

rm -f src/app/etc/local.xml

mysql -u$USER -p$PASS -h$HOST -e "drop database ${DATABASE}; create database ${DATABASE}"
mysql -u$USER -p$PASS -h$HOST $DATABASE < data/magento-sample-data/magento_sample_data_for_1.9.2.4.sql

cp -R data/magento-sample-data/media src
cp -R data/magento-sample-data/skin src

mr install --dbHost=$HOST --dbUser=$USER --dbPass=$PASS --forceUseDb --dbName=$DATABASE --useDefaultConfigParams=yes --noDownload --installationFolder="src" --baseUrl=$URL