#!/usr/bin/env bash
root_dir=$PWD

rm -f wp-rocket.zip

echo "Copy wp-rocket to temp"
mkdir -p ../wp-rocket-tmp/wp-rocket/
rsync -av . ../wp-rocket-tmp/wp-rocket --exclude node_modules --exclude vendor --exclude bin --exclude src --exclude tests --exclude .git --exclude .github --exclude .tx --quiet

echo "Move working directory to temp one"
cd ../wp-rocket-tmp/wp-rocket
echo "Start composer"
composer install --no-dev --no-scripts --no-interaction --quiet

echo "Build compressed file"
cd ../
zip -r $root_dir/wp-rocket.zip wp-rocket -x "*/.*" "*/composer*" "*/gulpfile.js" "*/package*" "*/php*" --quiet

cd ../
rm -rf wp-rocket-tmp
