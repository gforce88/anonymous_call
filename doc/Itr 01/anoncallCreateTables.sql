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
  `inx`                 int(11)         NOT NULL,
  `partnerInx`          int(11)         NOT NULL,
  `userName`            varchar(256)    NOT NULL,
  `pw`                  varchar(256)    NOT NULL,
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ----------------------------
-- Table structure for partners
-- ----------------------------
DROP TABLE IF EXISTS `partners`;
CREATE TABLE `partners` (
  `inx`                 int(11)         NOT NULL    AUTO_INCREMENT,
  `name`                varchar(256)    NOT NULL,
  `revShare`            decimal(4,0)    NOT NULL,
  `minCallBlkDur`       int(11)         NOT NULL,
  `inviteExpireTimeDur` int(11)         NOT NULL,
  `maxNumRings`         int(11)         NOT NULL    DEFAULT '5',
  `resourcePath`        varchar(1024)   NOT NULL,
  `emailAddr`           varchar(256)    NOT NULL,
  `inviteEmailSubject`  varchar(256)    NOT NULL,
  `inviteEmailBody`     varchar(2048)   NOT NULL,
  `confirmEmailSubject` varchar(256)    NOT NULL,
  `confirmEmailBody`    varchar(2048)   NOT NULL,
  `thanksEmailSubject`  varchar(256)    NOT NULL,
  `thanksEmailBody`     varchar(2048)   NOT NULL,
  `address1`            varchar(1024)               DEFAULT NULL,
  `address2`            varchar(1024)               DEFAULT NULL,
  `postalCode`          varchar(25)                 DEFAULT NULL,
  `phoneNum`            varchar(25)                 DEFAULT NULL,
  `country`             varchar(128)                DEFAULT NULL,
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `inx`                 int(11)         NOT NULL    AUTO_INCREMENT,
  `userAlias`           varchar(256)                DEFAULT NULL,
  `phoneNum`            varchar(25)                 DEFAULT NULL,
  `email`               varchar(256)                DEFAULT NULL,
  `paypalToken`         varchar(256)                DEFAULT NULL,
  `createTime`          datetime        NOT NULL    DEFAULT NOW(),
  PRIMARY KEY (`inx`,`phoneNum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ----------------------------
-- Table structure for invites
-- ----------------------------
DROP TABLE IF EXISTS `invites`;
CREATE TABLE `invites` (
  `inx`                 int(11)         NOT NULL    AUTO_INCREMENT,
  `partnerInx`          int(11)         NOT NULL,
  `inviterInx`          int(11)         NOT NULL,
  `inviteeInx`          int(11)         NOT NULL,
  `inviteToken`         varchar(256),
  `inviteMsg`           varchar(2048)               DEFAULT NULL,
  `inviteTime`          timestamp       NOT NULL    DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ----------------------------
-- Table structure for calls
-- ----------------------------
DROP TABLE IF EXISTS `calls`;
CREATE TABLE `calls` (
  `inx`                 int(11)         NOT NULL    AUTO_INCREMENT,
  `inviteInx`           int(11)         NOT NULL,
  `callResult`          int(11)         NOT NULL,
  `callDuration`        time            NOT NULL    DEFAULT '00:00:00',
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ----------------------------
-- Table structure for callresult
-- ----------------------------
DROP TABLE IF EXISTS `callresult`;
CREATE TABLE `callresult` (
  `inx`                 int(11)         NOT NULL,
  `desc`                varchar(256)    NOT NULL,
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ----------------------------
-- Table structure for phonenumpool
-- ----------------------------
DROP TABLE IF EXISTS `phonenumpool`;
CREATE TABLE `phonenumpool` (
  `inx`                 int(11)         NOT NULL,
  `partnerInx`          int(11)         NOT NULL,
  `phoneNum`            int(11)         NOT NULL,
  `countryInx`          int(11)         NOT NULL,
  `lastUsed`            timestamp       NOT NULL    DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ----------------------------
-- Table structure for countries
-- ----------------------------
DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries` (
  `isoCode`             int(11)         NOT NULL,
  `desc`                varchar(256)    NOT NULL,
  PRIMARY KEY (`isoCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ----------------------------
-- Records
-- ----------------------------
INSERT `callresult` (`inx`, `desc`)
        VALUES (1, 'Init');
INSERT `callresult` (`inx`, `desc`)
        VALUES (2, 'Pickup');
INSERT `callresult` (`inx`, `desc`)
        VALUES (3, 'Conference');
INSERT `callresult` (`inx`, `desc`)
        VALUES (4, 'Answer');
INSERT `countries` (`isoCode`, `desc`)
        VALUES (1, 'United States');
INSERT `countries` (`isoCode`, `desc`)
        VALUES (81, 'Japan');

-- ----------------------------
-- Test data
-- ----------------------------
INSERT `admins` (`inx`, `partnerInx`, `userName`, `pw`)
        VALUES (1, 0, 'admin', 'admin');
INSERT `partners` (`name`, `revShare`, `minCallBlkDur`, `inviteExpireTimeDur`, `maxNumRings`, `resourcePath`, `emailAddr`, `inviteEmailSubject`, `inviteEmailBody`, `confirmEmailSubject`, `confirmEmailBody`, `thanksEmailSubject`, `thanksEmailBody`, `address1`, `address2`, `postalcode`, `phoneNum`, `country`) VALUES ('EnTest', 12.34, 5, 8, 5, 'OutOfScopt', 'EnTest@email.com', 'You have been invited to a call %1s', 'You have been invited to call %1s using Tokumei number.<br>He / she leaves a message to you: %2s<br>please click this link to call %3s', 'confirmEmailSubject', 'confirmEmailBody', 'thankEmailSubject', 'thankEmailBody', 'address1', 'address2', '123456', '11234567890', 'US');
INSERT `partners` (`name`, `revShare`, `minCallBlkDur`, `inviteExpireTimeDur`, `maxNumRings`, `resourcePath`, `emailAddr`, `inviteEmailSubject`, `inviteEmailBody`, `confirmEmailSubject`, `confirmEmailBody`, `thanksEmailSubject`, `thanksEmailBody`, `address1`, `address2`, `postalcode`, `phoneNum`, `country`)
        VALUES ('JpTest', 12.34, 5, 8, 5, 'OutOfScopt', 'JpTest@email.com', '%1s邀请你拨打电话', '%1s邀请你拨打匿名电话。<br>这是对方的留言：  %2s<br>请点此链接拨打电话%3s', 'confirmEmailSubject', 'confirmEmailBody', 'thankEmailSubject', 'thankEmailBody', 'address1', 'address2', '123456', '811234567890', 'JP');


