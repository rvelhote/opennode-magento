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
docker build --no-cache -t magento-nginx docker/nginx/ > /dev/null

echo -e "${YELLOW}Building PHP...${NONE}"
docker build --no-cache -t magento-php docker/php/ > /dev/null

echo -e "${GREEN}All done!${NONE}"