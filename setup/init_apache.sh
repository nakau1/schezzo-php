#!/bin/bash

# Check is inited apache
if [ -a '/root/init_apache.log' ]; then
        echo "/root/init_apache.log is exist..."
        echo "init_apache is already inited!"
        exit 1
fi

ln -s /vagrant /var/www/schezzo
cp -p -Rf /vagrant/setup/httpd/httpd.conf /etc/httpd/conf/httpd.conf

# Set inited Log
touch /root/init_apache.log

exit 0
