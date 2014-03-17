#!/bin/bash
today=`date +"%Y-%m-%d_%H:%M:%S"`
backupPath=/root/workspace/backup/$today

# Backup
mkdir $backupPath
cp -rf /root/workspace/AnonCall/public      $backupPath
cp -rf /root/workspace/AnonCall/application $backupPath
cp -rf /root/workspace/AnonCall/library     $backupPath

# Deploy
rm -rf /root/workspace/AnonCall/public
rm -rf /root/workspace/AnonCall/application
rm -rf /root/workspace/AnonCall/library
cp -rf /root/workspace/dist/public      /root/workspace/AnonCall/
cp -rf /root/workspace/dist/application /root/workspace/AnonCall/
cp -rf /root/workspace/dist/library     /root/workspace/AnonCall/
rm -f /root/workspace/AnonCall/application/configs/application.ini
cp -f /root/workspace/AnonCall/application/configs/application_Staging.ini /root/workspace/AnonCall/application/configs/application.ini

cp -rf /usr/local/ZendFramework-1.12.5-minimal/library/Zend /root/workspace/AnonCall/library
