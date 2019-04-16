#!/bin/bash

# Load global configurations.
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
source $DIR/config

SETTINGS_LOCAL="web/sites/default/settings.local.php"
SETTINGS_PRODUCTION="environments/settings.production.php"
SETTINGS_STAGING="environments/settings.staging.php"
SETTINGS_DEVELOP="environments/settings.develop.php"

SERVICES="web/sites/default/services.local.yml"
SERVICES_PRODUCTION="environments/services.production.yml"
SERVICES_STAGING="environments/services.staging.yml"
SERVICES_DEVELOP="environments/services.develop.yml"

cd $DOCROOT

chmod +w web/sites/default

# Put random salt in settings.php
HASHSTRING=$(LC_ALL=C tr -dc 'A-Za-z0-9-_' </dev/urandom | head -c 64)
# Different commands for string replace by OS (darwin* is MACOSX)
if [[ "$OSTYPE" == darwin* ]]; then
  sed -i '' "s/_HASH_SALT_HERE_REPLACED_BY_PROJECT_INSTALL_SH_/$HASHSTRING/g" "web/sites/default/settings.php"
else
  sed -i "s/_HASH_SALT_HERE_REPLACED_BY_PROJECT_INSTALL_SH_/$HASHSTRING/g" "web/sites/default/settings.php"
fi

# Copy environment config to web/sites/default/settings.local.php
if [ "$1" = 'prod' ]; then
  # Copy from .dist if settings.production.php doesn't exist
  if [ ! -f $SETTINGS_PRODUCTION ]; then
    cp $SETTINGS_PRODUCTION.dist $SETTINGS_LOCAL
  else
    cp $SETTINGS_PRODUCTION $SETTINGS_LOCAL
  fi

  # Copy from .dist if services.production.yml doesn't exist
  if [ ! -f $SERVICES_PRODUCTION ]; then
    cp $SERVICES_PRODUCTION.dist $SERVICES
  else
    cp $SERVICES_PRODUCTION $SERVICES
  fi
elif [ "$1" = 'staging' ]; then
  # Copy from .dist if settings.staging.php doesn't exist
  if [ ! -f $SETTINGS_STAGING ]; then
    cp $SETTINGS_STAGING.dist $SETTINGS_LOCAL
  else
    cp $SETTINGS_STAGING $SETTINGS_LOCAL
  fi

  # Copy from .dist if services.staging.yml doesn't exist
  if [ ! -f $SERVICES_STAGING ]; then
    cp $SERVICES_STAGING.dist $SERVICES
  else
    cp $SERVICES_STAGING $SERVICES
  fi
else
  # Copy from .dist if settings.develop.php doesn't exist
  if [ ! -f $SETTINGS_DEVELOP ]; then
    cp $SETTINGS_DEVELOP.dist $SETTINGS_LOCAL
  else
    cp $SETTINGS_DEVELOP $SETTINGS_LOCAL
  fi

  # Copy from .dist if services.develop.yml doesn't exist
  if [ ! -f $SERVICES_DEVELOP ]; then
    cp $SERVICES_DEVELOP.dist $SERVICES
  else
    cp $SERVICES_DEVELOP $SERVICES
  fi
fi

chmod 755 $SETTINGS_LOCAL
