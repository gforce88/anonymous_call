#!/bin/bash
rm -rf /root/workspace/Anonym/public
rm -rf /root/workspace/Anonym/application
rm -rf /root/workspace/Anonym/library
cp -rf /root/workspace/dist/public      /root/workspace/Anonym/
cp -rf /root/workspace/dist/application /root/workspace/Anonym/
cp -rf /root/workspace/dist/library     /root/workspace/Anonym/
rm -f /root/workspace/Anonym/application/configs/application.ini
cp -f /root/workspace/Anonym/application/configs/application_Staging.ini /root/workspace/Anonym/application/configs/application.ini

cp -rf /usr/local/ZendFramework-1.12.5-minimal/library/Zend /root/workspace/Anonym/library

