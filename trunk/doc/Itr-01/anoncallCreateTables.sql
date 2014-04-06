/*
MySQL Data Transfer
Source Host: localhost
Source Database: anoncall
Target Host: localhost
Target Database: anoncall
Date: 3/16/2014 1:05:46 PM
source /root/workspace/dist/doc/Itr-01/anoncallCreateTables.sql;
*/

SET FOREIGN_KEY_CHECKS=0;

-- ------------------------------------
-- Table structure for admins
-- ------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `inx`                 int(11)         NOT NULL                    COMMENT 'primary key',
  `partnerInx`          int(11)         NOT NULL                    COMMENT 'inx to partner table',
  `userName`            varchar(256)    NOT NULL                    COMMENT 'log in user name',
  `pw`                  varchar(256)    NOT NULL                    COMMENT 'password hash',
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ------------------------------------
-- Table structure for partners
-- ------------------------------------
DROP TABLE IF EXISTS `partners`;
CREATE TABLE `partners` (
  `inx`                 int(11)         NOT NULL    AUTO_INCREMENT  COMMENT 'parimary key',
  `name`                varchar(256)    NOT NULL                    COMMENT 'partner name',
  `revShare`            decimal(2, 0)   NOT NULL                    COMMENT '% revshare for partner',
  `chargeAmount`        decimal(6, 2)   NOT NULL                    COMMENT 'charge amount for each min call blk dur',
  `minCallBlkDur`       int(11)         NOT NULL                    COMMENT 'minimum call block duration as defined in the PRD (second)',
  `callAlertOffset`     int(11)         NOT NULL                    COMMENT 'offset from the min call block duration to alert an message to the user (second)',
  `inviteExpireDur`     int(11)         NOT NULL                    COMMENT 'duration of time an invite link is live for (hour)',
  `maxRingDur`          int(11)         NOT NULL    DEFAULT 20      COMMENT 'maximum duration of rings system should wait before hanging up (second)',
  `resourcePath`        varchar(1024)   NOT NULL                    COMMENT 'server path to partner specific resources such as CSS, images and other configurable resources',
  `phoneNum`            varchar(25)     NOT NULL                    COMMENT 'partner phone number',
  `emailAddr`           varchar(256)    NOT NULL                    COMMENT 'from address to use for invite email',
  `inviteEmailSubject`  varchar(256)    NOT NULL                    COMMENT 'subject line to use for invite email',
  `inviteEmailBody`     varchar(2048)   NOT NULL                    COMMENT 'invite email body',
  `confirmEmailSubject` varchar(256)    NOT NULL                    COMMENT 'subject line to use for confirm email',
  `confirmEmailBody`    varchar(2048)   NOT NULL                    COMMENT 'confirm email body',
  `thanksEmailSubject`  varchar(256)    NOT NULL                    COMMENT 'subject line to use for thanks email',
  `thanksEmailBody`     varchar(2048)   NOT NULL                    COMMENT 'thanks email body',
  `address1`            varchar(1024)               DEFAULT NULL    COMMENT 'partner address line 1',
  `address2`            varchar(1024)               DEFAULT NULL    COMMENT 'partner address line 2',
  `country`             varchar(128)                DEFAULT NULL    COMMENT 'country where partner is located',
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ------------------------------------
-- Table structure for users
-- ------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `inx`                 int(11)         NOT NULL    AUTO_INCREMENT  COMMENT 'primary key',
  `userAlias`           varchar(256)                DEFAULT NULL    COMMENT 'alias of user, may or may not be user name',
  `phoneNum`            varchar(25)                 DEFAULT NULL    COMMENT 'user phone number',
  `email`               varchar(256)                DEFAULT NULL    COMMENT 'user email',
  `paypalToken`         varchar(256)                DEFAULT NULL    COMMENT 'paypal token for the user',
  `createTime`          timestamp       NOT NULL                    COMMENT 'time this user record was created',
  PRIMARY KEY (`inx`,`phoneNum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ------------------------------------
-- Table structure for invites
-- ------------------------------------
DROP TABLE IF EXISTS `invites`;
CREATE TABLE `invites` (
  `inx`                 int(11)         NOT NULL    AUTO_INCREMENT  COMMENT 'primary key',
  `partnerInx`          int(11)         NOT NULL                    COMMENT 'index to partner table',
  `inviterInx`          int(11)         NOT NULL                    COMMENT 'index to user table of users who sent invitation',
  `inviteeInx`          int(11)         NOT NULL                    COMMENT 'index of user who an invitation was sent to',
  `inviteToken`         varchar(256)                                COMMENT 'token in URL of invite email',
  `inviteMsg`           varchar(2048)               DEFAULT NULL    COMMENT 'invite message',
  `inviteTime`          timestamp       NOT NULL                    COMMENT 'time this invitation was created / extended',
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ------------------------------------
-- Table structure for calls
-- ------------------------------------
DROP TABLE IF EXISTS `calls`;
CREATE TABLE `calls` (
  `inx`                 int(11)         NOT NULL    AUTO_INCREMENT  COMMENT 'primary key',
  `inviteInx`           int(11)         NOT NULL                    COMMENT 'index to invites table',
  `callType`            int(1)          NOT NULL                    COMMENT '0 - first call inviter; 1 - first call invitee',
  `callResult`          int(2)          NOT NULL    DEFAULT 0       COMMENT 'index to callresults table',
  `paypalToken`         varchar(64)     NOT NULL                    COMMENT 'payapl token',
  `tropoSession`        varchar(64)                                 COMMENT 'tropo session ID',
  `callInitTime`        timestamp                   DEFAULT 0       COMMENT 'call init time',
  `callStartTime`       timestamp                   DEFAULT 0       COMMENT 'call start time',
  `callEndTime`         timestamp                   DEFAULT 0       COMMENT 'call end time',
  `nextRemindTime`      timestamp                   DEFAULT 0       COMMENT 'next remind time',
  `nextChargeTime`      timestamp                   DEFAULT 0       COMMENT 'next charge time',
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ------------------------------------
-- Table structure for callresult
-- ------------------------------------
DROP TABLE IF EXISTS `callresult`;
CREATE TABLE `callresult` (
  `inx`                 int(11)         NOT NULL                    COMMENT 'primary key',
  `desc`                varchar(256)    NOT NULL                    COMMENT 'text description of result',
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ------------------------------------
-- Table structure for phonenumpool
-- ------------------------------------
DROP TABLE IF EXISTS `phonenumpool`;
CREATE TABLE `phonenumpool` (
  `inx`                 int(11)         NOT NULL                    COMMENT 'primary key',
  `partnerInx`          int(11)         NOT NULL                    COMMENT 'link to partner who this number can be used for, if zero, number can be used for all partners',
  `phoneNum`            int(11)         NOT NULL                    COMMENT 'phone number (with country and area code)',
  `countryInx`          int(11)         NOT NULL                    COMMENT 'the country of origin of this phone number, links to countries table',
  `lastUsed`            timestamp       NOT NULL                    COMMENT 'time stamp the last time this number was used to connect users',
  PRIMARY KEY (`inx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ------------------------------------
-- Table structure for countries
-- ------------------------------------
DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries` (
  `isoCode`             int(11)         NOT NULL                    COMMENT 'iso 3166 country code',
  `desc`                varchar(256)    NOT NULL                    COMMENT 'text description of country',
  PRIMARY KEY (`isoCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16;

-- ------------------------------------
-- Master Data
-- ------------------------------------
INSERT `callresult` (`inx`, `desc`)
        VALUES (0, 'Init');
INSERT `callresult` (`inx`, `desc`)
        VALUES (1, '1stLeg_NoAnswer');
INSERT `callresult` (`inx`, `desc`)
        VALUES (2, '1stLeg_IsAnswerMachine');
INSERT `callresult` (`inx`, `desc`)
        VALUES (3, '1stLeg_Answered');
INSERT `callresult` (`inx`, `desc`)
        VALUES (4, '2ndLeg_NoAnswer');
INSERT `callresult` (`inx`, `desc`)
        VALUES (5, '2ndLeg_Answered');
INSERT `callresult` (`inx`, `desc`)
        VALUES (-1, 'Error');

INSERT `countries` (`isoCode`, `desc`)
        VALUES (1, 'United States');
INSERT `countries` (`isoCode`, `desc`)
        VALUES (81, 'Japan');

-- ------------------------------------
-- Test data
-- ------------------------------------
INSERT `admins` (`inx`, `partnerInx`, `userName`, `pw`)
        VALUES (1, 0, 'admin', PASSWORD('admin'));
INSERT `partners` (
          `name`,
          `revShare`,
          `chargeAmount`,
          `minCallBlkDur`,
          `callAlertOffset`,
          `inviteExpireDur`,
          `maxRingDur`,
          `resourcePath`,
          `phoneNum`,
          `emailAddr`,
          `inviteEmailSubject`,
          `inviteEmailBody`,
          `confirmEmailSubject`,
          `confirmEmailBody`,
          `thanksEmailSubject`,
          `thanksEmailBody`,
          `address1`,
          `address2`,
          `country`)
        VALUES (
          'EnTest',
          12,
          12.34,
          300,
          30,
          8,
          30,
          'OutOfScopt',
          '10123456789',
          'EnTest@email.com',
          'You have been invited to a call %1s',
          'You have been invited to call %1s using Tokumei number.<br>He / she leaves a message to you: %2s<br>please click this link to call <a href="%3s">%3s</a>',
          'confirmEmailSubject',
          'confirmEmailBody',
          'thankEmailSubject',
          'thankEmailBody',
          'address1',
          'address2',
          'US');
INSERT `partners` (
          `name`,
          `revShare`,
          `chargeAmount`,
          `minCallBlkDur`,
          `callAlertOffset`,
          `inviteExpireDur`,
          `maxRingDur`,
          `resourcePath`,
          `phoneNum`,
          `emailAddr`,
          `inviteEmailSubject`,
          `inviteEmailBody`,
          `confirmEmailSubject`,
          `confirmEmailBody`,
          `thanksEmailSubject`,
          `thanksEmailBody`,
          `address1`,
          `address2`,
          `country`)
        VALUES (
          'JpTest',
          12,
          12.34,
          300,
          30,
          8,
          30,
          'OutOfScopt',
          '810123456789',
          'JpTest@email.com',
          '%1s邀请你拨打电话',
          '%1s邀请你拨打匿名电话。<br>这是对方的留言：  %2s<br>请点此链接拨打电话<a href="%3s">%3s</a>',
          '确认邮件标题',
          '确认邮件内容',
          '感谢邮件标题',
          '感谢邮件内容',
          '地址1',
          '地址2',
          'JP');
