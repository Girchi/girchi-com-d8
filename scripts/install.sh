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
echo -e "${BLUE}Starting up containers\n\v${NONE}"
docker-compose pull
docker-compose up -d --remove-orphans
echo -e "\n"    
echo  -e "${BLUE}Running pre-install script\n\v${NONE}"
./scripts/pre-install.sh
echo -e "\n"
echo  -e "${BLUE}Running composer json\n\v${NONE}"
docker-compose exec php composer install
echo -e "\n"
echo  -e "${BLUE}Installing drupal\n\v${NONE}"
docker-compose exec php drush si --existing-config --account-pass=1234  -y -vvv 
echo -e "\n"
echo  -e "${BLUE}Importing translations\n\v${NONE}"
docker-compose exec php drush language-import
echo -e "\n"
echo  -e "${BLUE}Importing config\n\v${NONE}"
docker-compose exec php drush cim -y -vvv
echo -e "\n"
clear
echo  -e "${BLUE}D8 Project is ready${NONE} ${RED}\n\vWebsite${NONE}  - " $GREEN$WEBSITE$NONE"${RED}\n\vMailHog${NONE} - " $GREEN$MAILHOG$NONE"${RED}\n\vphpMyAdmin${NONE} - " $GREEN$PMA$NONE"\n"
sleep 3
