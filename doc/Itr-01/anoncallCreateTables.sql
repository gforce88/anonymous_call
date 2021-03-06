/*
MySQL Data Transfer
Source Host: localhost
Source Database: Anonymous Voice Calling
Target Host: localhost
Target Database: Anonymous Voice Calling
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
  `freeCallDur`         int(11)         NOT NULL                    COMMENT 'free call duration (second)',
  `chargeAmount`        decimal(6, 2)   NOT NULL                    COMMENT 'charge amount for each min call blk dur (USD)',
  `chargeCurrency`      varchar(3)      NOT NULL                    COMMENT 'charge currency (USD / JPY)',
  `minCallBlkDur`       int(11)         NOT NULL                    COMMENT 'minimum call block duration as defined in the PRD (second)',
  `callRemindOffset`    int(11)         NOT NULL                    COMMENT 'offset from the min call block duration to remind an message to the user (second)',
  `inviteExpireDur`     int(11)         NOT NULL                    COMMENT 'duration of time an invite link is live for (hour)',
  `maxRingDur`          int(11)         NOT NULL    DEFAULT 20      COMMENT 'maximum duration of rings system should wait before hanging up (second)',
  `resourcePath`        varchar(1024)   NOT NULL                    COMMENT 'server path to partner specific resources such as CSS, images and other configurable resources',
  `phoneNum`            varchar(25)     NOT NULL                    COMMENT 'partner phone number',
  `emailAddr`           varchar(256)    NOT NULL                    COMMENT 'from address to use for emails',
  `inviteEmailSubject`  varchar(256)                DEFAULT NULL    COMMENT 'invite email subject',
  `inviteEmailContent`  text(65535)                 DEFAULT NULL    COMMENT 'invite email body',
  `acceptEmailSubject`  varchar(256)                DEFAULT NULL    COMMENT 'accept email subject',
  `acceptEmailContent`  text(65535)                 DEFAULT NULL    COMMENT 'accept email body',
  `declineEmailSubject` varchar(256)                DEFAULT NULL    COMMENT 'decline email subject',
  `declineEmailContent` text(65535)                 DEFAULT NULL    COMMENT 'decline email body',
  `readyEmailSubject`   varchar(256)                DEFAULT NULL    COMMENT 'ready email subject',
  `readyEmailContent`   text(65535)                 DEFAULT NULL    COMMENT 'ready email body',
  `retryEmailSubject`   varchar(256)                DEFAULT NULL    COMMENT 'retry email subject',
  `retryEmailContent`   text(65535)                 DEFAULT NULL    COMMENT 'retry email body',
  `thanksEmailSubject`  varchar(256)                DEFAULT NULL    COMMENT 'subject line to use for thanks email',
  `thanksEmailContent`  text(65535)                 DEFAULT NULL    COMMENT 'thanks email body',
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
  `phoneNum`            varchar(25)                 DEFAULT NULL    COMMENT 'user phone number',
  `email`               varchar(256)                DEFAULT NULL    COMMENT 'user email',
  `profileUrl`          varchar(256)                DEFAULT NULL    COMMENT 'url of user profile',
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
  `inviteResult`        int(2)          NOT NULL    DEFAULT 0       COMMENT 'index to inviteresult table',
  `inviteType`          int(1)          NOT NULL                    COMMENT '1 - inviter pay; 2 - invitee pay',
  `inviteToken`         varchar(256)                                COMMENT 'token in URL of invite email',
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
  `callResult`          int(2)          NOT NULL    DEFAULT 0       COMMENT 'index to callresults table',
  `firstLegSession`     varchar(64)                                 COMMENT 'tropo session ID for 1st leg',
  `secondLegSession`    varchar(64)                                 COMMENT 'tropo session ID for 2nd leg',
  `callInitTime`        timestamp                   DEFAULT 0       COMMENT 'call init time',
  `callStartTime`       timestamp                   DEFAULT 0       COMMENT 'call start time',
  `callConnectTime`     timestamp                   DEFAULT 0       COMMENT 'call connect time',
  `callEndTime`         timestamp                   DEFAULT 0       COMMENT 'call end time',
  `nextRemindTime`      timestamp                   DEFAULT 0       COMMENT 'next remind time',
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
INSERT `countries` (`isoCode`, `desc`)
        VALUES (1, 'United States');
INSERT `countries` (`isoCode`, `desc`)
        VALUES (81, 'Japan');

-- ------------------------------------
-- Test data
-- ------------------------------------
INSERT `admins` (`inx`, `partnerInx`, `userName`, `pw`)
        VALUES (1, 1, 'admin', PASSWORD('1234'));
INSERT `partners` (
          `name`,
          `revShare`,
          `freeCallDur`,
          `chargeAmount`,
          `chargeCurrency`,
          `minCallBlkDur`,
          `callRemindOffset`,
          `inviteExpireDur`,
          `maxRingDur`,
          `resourcePath`,
          `phoneNum`,
          `emailAddr`,
          `address1`,
          `address2`,
          `country`)
        VALUES (
          'En Partner',
          12,
          120,
          2.99,
          'USD',
          500,
          30,
          8,
          30,
          'OutOfScopt',
          '10123456789',
          'EnPartner@email.com',
          'address1',
          'address2',
          'US');
INSERT `partners` (
          `name`,
          `revShare`,
          `freeCallDur`,
          `chargeAmount`,
          `chargeCurrency`,
          `minCallBlkDur`,
          `callRemindOffset`,
          `inviteExpireDur`,
          `maxRingDur`,
          `resourcePath`,
          `phoneNum`,
          `emailAddr`,
          `address1`,
          `address2`,
          `country`)
        VALUES (
          'Jp Partner',
          12,
          120,
          12.34,
          'JPY',
          300,
          30,
          8,
          30,
          'OutOfScopt',
          '810123456789',
          'JpPartner@email.com',
          'Address1',
          'Address2',
          'JP');
