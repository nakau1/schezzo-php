#!/bin/bash

if [ ! -e selenium-server-standalone-2.53.1.jar ]; then
  wget http://selenium-release.storage.googleapis.com/2.53/selenium-server-standalone-2.53.1.jar
fi

nohup java -jar selenium-server-standalone-2.53.1.jar -port 8000 &

exit 0
