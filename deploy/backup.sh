#!/bin/bash
now=`date +"%Y-%m-%d_%H:%M:%S"`
backupPath=/root/workspace/backup/$now

# Backup source code
mkdir $backupPath
cp -rf /root/workspace/AnonCall/public      $backupPath
cp -rf /root/workspace/AnonCall/application $backupPath
cp -rf /root/workspace/AnonCall/library     $backupPath
cp -rf /root/workspace/AnonCall/shell       $backupPath

# Backup database
mysqldump -uroot -r /root/workspace/backup/$now/anoncall.sql --routine --add-drop-database anoncall
