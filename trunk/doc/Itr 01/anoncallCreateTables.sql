/*
MySQL Data Transfer
Source Host: localhost
Source Database: anoncall
Target Host: localhost
Target Database: anoncall
Date: 3/16/2014 1:05:46 PM
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for admins
-- ----------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `inx` int(11) NOT NULL,
  `partnerInx` int(11) NOT NULL,
  `userName` varchar(256) NOT NULL,
  `pw` varchar(256) NOT NULL,
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ----------------------------
-- Table structure for callresult
-- ----------------------------
DROP TABLE IF EXISTS `callresult`;
CREATE TABLE `callresult` (
  `inx` int(11) NOT NULL,
  `desc` varchar(256) NOT NULL,
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ----------------------------
-- Table structure for calls
-- ----------------------------
DROP TABLE IF EXISTS `calls`;
CREATE TABLE `calls` (
  `inx` int(11) NOT NULL AUTO_INCREMENT,
  `inviteInx` int(11) NOT NULL,
  `callResult` int(11) NOT NULL,
  `callDuration` time NOT NULL DEFAULT '00:00:00',
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ----------------------------
-- Table structure for countries
-- ----------------------------
DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries` (
  `isoCode` int(11) NOT NULL,
  `desc` varchar(255) NOT NULL,
  PRIMARY KEY (`isoCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ----------------------------
-- Table structure for invites
-- ----------------------------
DROP TABLE IF EXISTS `invites`;
CREATE TABLE `invites` (
  `inx` int(11) NOT NULL AUTO_INCREMENT,
  `partnerInx` int(11) NOT NULL,
  `inviteUserInx` int(11) NOT NULL,
  `respondUserInx` int(11) NOT NULL,
  `inviteMsg` varchar(2056) DEFAULT NULL,
  `inviteTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ----------------------------
-- Table structure for partners
-- ----------------------------
DROP TABLE IF EXISTS `partners`;
CREATE TABLE `partners` (
  `inx` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `revShare` decimal(4,0) NOT NULL,
  `minCallBlkDur` int(11) NOT NULL,
  `inviteExpireTimeDur` int(11) NOT NULL,
  `maxNumRings` int(11) NOT NULL DEFAULT '5',
  `resourcePath` varchar(1024) NOT NULL,
  `inviteEmailAddr` varchar(256) NOT NULL,
  `inviteEmailSubject` varchar(256) NOT NULL,
  `inviteEmailBody` varchar(2048) NOT NULL,
  `address 1` varchar(1024) DEFAULT NULL,
  `address 2` varchar(1024) DEFAULT NULL,
  `postal code` varchar(256) DEFAULT NULL,
  `phoneNum` int(128) DEFAULT NULL,
  `country` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ----------------------------
-- Table structure for phonenumpool
-- ----------------------------
DROP TABLE IF EXISTS `phonenumpool`;
CREATE TABLE `phonenumpool` (
  `inx` int(11) NOT NULL,
  `partnerInx` int(11) NOT NULL,
  `phoneNum` int(11) NOT NULL,
  `countryInx` int(11) NOT NULL,
  `lastUsed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `inx` int(11) NOT NULL,
  `userAlias` varchar(256) DEFAULT NULL,
  `phoneNum` int(25) NOT NULL,
  `email` varchar(256) DEFAULT NULL,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`inx`,`phoneNum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ----------------------------
-- Records 
-- ----------------------------
