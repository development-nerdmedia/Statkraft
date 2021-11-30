/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/ STATKRAFTDB /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE STATKRAFTDB;

DROP TABLE IF EXISTS TWEB_EVENTS;
CREATE TABLE `TWEB_EVENTS` (
  `Id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `EventName` varchar(200) NOT NULL,
  `Description` text DEFAULT NULL,
  `EventImage` varchar(100) DEFAULT NULL,
  `EventDate` datetime NOT NULL,
  `CreateTime` datetime DEFAULT current_timestamp() COMMENT 'create time',
  `UpdateTime` datetime DEFAULT NULL COMMENT 'update time',
  `IsDeleted` bit(1) DEFAULT b'0' COMMENT 'Marks if the recored was deleted',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS TWEB_NEWSLETTER;
CREATE TABLE `TWEB_NEWSLETTER` (
  `Id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `Email` varchar(350) DEFAULT NULL,
  `CreateTime` datetime DEFAULT current_timestamp() COMMENT 'create time',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS TWEB_RECYCLE_OPTIONS;
CREATE TABLE `TWEB_RECYCLE_OPTIONS` (
  `Id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `OptionName` varchar(100) DEFAULT NULL,
  `CreateTime` datetime DEFAULT current_timestamp() COMMENT 'create time',
  `UpdateTime` datetime DEFAULT NULL COMMENT 'update time',
  `IsDeleted` bit(1) DEFAULT NULL COMMENT 'Marks if the recored was deleted',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS TWEB_RECYCLE_POINTS;
CREATE TABLE `TWEB_RECYCLE_POINTS` (
  `Id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `PointName` varchar(500) NOT NULL,
  `Address` varchar(1000) DEFAULT NULL,
  `Telephone` varchar(30) DEFAULT NULL,
  `Hours` varchar(100) DEFAULT NULL,
  `Latitude` decimal(10,7) DEFAULT NULL,
  `Longitude` decimal(10,7) DEFAULT NULL,
  `UbigeoId` int(11) DEFAULT NULL,
  `Thumbnail` varchar(100) DEFAULT NULL,
  `CreateTime` datetime DEFAULT NULL,
  `UpdateTime` datetime DEFAULT NULL,
  `IsDeleted` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS TWEB_RECYCLE_POINT_OPTIONS;
CREATE TABLE `TWEB_RECYCLE_POINT_OPTIONS` (
  `PointId` int(11) NOT NULL,
  `OptionId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS TWEB_UBIGEOS;
CREATE TABLE `TWEB_UBIGEOS` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ParentId` int(11) NOT NULL DEFAULT 0,
  `Name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DeletedAt` timestamp NULL DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT NULL,
  `UpdatedAt` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=1950 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;