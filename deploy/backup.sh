#!/bin/bash
today=`date +"%Y-%m-%d_%H:%M:%S"`
backupPath=/root/workspace/backup/$today

# Backup
mkdir $backupPath
cp -rf /root/workspace/AnonCall/public      $backupPath
cp -rf /root/workspace/AnonCall/application $backupPath
cp -rf /root/workspace/AnonCall/library     $backupPath
cp -rf /root/workspace/AnonCall/shell       $backupPath
