#!/bin/bash
rm -rf /root/workspace/AnonCall/public
rm -rf /root/workspace/AnonCall/application
rm -rf /root/workspace/AnonCall/library
rm -rf /root/workspace/AnonCall/shell
cp -rf /root/workspace/dist/public      /root/workspace/AnonCall/
cp -rf /root/workspace/dist/application /root/workspace/AnonCall/
cp -rf /root/workspace/dist/library     /root/workspace/AnonCall/
cp -rf /root/workspace/dist/shell       /root/workspace/AnonCall/
rm -f /root/workspace/AnonCall/application/configs/application.ini
cp -f /root/workspace/AnonCall/application/configs/application_Staging.ini /root/workspace/AnonCall/application/configs/application.ini
rm -f /root/workspace/AnonCall/application/configs/ivr.ini
cp -f /root/workspace/AnonCall/application/configs/ivr_Staging.ini /root/workspace/AnonCall/application/configs/ivr.ini

cp -rf /usr/local/ZendFramework-1.12.5-minimal/library/Zend /root/workspace/AnonCall/library
