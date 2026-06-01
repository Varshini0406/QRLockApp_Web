-- ============================================================
--  QRLockApp - Full Database Schema
--  Run this in phpMyAdmin or MySQL CLI to set up the database
--  Command: mysql -u root -p < qrlockapp_schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `qrlockapp`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `qrlockapp`;

-- ── Users / Signup ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `signup` (
  `Id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `Username`       VARCHAR(80)      NOT NULL,
  `Mobile`         VARCHAR(15)      NOT NULL UNIQUE,
  `Password`       VARCHAR(255)     NOT NULL,           -- bcrypt hash
  `TimeZone`       VARCHAR(60)      NOT NULL DEFAULT 'Asia/Kolkata',
  `ProfilePicture` VARCHAR(255)     DEFAULT NULL,
  `Active`         TINYINT(1)       NOT NULL DEFAULT 1,
  `created_at`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  KEY `idx_mobile` (`Mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Devices ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `deviceinfo` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `DeviceName` VARCHAR(100)  NOT NULL,
  `DeviceType` VARCHAR(60)   NOT NULL DEFAULT 'Door Lock',
  `DeviceId`   VARCHAR(100)  NOT NULL UNIQUE,           -- matches QR code value
  `number`     VARCHAR(15)   NOT NULL,                  -- owner mobile
  `lock`       ENUM('Yes','NO') NOT NULL DEFAULT 'NO',  -- Yes=Locked, NO=Unlocked
  `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_number` (`number`),
  KEY `idx_device_id` (`DeviceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Device Access Logs ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS `device_logs` (
  `Id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `DeviceID`  VARCHAR(100) NOT NULL,
  `Action`    VARCHAR(60)  NOT NULL,    -- 'Locked' or 'Unlocked'
  `Timestamp` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  KEY `idx_device` (`DeviceID`),
  KEY `idx_timestamp` (`Timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Notifications ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `notification` (
  `Id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `Mobile`     VARCHAR(15)  NOT NULL,
  `message`    TEXT         NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  KEY `idx_mobile` (`Mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Managed Users (access control) ─────────────────────────
CREATE TABLE IF NOT EXISTS `Users` (
  `Id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `Name`       VARCHAR(100) NOT NULL,
  `AccessType` ENUM('Permanent','Temporary') NOT NULL DEFAULT 'Permanent',
  `DeviceId`   INT UNSIGNED DEFAULT NULL,
  `LastSeen`   DATETIME     DEFAULT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  KEY `fk_device` (`DeviceId`),
  CONSTRAINT `fk_users_device` FOREIGN KEY (`DeviceId`) REFERENCES `deviceinfo` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Feedback ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `Feedback` (
  `Id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `Username`   VARCHAR(80)  NOT NULL,
  `Review`     TEXT         NOT NULL,
  `Rating`     TINYINT      NOT NULL CHECK (`Rating` BETWEEN 1 AND 5),
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Activity Status (for future use) ────────────────────────
CREATE TABLE IF NOT EXISTS `activity_status` (
  `Id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `DeviceId`  VARCHAR(100) NOT NULL,
  `Status`    VARCHAR(60)  NOT NULL,
  `UpdatedAt` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Demo seed data (optional, remove in production) ─────────
-- Creates one demo account: mobile=9876543210, password=demo123
INSERT IGNORE INTO `signup` (`Username`, `Mobile`, `Password`, `Active`)
VALUES ('Demo User', '9876543210', '$2y$10$xLJKyZv3PqsQ8mN5oT2iEuaGbR1cD4fHpVkWmS9nX0eZ7YjQO6.mi', 1);
-- password hash above = bcrypt of 'demo123'
