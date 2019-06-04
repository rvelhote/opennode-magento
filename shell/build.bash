#!/bin/bash
# Build a ready to install version of the module that users can copy to their Magento 1.9 deployments
RED="\033[0;31m"
YELLOW="\033[0;33m"
GREEN="\033[0;32m"
NONE="\033[0m"

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

echo -e "${YELLOW}Building Javascript...${NONE}"
npm run build > /dev/null

echo -e "${YELLOW}Building Release...${NONE}"
git ls-tree -r --full-name master | grep -i src/ | awk '{print $4}' | zip dist/OpenNode_Bitcoin.zip -@ > /dev/null

echo -e "${GREEN}All done. Check the DIST folder!${NONE}"