#!/bin/sh

# @package PublishPress
# @author PressShack
#
# Copyright (c) 2017 PressShack
#
# This file is part of PublishPress
#
# PublishPress is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# PublishPress is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with PublishPress.  If not, see <http://www.gnu.org/licenses/>.

export PLUGIN_SLUG=${PLUGIN_SLUG:-publishpress}
export WP_VERSION=${WP_VERSION:-master}
export WP_PATH=${WP_PATH:-./tmp/wordpress}
export WP_PATH=`cd "${WP_PATH}";pwd`
export PLUGIN_PATH=$WP_PATH/src/wp-content/plugins/$PLUGIN_SLUG
export MYSQL_VERSION=${MYSQL_VERSION:-latest}
export PHP_VERSION=${PHP_VERSION:-7.1}
export BASE_PATH=`pwd`

# Load user's rc file, if exists. This will load the local aliases
# Specially for local development with Docker
if [ -f ~/.bashrc ]
then
    source ~/.bashrc
fi

# Clone the Wordpress test repo
echo "Making sure we have Wordpress from tag $WP_VERSION"
if [ ! -d $WP_PATH ]
then
    mkdir $WP_PATH
    git clone --depth=1 --branch $WP_VERSION git://develop.git.wordpress.org/ $WP_PATH
fi
cd $WP_PATH
git checkout $WP_VERSION
cd $BASE_PATH

# Build a package
echo "Building a package for the plugin..."
robo pack:build

# Remove the old plugin from the temporary wordpress path
if [ -d $PLUGIN_PATH ]
then
    rm -rf $PLUGIN_PATH/*
else
    mkdir $PLUGIN_PATH
fi

# Unzip the new package inside the tests plugin folder
echo "Uncompressing the plugin..."
unzip -q package/${PLUGIN_SLUG}.zip -d $PLUGIN_PATH

# Start a docker container for the mysql
echo "Starting MySQL..."
MYSQL_CONT_ID=$(docker run -d --rm -e MYSQL_DATABASE=wp_tests -e MYSQL_USER=wp_tests -e MYSQL_PASSWORD=wp_tests -e MYSQL_ROOT_PASSWORD=wp_tests mysql:${MYSQL_VERSION})
echo "MySQL container ID: $MYSQL_CONT_ID"

# Wait until the MySQL server is loaded
echo "Waiting for MySQL..."
until echo `docker exec -t $MYSQL_CONT_ID mysqladmin ping` | grep "mysqld is alive" -C 99999; do echo '.'; sleep 1; done

# Update the DB settings
echo "Updating settings for Wordpress..."
cp $WP_PATH/wp-tests-config-sample.php $WP_PATH/wp-tests-config.php
sed -i '' -e "s/youremptytestdbnamehere/wp_tests/" $WP_PATH/wp-tests-config.php
sed -i '' -e "s/yourusernamehere/wp_tests/" $WP_PATH/wp-tests-config.php
sed -i '' -e "s/yourpasswordhere/wp_tests/" $WP_PATH/wp-tests-config.php
sed -i '' -e "s/localhost/db/" $WP_PATH/wp-tests-config.php

# Run the tests on PHPUnit
echo "Starting the container for PHP..."
PHP_CONT_ID=$(docker run -d --rm --link $MYSQL_CONT_ID:db -v $WP_PATH:/var/www/html -v $BASE_PATH:/plugin  alledia/apache-php-codeceptiont:$PHP_VERSION)
echo "PHP container ID: $PHP_CONT_ID"

echo "Waiting for MySQL..."
until echo `docker exec -t $PHP_CONT_ID php -f /plugin/tests/_test-db.php` | grep "1" -C 99999; do echo '.'; sleep 1; done

echo "Running the tests"
# Change to wpcept
docker exec -t $PHP_CONT_ID bash -c "cd /plugin; codeception run unit"

# Stop containers
echo "Stopping containers..."
docker stop $MYSQL_CONT_ID $PHP_CONT_ID

echo "Finished"
