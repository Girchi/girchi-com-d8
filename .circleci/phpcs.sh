#!/bin/bash

if [[ -z "${CIRCLE_PULL_REQUEST}" ]];
then
	echo "This is not a pull request, no PHPCS needed."
	exit 0
else
	echo "This is a pull request, continuing"
fi

# Check if phpcs.xml is present. Don't do anything if it isn't.
if [ ! -f phpcs.xml ]
then
	echo "No phpcs.xml file found. Nothing to do."
	exit 0
fi

if [[ -z $GITHUB_TOKEN ]];
then
	echo "GITHUB_TOKEN not set"
	exit 1
fi

regexp="[[:digit:]]\+$"
PR_NUMBER=`echo $CIRCLE_PULL_REQUEST | grep -o $regexp`

url="https://api.github.com/repos/$CIRCLE_PROJECT_USERNAME/$CIRCLE_PROJECT_REPONAME/pulls/$PR_NUMBER"

target_branch=$(curl -s -X GET -G \
$url \
-d access_token=$GITHUB_TOKEN | jq '.base.ref' | tr -d '"')

echo "Resetting $target_branch to where the remote version is..."
git checkout -q $target_branch

git reset --hard -q origin/$target_branch

git checkout -q $CIRCLE_BRANCH

echo "Getting list of changed files..."
changed_files=$(git diff --name-only $target_branch..$CIRCLE_BRANCH -- '*.php')

if [[ -z $changed_files ]]
then
	echo "There are no files to check."
	exit 0
fi

# Get wpcs
echo "Registering drupal standarts"
./vendor/bin/phpcs --config-set installed_paths $(pwd)/wpcs

echo "Checking installed paths"
./vendor/bin/phpcs -i

echo "Running phpcs..."
./vendor/bin/phpcs $changed_files

