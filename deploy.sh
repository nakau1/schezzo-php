#!/usr/bin/env bash
ENV_NAME=false

while getopts e: OPT
do
  case $OPT in
    "e" ) ENV_NAME=$OPTARG ;;
  esac
done

if [ $ENV_NAME != false ] ; then
    cd /var/www/schezzo && \
    git fetch origin && \
    git reset --hard origin/${ENV_NAME} && \
    cd src && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php ./composer-setup.php && \
    php -r "unlink('composer-setup.php');"
    rm -rf ~/.composer/vendor/fxp && \
    php ./composer.phar global require "fxp/composer-asset-plugin:^1.2.0" && \
    php ./composer.phar install && \
    php ./yii migrate/up --interactive=0 && \
    php ./yii cache/flush-schema --interactive=0 && \
    cd ../ && \
    cp ./.ebextensions/MailboxHeader.php ./vendor/swiftmailer/swiftmailer/lib/classes/Swift/Mime/Headers/
fi
