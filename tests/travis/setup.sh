#!/bin/sh

# WordPress test setup script for Travis CI
#
# Author: Benjamin J. Balter ( ben@balter.com | ben.balter.com )
# License: GPL3

export WP_CORE_DIR=/tmp/wordpress
export WP_TESTS_DIR=/tmp/wordpress-tests/tests/phpunit

plugin_slug=$(basename $(pwd))
plugin_dir=$WP_CORE_DIR/wp-content/plugins/$plugin_slug

# Init database
mysql -e 'CREATE DATABASE wordpress_test;' -uroot

# Grab specified version of WordPress from github
wget -nv -O /tmp/wordpress.tar.gz https://github.com/WordPress/WordPress/tarball/$WP_VERSION
mkdir -p $WP_CORE_DIR
tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C $WP_CORE_DIR

# Grab testing framework
git clone git://develop.git.wordpress.org/ /tmp/wordpress-tests

# Put various components in proper folders
cp tests/travis/wp-tests-config.php $WP_TESTS_DIR/wp-tests-config.php

cd ..
mv $plugin_slug $plugin_dir

cd $plugin_dir