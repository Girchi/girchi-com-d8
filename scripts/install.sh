#!/bin/bash

#links
WEBSITE="http://girchi.docker.localhost"
MAILHOG="http://mailhog.girchi.docker.localhost"
PMA="http://pma.girchi.docker.localhost"

#colors 
NONE='\033[00m'
GREEN='\033[01;32m'
BLUE='\033[1;36m'

clear
echo -e "${BLUE}[1/7] Starting up containers...\n\v${NONE}"
make up

echo -e "\n${BLUE}[2/7] Building UI Project...\n\v${NONE}"
mkdir -p front
make build-ui

echo -e "\n${BLUE}[3/7] Running pre-install script...\n\v${NONE}"
./scripts/pre-install.sh

echo -e "\n${BLUE}[4/7] Running composer install...\n\v${NONE}"
docker-compose exec php composer install

echo -e "\n${BLUE}[5/7] Installing drupal...\n\v${NONE}"
docker-compose exec php drush si --existing-config --account-pass=1234  -y -vvv 

echo -e "\n${BLUE}[6/7] Importing translations...\n\v${NONE}"
docker-compose exec php drush language-import

echo -e "\n${BLUE}[7/7] Importing config...\n\v${NONE}"
docker-compose exec php drush cim -y -vvv

echo -e "\n${BLUE}HGirchi Website is ready${NONE} ${GREEN}\n\vWebsite${NONE}  - " ${GREEN}${WEBSITE}${NONE}"${GREEN}\n\vMailHog${NONE} - " ${GREEN}${MAILHOG}${NONE}"${GREEN}\n\vphpMyAdmin${NONE} - " ${GREEN}${PMA}${NONE}"\n"
