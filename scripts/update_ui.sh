#!/bin/bash

# Define variables

# Script path
BASE_DIR=$(dirname "$0")

# Departure paths
DEP_THEME_PATH="$BASE_DIR/../front/dist"
DEP_CSS_PATH=${DEP_THEME_PATH}
DEP_JS_PATH=${DEP_THEME_PATH}
DEP_IMG_PATH="$DEP_THEME_PATH/images"
DEP_FONTS_PATH="$DEP_THEME_PATH/fonts"

# Destination paths
DEST_THEME_PATH="$BASE_DIR/../web/themes/custom/girchi"
DEST_DIST_PATH="$DEST_THEME_PATH/dist"
DEST_CSS_PATH="$DEST_DIST_PATH/css"
DEST_JS_PATH="$DEST_DIST_PATH/js"
DEST_IMG_PATH="$DEST_CSS_PATH/images"
DEST_FONTS_PATH="$DEST_CSS_PATH/fonts"

# Check if DEP_THEME_PATH exists
if [[ -d "$DEP_THEME_PATH" ]]; then
    # Create destination directories if they don't exists
    mkdir -p ${DEST_DIST_PATH}
    mkdir -p ${DEST_CSS_PATH}
    mkdir -p ${DEST_JS_PATH}
    mkdir -p ${DEST_IMG_PATH}
    mkdir -p ${DEST_FONTS_PATH}

    # Copy files
    cp ${DEP_CSS_PATH}/bundle-*.css ${DEST_CSS_PATH}/global.css
    #cp ${DEP_CSS_PATH}/bundle-*.js.map ${DEST_CSS_PATH}/custom.css.map
    cp ${DEP_JS_PATH}/bundle-*.js ${DEST_JS_PATH}/script.js
    #cp ${DEP_JS_PATH}/bundle-*.js.map ${DEST_JS_PATH}/script.js.map
    cp ${DEP_IMG_PATH}/* ${DEST_IMG_PATH}/
    cp ${DEP_FONTS_PATH}/* ${DEST_FONTS_PATH}/
else
    echo -e "\e[0;5;41m ERROR: Dist folder not found! Please, run make build-ui and then composer install.\e[00m"
    exit 1
fi
