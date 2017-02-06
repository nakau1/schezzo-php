#!/usr/bin/env bash
/sbin/initctl start selenium-server
if [ $? != 0  ]; then
    /sbin/initctl restart selenium-server
fi
