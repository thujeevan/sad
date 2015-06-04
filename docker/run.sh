#!/bin/bash

chown -R :www-data /var/www/sad
chmod -R a+rX /var/www/sad

/create_mysql_sad_user_db.sh

cd /var/www/sad && php app/console doctrine:schema:update --force && \
 rm -rf app/cache/* app/logs/*

source /etc/apache2/envvars
exec apache2 -D FOREGROUND