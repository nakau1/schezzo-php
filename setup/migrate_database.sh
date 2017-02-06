#!/bin/bash

if [ -a '/root/migrate_database.log' ]; then
        echo "/root/migrate_database.log exists..."
        echo "database is already migrated!"
        exit 1
fi

cd /vagrant/src
mysql -u root -poz-vision123 -e'DROP DATABASE daifuku; CREATE DATABASE daifuku';
yes | php yii migrate

touch /root/migrate_database.log

exit 0
