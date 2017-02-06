#!/bin/bash

cd /vagrant
composer global require "fxp/composer-asset-plugin:^1.2.0"
composer install
exit 0
