#!/usr/bin/env bash
# Install Magento + Sample Data
RED="\033[0;31m"
YELLOW="\033[0;33m"
GREEN="\033[0;32m"
NONE="\033[0m"

USER="root"
PASS="control"
HOST="db"
DATABASE="magentodb"
URL="http://development.opennode.co"

echo -e "${RED}"
cat <<EOF
 ______  __________________ _______  _______ _________ _
(  ___ \ \__   __/\__   __/(  ____ \(  ___  )\__   __/( (    /|
| (   ) )   ) (      ) (   | (    \/| (   ) |   ) (   |  \  ( |
| (__/ /    | |      | |   | |      | |   | |   | |   |   \ | |
|  __ (     | |      | |   | |      | |   | |   | |   | (\ \) |
| (  \ \    | |      | |   | |      | |   | |   | |   | | \   |
| )___) )___) (___   | |   | (____/\| (___) |___) (___| )  \  |
|/ \___/ \_______/   )_(   (_______/(_______)\_______/|/    )_)
EOF

echo -e "${NONE}"

echo -e "${YELLOW}Bulding Nginx...${NONE}"
docker build -t magento-nginx docker/nginx/ > /dev/null

echo -e "${YELLOW}Building PHP...${NONE}"
docker build -t magento-php docker/php/ > /dev/null

echo -e "${YELLOW}Downloading n98-magerun...${NONE}"
php -r "copy('https://files.magerun.net/n98-magerun.phar', 'bin/n98-magerun.phar');"
chmod +x bin/n98-magerun.phar

echo -e "${YELLOW}Removing any existing configuration files...${NONE}"
rm -f src/app/etc/local.xml > /dev/null

echo -e "${YELLOW}Dropping any existing databases...${NONE}"
mysql -u$USER -p$PASS -h$HOST -e "drop database ${DATABASE};" > /dev/null

echo -e "${YELLOW}Creating a new database...${NONE}"
mysql -u$USER -p$PASS -h$HOST -e "create database ${DATABASE};" > /dev/null

echo -e "${YELLOW}Importing the Magento 1.9 sample database...${NONE}"
mysql -u$USER -p$PASS -h$HOST $DATABASE < data/magento-sample-data/magento_sample_data_for_1.9.2.4.sql

echo -e "${YELLOW}Copy all sample MEDIA and SKIN folders...${NONE}"
cp -R data/magento-sample-data/media src
cp -R data/magento-sample-data/skin src

echo -e "${YELLOW}Installing Magento...${NONE}"
bin/n98-magerun.phar install \
                        --dbHost=$HOST \
                        --dbUser=$USER \
                        --dbPass=$PASS \
                        --forceUseDb \
                        --dbName=$DATABASE \
                        --useDefaultConfigParams=yes \
                        --noDownload \
                        --installationFolder="src" \
                        --baseUrl=$URL > /dev/null

echo -e "${YELLOW}Delete the var folder to clear any stale caches...${NONE}"
rm -rf src/var/*

bin/n98-magerun.phar --root-dir=src customer:change-password janedoe@example.com password123 > /dev/null
bin/n98-magerun.phar --root-dir=src config:set carriers/flatrate/active 0 > /dev/null
bin/n98-magerun.phar --root-dir=src config:set carriers/freeshipping/free_shipping_subtotal 0 > /dev/null
bin/n98-magerun.phar --root-dir=src opennode:setup
bin/n98-magerun.phar --root-dir=src cache:clean > /dev/null

echo -e "${GREEN}All done!${NONE}"