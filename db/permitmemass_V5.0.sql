-- phpMyAdmin SQL Dump
-- version 5.1.1deb3+bionic1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 05, 2021 at 08:33 PM
-- Server version: 5.7.35-0ubuntu0.18.04.1
-- PHP Version: 7.4.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `permitmemass V5.0`
--

DELIMITER $$
--
-- Procedures
--
CREATE PROCEDURE `after_iotdata_insert` (IN `inDevice` VARCHAR(255), IN `updateValue` INTEGER, IN `curr_date` TIMESTAMP)  BEGIN
	/*
	Simple truth table approach 
	if pulse rate is high - value = 1
	if spo2 is low - value = 2
	if temp is high - value = 4
	updateValue gets the value based on the above parameters so we can set the proper column to be updated 
	*/
        IF(
        SELECT
            COUNT(*)
        FROM
            iotdatasummary
        WHERE
            device = inDevice AND CAST(fordate AS DATE) = CAST(curr_date AS DATE)
    	) THEN
            BEGIN
                    #row already exists. needs to be updated
                    DECLARE uhighPulseRate INTEGER DEFAULT 0 ; 
                    DECLARE ulowSpo2 INTEGER DEFAULT 0 ; 
                    DECLARE uhighTemp INTEGER DEFAULT 0 ; 
                    DECLARE uhighPulseLowSpo2 INTEGER DEFAULT 0 ; 
                    DECLARE uhighPulseHighTemp INTEGER DEFAULT 0 ; 
                    DECLARE ulowSpo2HighTemp INTEGER DEFAULT 0 ; 
                    DECLARE uallNormal INTEGER DEFAULT 0 ; 
                    DECLARE uallAbnormal INTEGER DEFAULT 0 ;

				SELECT
                    highpulserate, lowspo2, hightemp, highpulseratelowspo2, highpulseratehightemp, lowspo2hightemp, allnormal,  allabnormal 
                    INTO uhighPulseRate , ulowSpo2 , uhighTemp , uhighPulseLowSpo2 , uhighPulseHighTemp ,ulowSpo2HighTemp ,uallNormal , uallAbnormal
                FROM
                    iotdatasummary
                WHERE
                    device = inDevice AND CAST(fordate AS DATE) = CAST(curr_date AS DATE) ;

                IF updateValue = 0 THEN
                        UPDATE iotdatasummary SET allnormal =(uallNormal + 1)
							WHERE device = inDevice AND CAST(fordate AS DATE) = CAST(curr_date AS DATE);
				ELSEIF updateValue = 1 THEN
                        UPDATE iotdatasummary SET highpulserate =(uhighPulseRate + 1)
							WHERE device = inDevice AND CAST(fordate AS DATE) = CAST(curr_date AS DATE);
				ELSEIF updateValue = 2 THEN
                        UPDATE iotdatasummary SET lowspo2 =(ulowSpo2 + 1)
							WHERE device = inDevice AND CAST(fordate AS DATE) = CAST(curr_date AS DATE);
				ELSEIF updateValue = 4 THEN
                        UPDATE iotdatasummary SET hightemp =(uhighTemp + 1)
							WHERE device = inDevice AND CAST(fordate AS DATE) = CAST(curr_date AS DATE);
				ELSEIF updateValue = 3 THEN
                        UPDATE iotdatasummary SET highpulseratelowspo2 =(uhighPulseLowSpo2 + 1)
							WHERE device = inDevice AND CAST(fordate AS DATE) = CAST(curr_date AS DATE);
				ELSEIF updateValue = 5 THEN
                        UPDATE iotdatasummary SET highpulseratehightemp =(uhighPulseHighTemp + 1)
							WHERE device = inDevice AND CAST(fordate AS DATE) = CAST(curr_date AS DATE);
				ELSEIF updateValue = 6 THEN 
						UPDATE iotdatasummary SET lowspo2hightemp =(ulowSpo2HighTemp + 1)
							WHERE device = inDevice AND CAST(fordate AS DATE) = CAST(curr_date AS DATE);
				ELSEIF updateValue = 7 THEN
                        UPDATE iotdatasummary SET allabnormal =(uallAbnormal + 1)
                        WHERE device = inDevice AND CAST(fordate AS DATE) = CAST(curr_date AS DATE);
                END IF;
			END; 
		ELSE
			BEGIN
        # there are no records in this table. lets add a new row
        		IF updateValue = 0 THEN
					INSERT INTO iotdatasummary(device, fordate, allnormal)
						VALUES(inDevice, CAST(curr_date AS DATE), 1); 
				ELSEIF updateValue = 1 THEN
					INSERT INTO iotdatasummary(device, fordate, highpulserate)
						VALUES(inDevice, CAST(curr_date AS DATE), 1); 
				ELSEIF updateValue = 2 THEN
					INSERT INTO iotdatasummary(device, fordate, lowspo2)
						VALUES( inDevice, CAST(curr_date AS DATE), 1); 
				ELSEIF updateValue = 4 THEN
					INSERT INTO iotdatasummary(device, fordate, hightemp)
						VALUES(inDevice, CAST(curr_date AS DATE), 1); 
				ELSEIF updateValue = 3 THEN
					INSERT INTO iotdatasummary(device, fordate, highpulseratelowspo2)
						VALUES(inDevice, CAST(curr_date AS DATE), 1); 
				ELSEIF updateValue = 5 THEN
					INSERT INTO iotdatasummary(device, fordate, highpulseratehightemp)
						VALUES(inDevice, CAST(curr_date AS DATE), 1); 
				ELSEIF updateValue = 6 THEN
					INSERT INTO iotdatasummary(device, fordate, lowspo2hightemp)
						VALUES(inDevice, CAST(curr_date AS DATE), 1); 
				ELSEIF updateValue = 7 THEN
					INSERT INTO iotdatasummary(device, fordate, allabnormal)
						VALUES(inDevice,CAST(curr_date AS DATE), 1);
    			END IF;
		END;
	END IF;
END$$

CREATE PROCEDURE `getCountByCity` (IN `inVal` VARCHAR(255))  BEGIN
		        declare done int default false;
                declare qVal int ;
                declare curForLoop cursor for 
			        select location.id from location 
				        where location.city = inVal;
		        declare continue handler for not found set done = true;
                
                open curForLoop;        
			        read_loop: loop
				        fetch curForLoop into qVal;
                        if done then
					        leave read_loop;
				        end if;
                        select l.pincode, l.id, ifnull(count(*),0) as cnt from plocation as l 
					        inner join LinkLocDev lld 
						        on l.id = lld.locationid
					        inner join device as d
						        on lld.deviceid = d.id
					        inner join iotdata as i 
						        on d.serial_no = i.deviceid
					        where l.isactive = true and lld.isactive = true and d.isactive = true and l.id = qVal
					        group by l.pincode, l.id;
			        end loop;
                close curForLoop;
				        
		        
            END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `bedMaster`
--

CREATE TABLE `bedMaster` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bedNo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `locationId` bigint(20) UNSIGNED DEFAULT NULL,
  `isactive` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bedMaster`
--

INSERT INTO `bedMaster` (`id`, `bedNo`, `locationId`, `isactive`, `created_at`, `updated_at`) VALUES
(1, 'A-01', 5, 1, '2020-10-11 11:24:41', '2020-10-11 11:24:41'),
(2, 'A-02', 5, 1, '2020-10-11 11:26:01', '2020-10-11 11:26:01'),
(3, 'A-03', 5, 1, '2020-10-11 11:26:06', '2020-10-11 15:45:42'),
(4, 'A-04', 5, 1, '2020-10-11 15:45:50', '2020-10-11 15:45:50'),
(5, 'A-05', 5, 1, '2020-10-11 15:45:57', '2020-10-11 15:45:57'),
(6, 'A-06', 5, 1, '2020-10-11 15:46:04', '2020-10-11 15:46:04'),
(7, 'A-07', 5, 1, '2020-10-11 15:46:11', '2020-10-11 15:46:11'),
(8, 'A-08', 5, 1, '2020-10-11 15:46:19', '2020-10-11 15:46:19'),
(9, 'A-09', 5, 1, '2020-10-11 15:46:26', '2020-10-11 15:46:26'),
(10, 'A-10', 5, 1, '2020-10-11 15:46:33', '2020-10-11 15:46:33'),
(11, 'A-11', 5, 1, '2020-11-07 15:00:01', '2020-11-07 15:00:01');

-- --------------------------------------------------------

--
-- Table structure for table `billplan`
--

CREATE TABLE `billplan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NO NAME',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secdeposit` int(11) NOT NULL DEFAULT '0',
  `rentpermonth` int(11) NOT NULL DEFAULT '0',
  `transactionrate` decimal(8,2) NOT NULL DEFAULT '1.00',
  `hostingcharges` int(11) NOT NULL DEFAULT '0',
  `hardwareamcrate` decimal(8,2) NOT NULL DEFAULT '20.00',
  `softwareamcrate` decimal(8,2) NOT NULL DEFAULT '20.00',
  `trainingcost` int(11) NOT NULL DEFAULT '0',
  `installationandsetupcost` int(11) NOT NULL DEFAULT '0',
  `isactive` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `billplan`
--

INSERT INTO `billplan` (`id`, `name`, `description`, `secdeposit`, `rentpermonth`, `transactionrate`, `hostingcharges`, `hardwareamcrate`, `softwareamcrate`, `trainingcost`, `installationandsetupcost`, `isactive`, `created_at`, `updated_at`) VALUES
(1, 'Test Plan', 'This is a test plan', 15000, 750, '0.30', 10000, '20.00', '20.00', 2000, 1500, 1, '2021-06-03 18:33:16', '2021-06-21 06:34:07');

-- --------------------------------------------------------

--
-- Table structure for table `devauth`
--

CREATE TABLE `devauth` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `deviceid` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `devupdated` tinyint(1) NOT NULL,
  `isactive` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `devauth`
--

INSERT INTO `devauth` (`id`, `deviceid`, `token`, `devupdated`, `isactive`, `created_at`, `updated_at`) VALUES
(1, 'DEVTEST14', 'JM8c6zyxVPJNkA93', 1, 0, '2021-05-15 00:30:38', '2021-05-15 00:44:32'),
(2, 'DEVKODEMO10', 'Gicef2mI5kCE9dzJ', 1, 0, '2021-05-15 00:45:25', '2021-05-15 00:47:15'),
(3, 'DEVTEST14', 'sktyPbf97asmkpIs', 1, 0, '2021-05-15 00:47:15', '2021-05-15 00:53:58'),
(4, 'DEVTEST14', 'wRE3ztT35NS0R1Kd', 1, 1, '2021-05-15 00:53:58', '2021-05-15 01:02:07'),
(5, 'DEVKODEMO11', 'u8zFezcLYivLwAry', 1, 0, '2021-05-18 06:57:20', '2021-05-18 06:57:52'),
(6, 'DEVKODEMO11', 'v3n3G69a3NXlA1vU', 0, 1, '2021-05-18 06:57:52', '2021-05-18 06:57:52'),
(7, 'DEVKODEMO11', 'hd0UioKZx6aUA7Zb', 1, 0, '2021-05-18 06:58:55', '2021-05-18 07:01:07'),
(8, 'DEVKODEMO11', 'uX7T2xmjQsQ6GznZ', 1, 0, '2021-05-18 07:01:07', '2021-05-18 07:57:33'),
(9, 'DEVKODEMO11', 'lmF5sfE5rvxFPgHt', 0, 1, '2021-05-18 07:57:33', '2021-05-18 07:57:33'),
(10, 'DEVKODEMO11', 'tZCBE31JXN3Lfs94', 1, 0, '2021-05-18 07:58:36', '2021-05-18 07:59:05'),
(11, 'DEVKODEMO11', 'PmNOisbllTO4bQ7s', 1, 0, '2021-05-18 07:59:05', '2021-05-24 17:08:12'),
(12, 'DEVKODEMO11', '5x5ka7kNC4EQS7MK', 0, 1, '2021-05-24 17:03:27', '2021-05-24 17:03:27'),
(13, 'DEVKODEMO11', 'BqCSaFa8xQJ8fUbt', 1, 0, '2021-05-24 17:54:20', '2021-05-24 17:59:10'),
(14, 'DEVKODEMO11', '5LDHgW7JYrP8lyU0', 1, 0, '2021-05-24 17:59:10', '2021-05-24 18:00:02'),
(15, 'DEVKODEMO11', 'jP2kdECosFpSMyqX', 1, 1, '2021-05-24 18:00:02', '2021-05-24 18:00:02'),
(16, 'DEVKODEMO11', 'iJBzV3Un4XqVHO61', 1, 0, '2021-05-24 18:01:01', '2021-05-24 18:05:34'),
(17, 'DEVKODEMO11', 'hjKoJp76oEQZLca8', 1, 0, '2021-05-24 18:05:34', '2021-05-24 18:10:02'),
(18, 'DEVKODEMO11', 'J3JZh0CnQHZIgPht', 1, 1, '2021-05-24 18:10:02', '2021-05-24 18:10:02'),
(19, 'DEVKODEMO11', 'n9lONZwVD8VCL8Y7', 1, 0, '2021-05-24 18:14:06', '2021-05-24 18:14:58'),
(20, 'DEVKODEMO11', 'mxAR5wOKqs4DXhWr', 1, 0, '2021-05-24 18:14:58', '2021-07-23 10:07:25'),
(21, 'DEVKODEMO11', '26EAUpsdBWIvHgyC', 0, 1, '2021-07-23 10:07:25', '2021-07-23 10:07:25'),
(22, 'DEVKODEMO11', 'vOM4nmCwYNAb8CUB', 0, 1, '2021-07-23 10:08:53', '2021-07-23 10:08:53');

-- --------------------------------------------------------

--
-- Table structure for table `device`
--

CREATE TABLE `device` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `serial_no` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `devtype` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'KEYBOARD',
  `isactive` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `device`
--

INSERT INTO `device` (`id`, `serial_no`, `devtype`, `isactive`, `created_at`, `updated_at`) VALUES
(1, 'DEVICE0', 'KEYBOARD', 0, '2020-07-31 12:24:05', '2020-07-31 12:24:05'),
(2, 'DEVICE1', 'KEYBOARD', 1, '2020-07-31 12:24:05', '2020-07-31 12:24:05'),
(3, 'DEVICE2', 'KEYBOARD', 1, '2020-07-31 12:24:05', '2020-07-31 12:24:05'),
(4, 'DEVICE3', 'KEYBOARD', 1, '2020-07-31 12:24:05', '2020-07-31 12:24:05'),
(5, 'DEVICE4', 'KEYBOARD', 0, '2020-07-31 12:24:05', '2020-07-31 12:24:05'),
(6, 'DEVICE06', 'KEYBOARD', 1, '2020-07-31 17:49:56', '2020-07-31 17:49:56'),
(7, 'DEVICE7', 'KEYBOARD', 0, '2020-07-31 17:50:07', '2021-05-01 13:41:46'),
(8, 'DEVICE08', 'KEYBOARD', 1, '2020-08-08 20:47:22', '2020-08-08 20:50:52'),
(9, 'DEVICEHR01', 'KEYBOARD', 1, '2020-08-10 07:01:19', '2020-08-10 07:01:19'),
(10, 'DEVTEST01', 'KEYBOARD', 1, '2020-08-23 18:44:10', '2020-08-23 18:44:10'),
(11, 'DEVTEST02', 'KEYBOARD', 1, '2020-08-23 18:44:40', '2020-08-23 18:44:40'),
(12, 'DEVTEST03', 'KEYBOARD', 1, '2020-08-23 18:44:50', '2020-08-23 18:44:50'),
(13, 'DEVTEST04', 'KEYBOARD', 1, '2020-08-23 18:45:00', '2020-08-23 18:45:00'),
(14, 'DEVTEST05', 'KEYBOARD', 1, '2020-08-23 18:45:10', '2020-08-23 18:45:10'),
(15, 'DEVTEST06', 'KEYBOARD', 1, '2020-08-23 18:45:23', '2020-08-23 18:45:23'),
(16, 'DEVTEST07', 'KEYBOARD', 1, '2020-08-23 18:45:34', '2020-08-23 18:45:34'),
(17, 'DEVTEST08', 'KEYBOARD', 1, '2020-08-23 18:45:44', '2020-08-23 18:45:44'),
(18, 'DEVTEST09', 'KEYBOARD', 1, '2020-08-23 18:45:52', '2020-08-23 18:45:52'),
(19, 'DEVTEST10', 'KEYBOARD', 1, '2020-08-23 18:46:01', '2020-08-23 18:46:01'),
(20, 'DEVTEST11', 'KEYBOARD', 1, '2020-08-23 18:46:13', '2020-08-23 18:46:13'),
(21, 'DEVTEST12', 'KEYBOARD', 1, '2020-08-23 18:46:22', '2020-08-23 18:46:22'),
(22, 'DEVTEST13', 'KEYBOARD', 1, '2020-08-23 18:46:34', '2020-08-23 18:46:34'),
(23, 'DEVTEST14', 'KEYBOARD', 1, '2020-08-23 18:46:43', '2020-08-23 18:46:43'),
(24, 'DEVTEST15', 'KEYBOARD', 1, '2020-08-23 18:46:52', '2020-08-23 18:46:52'),
(25, 'DEVTEST16', 'KEYBOARD', 1, '2020-08-23 18:47:01', '2020-08-23 18:47:01'),
(26, 'DEVTEST17', 'KEYBOARD', 1, '2020-08-23 18:47:09', '2020-08-23 18:47:09'),
(27, 'DEVTEST18', 'KEYBOARD', 1, '2020-08-23 18:47:18', '2020-08-23 18:47:18'),
(28, 'DEVTEST19', 'KEYBOARD', 1, '2020-08-23 18:47:26', '2020-08-23 18:47:26'),
(29, 'DEVTEST20', 'KEYBOARD', 1, '2020-08-23 18:47:34', '2020-08-23 18:47:34'),
(30, 'DEVTEST21', 'KEYBOARD', 1, '2020-08-23 18:47:43', '2020-08-23 18:47:43'),
(31, 'DEVTEST22', 'KEYBOARD', 1, '2020-08-23 18:47:53', '2020-08-23 18:47:53'),
(32, 'DEVTEST23', 'KEYBOARD', 1, '2020-08-23 18:48:04', '2020-08-23 18:48:04'),
(33, 'DEVTEST24', 'KEYBOARD', 1, '2020-08-23 18:48:13', '2020-08-23 18:48:13'),
(34, 'DEVTEST25', 'KEYBOARD', 1, '2020-08-23 18:48:24', '2020-08-23 18:48:24'),
(35, 'DEVTEST26', 'KEYBOARD', 1, '2020-08-23 18:48:34', '2020-08-23 18:48:34'),
(36, 'DEVTEST26', 'KEYBOARD', 0, '2020-08-23 18:48:45', '2020-08-23 18:49:50'),
(37, 'DEVTEST27', 'KEYBOARD', 1, '2020-08-23 18:48:57', '2020-08-23 18:48:57'),
(38, 'DEVTEST28', 'KEYBOARD', 1, '2020-08-23 18:49:05', '2020-08-23 18:49:05'),
(39, 'DEVTEST29', 'KEYBOARD', 1, '2020-08-23 18:49:14', '2020-08-23 18:49:14'),
(40, 'DEVTEST30', 'KEYBOARD', 1, '2020-08-23 18:49:28', '2020-08-23 18:49:28'),
(41, 'DEVKODEMO1', 'KEYBOARD', 1, '2020-09-09 00:14:53', '2020-09-09 00:14:53'),
(42, 'DEVKODEMO2', 'KEYBOARD', 1, '2020-09-09 09:10:56', '2020-09-09 09:10:56'),
(43, 'DEV-KO-001', 'KEYBOARD', 1, '2020-09-09 10:27:29', '2020-09-09 10:27:29'),
(44, 'DEVKODEMO3', 'KEYBOARD', 1, '2020-09-09 12:03:09', '2020-09-09 12:03:09'),
(45, 'DEVKODEMO4', 'KEYBOARD', 1, '2020-09-09 15:54:54', '2020-09-09 15:54:54'),
(46, 'DEVKODEMO5', 'KEYBOARD', 1, '2020-09-18 13:29:10', '2020-09-18 13:29:10'),
(47, 'DEVKODEMO9', 'KEYBOARD', 1, '2020-09-19 11:20:21', '2021-03-08 23:37:29'),
(48, 'DEVKODEMO6', 'KEYBOARD', 1, '2020-09-22 12:15:41', '2020-09-22 12:15:41'),
(49, 'DEVKODEMO7', 'KEYBOARD', 1, '2020-09-25 12:49:27', '2020-09-25 12:49:27'),
(50, 'DEVKODEMO8', 'KEYBOARD', 1, '2020-09-25 12:49:40', '2020-09-25 12:49:40'),
(51, 'DEV-KO-002', 'KEYBOARD', 1, '2020-10-14 15:17:24', '2020-10-14 15:17:24'),
(52, 'DEVKODEMO14', 'KEYBOARD', 1, '2020-10-22 14:13:45', '2020-12-25 10:01:34'),
(53, 'dupgrade12', 'KEYBOARD', 0, '2020-11-04 23:04:19', '2021-05-12 10:19:17'),
(54, 'DEVKODEMO12', 'KEYBOARD', 1, '2020-11-07 15:09:14', '2020-11-07 15:09:14'),
(55, 'DEVKODEMO10', 'KEYBOARD', 1, '2020-12-09 12:42:08', '2020-12-09 12:42:08'),
(56, 'DEVKODEMO13', 'KEYBOARD', 1, '2020-12-10 12:32:43', '2020-12-10 12:32:43'),
(57, 'DEVKODEMO15', 'KEYBOARD', 1, '2020-12-31 14:20:02', '2020-12-31 14:20:02'),
(58, 'DEVKODEMO11', 'OTHER', 1, '2021-01-13 16:15:12', '2021-01-13 16:15:12'),
(59, 'TESTDEVTYPE', 'KEYBOARD', 1, '2021-05-01 14:43:05', '2021-05-01 14:43:05'),
(60, 'TESTDEVTYPE2', 'RFID', 1, '2021-05-01 14:43:39', '2021-05-01 14:52:30'),
(61, 'devpermtest', 'KEYBOARD', 0, '2021-05-12 10:19:47', '2021-05-12 10:19:47');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `iotdata`
--

CREATE TABLE `iotdata` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `identifier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deviceid` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `temp` double(8,2) NOT NULL,
  `spo2` int(11) NOT NULL,
  `hbcount` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `flagstatus` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `iotdata`
--



-- --------------------------------------------------------

--
-- Table structure for table `iotdatasummary`
--

CREATE TABLE `iotdatasummary` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `device` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fordate` date NOT NULL,
  `highpulserate` int(11) NOT NULL DEFAULT '0',
  `lowspo2` int(11) NOT NULL DEFAULT '0',
  `hightemp` int(11) NOT NULL DEFAULT '0',
  `highpulseratelowspo2` int(11) NOT NULL DEFAULT '0',
  `highpulseratehightemp` int(11) NOT NULL DEFAULT '0',
  `lowspo2hightemp` int(11) NOT NULL DEFAULT '0',
  `allabnormal` int(11) NOT NULL DEFAULT '0',
  `allnormal` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `iotdatasummary`
--



-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `linkHospitalBedUser`
--

CREATE TABLE `linkHospitalBedUser` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `locationId` bigint(20) UNSIGNED NOT NULL,
  `bedId` bigint(20) UNSIGNED NOT NULL,
  `patientId` bigint(20) UNSIGNED NOT NULL,
  `isactive` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `linkHospitalBedUser`
--

INSERT INTO `linkHospitalBedUser` (`id`, `locationId`, `bedId`, `patientId`, `isactive`, `created_at`, `updated_at`) VALUES
(1, 5, 1, 15, 0, '2020-10-11 12:41:41', '2020-10-11 15:45:28'),
(2, 5, 2, 9, 0, '2020-10-11 12:44:26', '2020-10-11 14:33:29'),
(3, 5, 2, 9, 0, '2020-10-11 14:33:45', '2020-10-11 15:45:33'),
(4, 5, 1, 5, 0, '2020-10-11 15:46:46', '2020-10-13 07:59:13'),
(5, 5, 2, 7, 1, '2020-10-11 15:46:54', '2020-10-11 15:46:54'),
(6, 5, 3, 8, 1, '2020-10-11 15:47:04', '2020-10-11 15:47:04'),
(7, 5, 4, 10, 1, '2020-10-11 15:47:12', '2020-10-11 15:47:12'),
(8, 5, 5, 9, 1, '2020-10-11 15:47:21', '2020-10-11 15:47:21'),
(9, 5, 6, 6, 1, '2020-10-11 15:47:33', '2020-10-11 15:47:33'),
(10, 5, 7, 4, 1, '2020-10-11 15:47:44', '2020-10-11 15:47:44'),
(11, 5, 1, 5, 1, '2020-10-13 07:59:29', '2020-10-13 07:59:29'),
(12, 5, 11, 16, 1, '2020-11-07 15:00:58', '2020-11-07 15:00:58');

-- --------------------------------------------------------

--
-- Table structure for table `LinkLocDev`
--

CREATE TABLE `LinkLocDev` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `locationid` bigint(20) UNSIGNED NOT NULL,
  `deviceid` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Main Door',
  `isactive` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `LinkLocDev`
--

INSERT INTO `LinkLocDev` (`id`, `locationid`, `deviceid`, `name`, `isactive`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Main Door', 1, '2020-07-31 19:49:07', '2020-07-31 20:12:03'),
(2, 1, 2, 'Main Door', 1, '2020-08-08 18:54:28', '2020-08-08 18:54:28'),
(3, 4, 3, 'Main Door', 1, '2020-08-09 09:58:37', '2020-08-09 09:58:37'),
(4, 5, 8, 'Main Door', 1, '2020-08-09 18:06:54', '2020-08-09 18:06:54'),
(5, 7, 6, 'Main Door', 1, '2020-08-09 22:41:37', '2020-08-09 22:41:54'),
(6, 8, 9, 'Main Door', 1, '2020-08-10 07:07:18', '2020-08-10 07:07:18'),
(7, 16, 10, 'Main Door', 1, '2020-08-23 18:59:32', '2020-08-23 18:59:32'),
(8, 14, 11, 'Main Door', 1, '2020-08-23 18:59:45', '2020-08-23 18:59:45'),
(9, 15, 12, 'Main Door', 1, '2020-08-23 18:59:58', '2020-08-23 18:59:58'),
(10, 13, 13, 'Main Door', 1, '2020-08-23 19:00:16', '2020-08-23 19:00:16'),
(11, 11, 14, 'Main Door', 1, '2020-08-23 19:00:31', '2020-08-23 19:00:31'),
(12, 9, 15, 'Main Door', 1, '2020-08-23 19:00:43', '2020-08-23 19:00:43'),
(13, 12, 16, 'Main Door', 1, '2020-08-23 19:00:59', '2020-08-23 19:00:59'),
(14, 10, 17, 'Main Door', 1, '2020-08-23 19:01:15', '2020-08-23 19:01:15'),
(15, 17, 18, 'Main Door', 1, '2020-08-23 19:06:22', '2020-08-23 19:06:22'),
(16, 18, 19, 'Main Door', 1, '2020-08-23 19:07:56', '2020-08-23 19:07:56'),
(17, 19, 20, 'Main Door', 1, '2020-08-23 19:09:28', '2020-08-23 19:09:28'),
(18, 20, 21, 'Main Door', 1, '2020-08-23 19:10:45', '2020-08-23 19:10:45'),
(19, 21, 22, 'Main Door', 1, '2020-08-23 19:11:45', '2020-08-23 19:11:45'),
(20, 22, 23, 'Main Door', 1, '2020-08-23 19:13:08', '2020-08-23 19:13:08'),
(21, 23, 24, 'Main Door', 1, '2020-08-23 19:14:51', '2020-08-23 19:14:51'),
(22, 24, 25, 'Main Door', 1, '2020-08-23 19:20:15', '2020-08-23 19:20:15'),
(23, 25, 26, 'Main Door', 1, '2020-08-23 19:21:18', '2020-08-23 19:21:18'),
(24, 26, 27, 'Main Door', 1, '2020-08-23 19:22:21', '2020-08-23 19:22:21'),
(26, 28, 29, 'Main Door 2', 1, '2020-08-23 19:24:50', '2020-08-23 19:24:50'),
(27, 8, 30, 'Main Door', 1, '2020-08-23 19:26:05', '2020-08-23 19:26:05'),
(28, 5, 31, 'Main Door', 1, '2020-08-23 19:26:20', '2020-08-23 19:26:20'),
(29, 6, 32, 'Main Door', 1, '2020-08-23 19:26:38', '2020-08-23 19:26:38'),
(30, 4, 33, 'Main Door', 1, '2020-08-23 19:26:56', '2020-08-23 19:26:56'),
(31, 1, 34, 'Main Door', 1, '2020-08-23 19:27:10', '2020-08-23 19:27:10'),
(32, 29, 41, 'Main Door', 1, '2020-09-09 00:15:22', '2020-09-09 00:15:22'),
(33, 30, 42, 'Main Door', 1, '2020-09-09 09:11:19', '2020-09-09 09:11:19'),
(34, 5, 43, 'Main Door', 1, '2020-09-09 10:27:43', '2020-09-09 10:27:43'),
(35, 29, 44, 'Main Door', 1, '2020-09-09 12:04:14', '2020-12-25 10:03:41'),
(36, 5, 45, 'Main Door', 1, '2020-09-09 15:56:03', '2020-09-09 15:56:03'),
(37, 5, 46, 'Main Door', 1, '2020-09-18 13:32:06', '2021-01-20 13:35:53'),
(39, 6, 48, 'Main Door', 1, '2020-09-22 12:15:55', '2020-09-22 12:15:55'),
(41, 5, 51, 'Main Door', 1, '2020-10-14 15:17:53', '2020-10-14 15:17:53'),
(42, 31, 49, 'Main Door', 1, '2020-10-29 10:43:10', '2020-10-29 10:43:10'),
(43, 29, 52, 'Main Door', 1, '2020-11-05 16:21:58', '2020-12-25 10:02:50'),
(45, 29, 50, 'Main Door', 1, '2020-11-09 17:26:00', '2020-11-09 17:26:00'),
(46, 5, 54, 'Main Door', 1, '2020-12-08 17:39:43', '2020-12-08 17:39:43'),
(47, 5, 55, 'Main Door', 1, '2020-12-09 12:42:50', '2020-12-09 12:42:50'),
(49, 5, 56, 'Main Door', 1, '2020-12-16 13:25:00', '2020-12-16 13:25:00'),
(50, 5, 47, 'Main Door', 1, '2020-12-25 11:51:56', '2020-12-25 11:51:56'),
(52, 32, 57, 'Main Door', 1, '2021-01-11 14:41:26', '2021-01-11 14:41:26'),
(53, 29, 58, 'Main Door', 1, '2021-01-13 16:15:46', '2021-01-13 16:15:46'),
(54, 22, 28, 'Main Door 2', 1, '2021-04-30 11:47:12', '2021-04-30 11:47:12'),
(55, 34, 60, 'Test location 1', 1, '2021-05-01 15:02:01', '2021-05-01 15:07:32');

-- --------------------------------------------------------

--
-- Table structure for table `linklocusers`
--

CREATE TABLE `linklocusers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `locationid` bigint(20) UNSIGNED NOT NULL,
  `userid` bigint(20) UNSIGNED NOT NULL,
  `designation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phoneno1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isactive` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `linklocusers`
--

INSERT INTO `linklocusers` (`id`, `locationid`, `userid`, `designation`, `phoneno1`, `isactive`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Secretary', '7718865005', 1, '2020-08-02 11:06:00', '2020-08-02 11:39:37'),
(2, 1, 2, 'Chairman', '9999999999', 1, '2020-08-09 09:59:29', '2020-08-09 09:59:29'),
(3, 5, 3, 'Chairman', '7718865005', 1, '2020-08-09 17:52:17', '2020-08-09 17:52:17'),
(4, 7, 4, 'Treasurer', '9000000002', 1, '2020-08-09 22:36:02', '2020-08-09 22:36:02'),
(5, 8, 5, 'Secretary', '9920781626', 1, '2020-08-10 07:09:29', '2020-08-10 07:09:29'),
(6, 5, 6, 'Other', '8983177587', 1, '2020-08-10 07:09:29', '2020-08-10 07:09:29'),
(7, 29, 8, 'Other', '9999911111', 1, '2020-09-09 08:34:25', '2020-09-09 08:34:25'),
(8, 5, 9, 'Other', '9000000001', 1, '2020-09-10 09:44:03', '2020-09-10 09:44:03'),
(9, 29, 10, 'Officer', '9833848283', 1, '2020-09-10 09:52:27', '2021-05-01 13:41:21'),
(10, 5, 11, 'Officer', '9822574956', 1, '2020-09-10 10:00:06', '2020-09-10 10:00:06'),
(11, 5, 12, 'HR', '7718865003', 1, '2020-09-17 10:40:50', '2020-09-17 10:40:50'),
(12, 30, 13, 'Management', '9820324306', 1, '2020-09-25 14:04:11', '2020-09-25 14:04:11'),
(13, 6, 14, 'Management', '9885315327', 1, '2020-10-03 15:25:07', '2020-10-03 15:25:07'),
(14, 31, 15, 'Management', '9920650618', 1, '2020-10-14 14:46:02', '2020-10-14 14:46:02'),
(15, 5, 16, 'Other', '8779126851', 1, '2020-11-25 21:42:33', '2020-11-25 21:42:33'),
(16, 22, 17, 'Other', '9284745738', 1, '2020-12-20 14:52:55', '2020-12-20 14:52:55'),
(17, 29, 18, 'Chairman', '1234567890', 1, '2020-12-25 10:10:57', '2020-12-25 10:10:57');

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `noofresidents` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pincode` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `landmark` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NOT KNOWN',
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taluka` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isactive` tinyint(1) NOT NULL,
  `parent` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `smsnotification` tinyint(1) NOT NULL DEFAULT '0',
  `latitude` decimal(7,4) NOT NULL DEFAULT '0.0000',
  `longitude` decimal(7,4) NOT NULL DEFAULT '0.0000',
  `altitude` decimal(7,4) NOT NULL DEFAULT '0.0000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`id`, `name`, `noofresidents`, `address1`, `address2`, `pincode`, `landmark`, `city`, `taluka`, `district`, `state`, `isactive`, `parent`, `created_at`, `updated_at`, `smsnotification`, `latitude`, `longitude`, `altitude`) VALUES
(1, 'Vidarbh Mahesh CHS', '85', 'Coral Heights Compound,', 'G. B. Road, Thane West', '400615', 'NOT KNOWN', 'Thane', 'Thane', 'Thane', 'Maharashtra', 1, 0, '2020-07-31 18:12:26', '2020-07-31 18:12:26', 0, '0.0000', '0.0000', '0.0000'),
(4, 'Swastik Palms', '1000', 'Brahmand', 'Phase 6', '400607', 'NOT KNOWN', 'Thane', 'Thane', 'Thane', 'Maharashtra', 1, 0, '2020-07-31 18:26:52', '2020-10-13 17:30:48', 0, '0.0000', '0.0000', '0.0000'),
(5, 'Ko-Aaham Technologies LLP', '20', 'Bhumi World Industrial Estate', 'Pimplas Village', '421302', 'NOT KNOWN', 'Thane', 'Thane', 'Thane', 'Maharashtra', 1, 0, '2020-08-09 17:47:29', '2020-11-07 16:08:29', 0, '0.0000', '0.0000', '0.0000'),
(6, 'Realtime Engineering Systems', '30', 'Masabthank', 'Banjara Hills', '500028', 'NOT KNOWN', 'Hyderabad', 'Hyderabad', 'Hyderabad', 'Telangana', 1, 29, '2020-08-09 17:49:54', '2020-10-13 17:31:01', 0, '0.0000', '0.0000', '0.0000'),
(7, 'Test Location', '10', 'test address 1', 'test address 2', '400001', 'NOT KNOWN', 'Mumbai', 'Mumbai', 'Mumbai', 'Maharashtra', 1, 29, '2020-08-09 17:51:29', '2020-08-09 17:51:29', 0, '0.0000', '0.0000', '0.0000'),
(8, 'Hiranandani Estate', '15000', 'Patlipada', 'G. B. Road, Thane West', '400607', 'NOT KNOWN', 'Thane', 'Thane', 'Thane', 'Maharashtra', 1, 0, '2020-08-10 07:00:32', '2020-08-10 07:00:32', 0, '0.0000', '0.0000', '0.0000'),
(9, 'Shivani Textiles', '200', 'Rajive Gandhi Road', 'Adhalgaon', '413701', 'NOT KNOWN', 'Ahmednagar', 'Adhalgaon', 'Ahmednagar', 'Maharashtra', 1, 29, '2020-08-23 18:34:27', '2020-10-13 17:30:30', 0, '0.0000', '0.0000', '0.0000'),
(10, 'Vijaya Printers', '20', 'M. G. Road', 'Ahmednagar City', '414001', 'NOT KNOWN', 'Ahmednagar', 'Ahmednagar City', 'Ahmednagar', 'Maharashtra', 1, 29, '2020-08-23 18:35:32', '2020-08-23 18:35:32', 0, '0.0000', '0.0000', '0.0000'),
(11, 'Ritika Collections', '30', '1089', 'Tree Top Lane', '414701', 'NOT KNOWN', 'Ajnuj', 'Ajnuj', 'Ahmednagar', 'Maharashtra', 1, 29, '2020-08-23 18:36:34', '2020-08-23 18:36:34', 0, '0.0000', '0.0000', '0.0000'),
(12, 'Vijay Vilas Residency', '200', 'Savarkar Road', 'Akola', '414102', 'NOT KNOWN', 'Akola', 'Akola', 'Ahmednagar', 'Maharashtra', 1, 29, '2020-08-23 18:37:20', '2020-08-23 18:37:20', 0, '0.0000', '0.0000', '0.0000'),
(13, 'Rajguru Pvt. Ltd.', '25', '2nd Floor,', 'M. K. Plaza', '413728', 'NOT KNOWN', 'Chikhali', 'Chikhali', 'Ahmednagar', 'Maharashtra', 1, 29, '2020-08-23 18:38:43', '2020-08-23 18:38:43', 0, '0.0000', '0.0000', '0.0000'),
(14, 'Mezzanine', '35', 'Mezzanine floor', 'Navratna D\'Mello Road', '424405', 'NOT KNOWN', 'Shirpur', 'Shirpur', 'Dhule', 'Maharashtra', 1, 29, '2020-08-23 18:40:32', '2020-08-23 18:40:32', 0, '0.0000', '0.0000', '0.0000'),
(15, 'Patel Ashwamegh', '40', 'Sahayaji Gunj', 'Dhule Market yard', '424004', 'NOT KNOWN', 'Dhule', 'Dhule Market yard', 'Dhule', 'Maharashtra', 1, 29, '2020-08-23 18:41:19', '2020-08-23 18:41:19', 0, '0.0000', '0.0000', '0.0000'),
(16, 'Coral Square', '50', '13/3', 'Opp Bata Showroom', '424001', 'NOT KNOWN', 'Devpur', 'Devpur', 'Dhule', 'Maharashtra', 1, 29, '2020-08-23 18:42:04', '2020-08-23 18:42:04', 0, '0.0000', '0.0000', '0.0000'),
(17, 'i-Phone Park', '45', '208 Regent Chanbers', 'Above Status Restaurant', '425406', 'NOT KNOWN', 'Shindkheda', 'Shindkheda', 'Dhule', 'Maharashtra', 1, 29, '2020-08-23 19:06:06', '2020-08-23 19:06:06', 0, '0.0000', '0.0000', '0.0000'),
(18, 'Piezers Park', '600', 'Above Shiva Hotel', 'Ram Nagar', '424304', 'NOT KNOWN', 'Sakri', 'Sakri', 'Dhule', 'Maharashtra', 1, 29, '2020-08-23 19:07:35', '2020-08-23 19:07:35', 0, '0.0000', '0.0000', '0.0000'),
(19, 'Kalandi Textile', '60', 'Shalimar Garden Road', 'S P', '416215', 'NOT KNOWN', 'Shahuwadi', 'Shahuwadi', 'Kolhapur', 'Maharashtra', 1, 29, '2020-08-23 19:09:15', '2020-08-23 19:09:15', 0, '0.0000', '0.0000', '0.0000'),
(20, 'Lakme Acadamy', '50', 'Shahibabad', 'M G Road', '416201', 'NOT KNOWN', 'Panahala', 'Panahala', 'Kolhapur', 'Maharashtra', 1, 29, '2020-08-23 19:10:31', '2020-08-23 19:10:31', 0, '0.0000', '0.0000', '0.0000'),
(21, 'Arunoday Publications', '54', '11th floor', 'G-Corp', '416103', 'NOT KNOWN', 'Shirol', 'Shirol', 'Kolhapur', 'Maharashtra', 1, 29, '2020-08-23 19:11:28', '2020-08-23 19:11:28', 0, '0.0000', '0.0000', '0.0000'),
(22, 'Mamata Maternity home', '40', 'Castelmills naka', 'Chitalsar', '416502', 'NOT KNOWN', 'Gadhinglaj', 'Gadhinglaj', 'Kolhapur', 'Maharashtra', 1, 5, '2020-08-23 19:12:51', '2020-08-23 19:12:51', 0, '0.0000', '0.0000', '0.0000'),
(23, 'Raheja Complex', '1500', 'M K Road', 'Opp Mac', '416212', 'NOT KNOWN', 'Radhanagari', 'Radhanagari', 'Kolhapur', 'Maharashtra', 1, 29, '2020-08-23 19:14:37', '2020-08-23 19:14:37', 0, '0.0000', '0.0000', '0.0000'),
(24, 'Reva Tech', '605', '2nd Floor', 'Shalimar Business Park', '400050', 'NOT KNOWN', 'Bandra', 'Mumbai', 'Mumbai', 'Maharashtra', 1, 29, '2020-08-23 19:19:32', '2020-08-23 19:19:32', 0, '0.0000', '0.0000', '0.0000'),
(25, 'Shiva prasad publications', '400', 'Thakar bappa road', 'Kurla', '400070', 'NOT KNOWN', 'Mumbai', 'Mumbai', 'Mumbai', 'Maharashtra', 1, 29, '2020-08-23 19:21:01', '2020-08-23 19:21:01', 0, '0.0000', '0.0000', '0.0000'),
(26, 'Manisha Garden', '200', 'Nr. Mulund Gymkhana', 'Mulund', '400081', 'NOT KNOWN', 'Mumbai', 'Mumbai', 'Mumbai', 'Maharashtra', 1, 29, '2020-08-23 19:22:08', '2020-08-23 19:22:08', 0, '0.0000', '0.0000', '0.0000'),
(27, 'Mafco Pvt. Ltd.', '100', 'Sahakarnagar', 'No 5, Chembur', '400071', 'NOT KNOWN', 'Mumbai', 'Mumbai', 'Mumbai', 'Maharashtra', 1, 29, '2020-08-23 19:23:12', '2020-08-23 19:23:12', 0, '0.0000', '0.0000', '0.0000'),
(28, 'Elegant Business Park', '250', 'Bh. Kohinoor Continental', 'Andheri', '400069', 'NOT KNOWN', 'Mumabi', 'Andheri', 'Mumbai', 'Maharashtra', 1, 29, '2020-08-23 19:24:38', '2020-08-23 19:24:38', 0, '0.0000', '0.0000', '0.0000'),
(29, 'Ko-Aahan Demo', '50', 'G B Road', 'Chandroday', '400607', 'NOT KNOWN', 'Thane', 'Thane', 'Thane', 'Maharashtra', 1, 0, '2020-09-08 14:20:07', '2020-09-22 19:41:31', 0, '0.0000', '0.0000', '0.0000'),
(30, 'Abacus Infotech', '10', '211, Blue Rose Industrial Premises, 2nd Floor, Above Maruti Showroom', 'Datta Pada, Opp. Western Express Highway, Borivali (East)', '400066', 'NOT KNOWN', 'Mumbai', 'Mumbai', 'Mumbai', 'Maharashtra', 1, 29, '2020-09-25 14:03:01', '2020-09-25 14:03:01', 0, '0.0000', '0.0000', '0.0000'),
(31, 'Shrinathji Temple', '500', 'Nathdwara', 'Nathdwara', '313301', 'NOT KNOWN', 'Nathdwara', 'Nathdwara', 'Nathdwara', 'Rajasthan', 1, 0, '2020-10-14 14:40:29', '2020-10-14 14:40:29', 0, '0.0000', '0.0000', '0.0000'),
(32, 'Carwala garage', '100', 'Shop 1 & 2', 'Near DSK Ranvala', '411021', 'NOT KNOWN', 'Bavdhan', 'Pune', 'Pune', 'Maharashtra', 1, 0, '2021-01-11 14:40:25', '2021-01-11 14:40:25', 0, '0.0000', '0.0000', '0.0000'),
(33, 'Test hierarchy locations', '20', 'test address line 1', 'test address line 2', '400001', 'NOT KNOWN', 'Mumbai', 'Mumbai', 'Mumbai', 'Maharashtra', 1, 0, '2021-05-01 11:31:29', '2021-05-01 11:31:29', 0, '0.0000', '0.0000', '0.0000'),
(34, 'Test GSP', '20', 'GPS', 'GPS2', '400615', 'NOT KNOWN', 'Thane', 'Thane', 'Thane', 'Maharashtra', 1, 0, '2021-05-01 12:38:09', '2021-05-01 13:27:04', 0, '19.2598', '72.9703', '10.0000');

-- --------------------------------------------------------

--
-- Table structure for table `locationbillplanlink`
--

CREATE TABLE `locationbillplanlink` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `locationid` bigint(20) UNSIGNED NOT NULL,
  `planid` bigint(20) UNSIGNED NOT NULL,
  `isactive` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `planstartdate` date DEFAULT NULL,
  `planenddate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locationbillplanlink`
--

INSERT INTO `locationbillplanlink` (`id`, `locationid`, `planid`, `isactive`, `created_at`, `updated_at`, `planstartdate`, `planenddate`) VALUES
(1, 34, 1, 1, '2021-06-07 15:16:30', '2021-06-21 06:58:04', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2020_07_31_170856_create_device_table', 2),
(7, '2020_07_31_215748_create_table_location', 3),
(9, '2020_08_01_002903_create_link_loc_devs_table', 4),
(10, '2020_08_02_155806_create_link_loc_users_table', 5),
(11, '2020_08_02_171156_create_iot_data_table', 6),
(12, '2020_08_02_171156_change_iot_data_table ', 7),
(13, '2020_08_06_191106_add_flag_to_iotdata', 8),
(14, '2020_08_09_004800_create_reg_users_table', 9),
(15, '2020_08_09_011310_add_location_to__reg_user', 10),
(16, '2020_08_09_192328_create_permission_tables', 11),
(17, '2020_09_07_162833_create_jobs_table', 12),
(18, '2020_09_07_165829_add_send_sms_to_location_table', 13),
(19, '2020_10_11_154801_add_aadhar_to_staff', 14),
(20, '2020_10_11_160951_add_hospital_user_link_table', 15),
(21, '2020_10_11_161435_add_bedmaster', 15),
(22, '2021_04_28_223702_add_parent_to_location', 16),
(24, '2021_04_30_161456_add_name_to__link_loc_dev', 17),
(25, '2021_05_01_171548_add_gsp_to_location', 18),
(27, '2021_05_01_191853_add_type_to_device', 19),
(29, '2021_05_01_204239_add_fields_to_reguser', 20),
(30, '2021_05_01_220451_add_vaccin_fields_to_reguser', 21),
(31, '2021_05_11_004235_create_devauth_table', 22),
(32, '2021_05_11_011946_add_column_to_devauth', 23),
(33, '2021_05_11_163753_add_column_to_location_table', 24),
(34, '2021_05_24_165412_create_table_iotdatasummary', 25),
(35, '2021_05_24_175827_add_column_to_iotdatasummary', 26),
(36, '2021_05_24_214229_add_defaults_to_iotdatasummary', 27),
(39, '2021_06_03_174054_create_table_billplan', 28),
(40, '2021_06_04_003309_create_locationbillplanlink', 29),
(41, '2021_07_27_152143_add_start_end_columns_to_locationbillplanlink', 30);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(3, 'App\\User', 1),
(2, 'App\\User', 2),
(1, 'App\\User', 3),
(3, 'App\\User', 4),
(1, 'App\\User', 5),
(1, 'App\\User', 6),
(3, 'App\\User', 7),
(2, 'App\\User', 10),
(2, 'App\\User', 11),
(3, 'App\\User', 12),
(3, 'App\\User', 13),
(2, 'App\\User', 14),
(3, 'App\\User', 15),
(1, 'App\\User', 16),
(3, 'App\\User', 17),
(4, 'App\\User', 17),
(1, 'App\\User', 18);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`email`, `token`, `created_at`) VALUES
('samyakdaware@gmail.com', '$2y$10$dbCUleqU3U9mA9ilHGbRje1g9hb8vyHRbD6TVx9uAjj3dJufR8ogm', '2021-01-12 17:11:50');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'device', 'web', '2020-08-09 14:18:38', '2020-08-09 14:42:32'),
(2, 'location', 'web', '2020-08-09 14:18:55', '2020-08-09 14:18:55'),
(3, 'permissions', 'web', '2020-08-09 14:19:08', '2020-08-09 14:19:08'),
(4, 'linkLocation', 'web', '2020-08-09 14:19:19', '2020-08-09 14:19:19'),
(5, 'linkUser', 'web', '2020-08-09 14:19:51', '2020-08-09 14:19:51'),
(6, 'registerUser', 'web', '2020-08-09 14:20:04', '2020-08-09 14:20:04'),
(7, 'reports', 'web', '2020-08-09 14:20:21', '2020-08-09 14:20:21');

-- --------------------------------------------------------

--
-- Table structure for table `reguser`
--

CREATE TABLE `reguser` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `phoneno` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `coverimage` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'noImage.jpg',
  `tagid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `resiarea` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'NOT KNOWN',
  `resilandmark` varchar(150) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'NOT KNOWN',
  `vaccinated` tinyint(1) NOT NULL DEFAULT '0',
  `firstvaccin` date DEFAULT NULL,
  `secondvaccin` date DEFAULT NULL,
  `isactive` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `locationid` bigint(20) UNSIGNED NOT NULL,
  `AadharNo` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `reguser`
--

INSERT INTO `reguser` (`id`, `name`, `phoneno`, `coverimage`, `tagid`, `resiarea`, `resilandmark`, `vaccinated`, `firstvaccin`, `secondvaccin`, `isactive`, `created_at`, `updated_at`, `locationid`, `AadharNo`) VALUES
(1, 'user 1', '1231231231', 'noImage_1596919254.jpg', 'AA BB CC DD', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-08-08 19:51:17', '2020-08-08 20:43:36', 1, NULL),
(2, 'user 2', '9999999991', 'noImage.jpg', 'AA BB CC AA', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-08-09 20:08:16', '2020-08-09 20:08:16', 1, NULL),
(3, 'Test User Office 1', '9999123456', 'noImage.jpg', 'C9 10 91 96', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-08-12 13:06:44', '2020-08-12 13:06:44', 1, NULL),
(4, 'Yogesh Borse', '8983177587', 'Ko-Aaham_1604742624.png', 'FA C4 C5 80', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-08-13 15:25:32', '2021-01-12 10:33:40', 5, NULL),
(5, 'Bharati Parab', '8451006992', 'noImage.jpg', '14 F6 FA 40', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-08-13 15:27:08', '2020-08-13 15:27:08', 5, NULL),
(6, 'Vikas kshirsagar', '9325468143', 'noImage.jpg', 'AC 8D 12 0F', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-08-13 15:28:20', '2021-02-27 17:32:22', 5, NULL),
(7, 'Anup shekokar', '9321396256', 'noImage.jpg', 'DA F2 32 47', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-08-13 15:29:12', '2021-01-28 13:02:39', 5, NULL),
(8, 'Gitesh Kharik', '8668584811', 'noImage.jpg', '86 F4 2C AC', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-08-13 15:29:43', '2020-08-13 15:29:43', 5, NULL),
(9, 'Ketan Kolge', '9967597305', 'noImage.jpg', 'A4 29 50 24', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-08-18 15:01:32', '2020-10-07 11:47:12', 5, '9898 9808 9809'),
(10, 'Ketan Kolge', '7718865005', 'noImage.jpg', '14 F3 AD 40', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-08-18 15:02:53', '2021-01-28 13:00:13', 5, '102010202029'),
(11, 'Test User Office 2', '9967597305', 'noImage.jpg', 'C0 D8 DC 1A', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-08-25 15:00:12', '2020-09-09 09:17:29', 29, NULL),
(12, 'Test User Office 3', '1122334455', 'noImage.jpg', 'C0 D8 DC 1B', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-08-25 15:00:43', '2020-08-25 15:00:43', 29, NULL),
(13, 'Test User Office 4', '5544332211', 'noImage.jpg', 'C9 10 91 96', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-08-25 15:01:09', '2020-08-25 15:01:09', 29, NULL),
(14, 'Test User Office 5', '9988774455', 'noImage.jpg', '67 9F C3 59', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-08-25 15:01:41', '2020-08-25 15:01:41', 29, NULL),
(15, 'Karishma Palvi', '9689413068', 'noImage.jpg', '1A D1 96 78', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-09-17 10:50:05', '2021-03-19 14:38:28', 5, NULL),
(16, 'TU Office 1', '1231231231', 'noImage.jpg', '1A 56 65 48', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-10-08 11:02:14', '2020-12-20 14:50:19', 5, NULL),
(17, 'Test User 7', '1122334455', 'noImage.jpg', 'F0 27 EB FC', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-10-10 18:34:48', '2020-10-10 18:34:48', 5, NULL),
(18, 'Test User 8', '1122334455', 'noImage.jpg', '5E 3A DD 46', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-10-10 18:36:04', '2020-10-10 18:36:04', 5, NULL),
(19, 'Test User 9', '1122334455', 'noImage.jpg', '04 F2 C6 EA 5F 28 80', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-10-10 18:49:52', '2020-10-10 18:52:09', 5, NULL),
(20, 'LD Purohit', '9784294608', 'noImage.jpg', '04 44 D0 EA 5F 28 80', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-10-30 16:28:02', '2020-10-30 16:32:16', 31, NULL),
(21, 'Ketan Demo card', '7718865005', 'noImage.jpg', '5E 3A DD 46', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-11-25 09:08:25', '2020-11-26 16:39:02', 5, NULL),
(22, 'Samyak Daware', '9284745738', 'noImage.jpg', 'F0 38 1C 1B', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-11-26 16:39:42', '2020-12-20 14:42:48', 5, NULL),
(23, 'Ketan Demo card1', '1234567989', 'noImage.jpg', '04 A8 E1 EA 5F 28 80', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-11-26 16:50:41', '2020-11-26 16:50:41', 5, NULL),
(24, 'Demo User 1', '1234567890', 'noImage.jpg', 'B6 05 7F 03', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-12-21 10:58:00', '2020-12-21 10:58:00', 5, NULL),
(25, 'Demo User 2', '1234567890', 'noImage.jpg', 'C0 00 62 B0', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-12-21 10:58:55', '2020-12-21 10:58:55', 5, NULL),
(26, 'TU Office 2', '1234567890', 'noImage.jpg', '00 FE 6B B0', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-12-25 11:11:26', '2020-12-25 11:11:26', 5, NULL),
(27, 'TU Office 3', '1234567890', 'noImage.jpg', '66 A6 84 03', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2020-12-25 11:12:17', '2020-12-25 11:12:17', 5, NULL),
(28, 'TU Office 2', '1234567890', 'noImage.jpg', '4A 52 76 23', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-01-12 10:36:54', '2021-01-12 10:36:54', 5, NULL),
(29, 'Irfan Shaikh', '9594944587', 'noImage.jpg', '56 B6 9F 03', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 11:49:58', '2021-02-01 11:49:58', 5, NULL),
(30, 'Gaurav Pandey', '8291092267', 'noImage.jpg', '50 6C 66 B0', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 11:53:42', '2021-02-01 11:53:42', 5, NULL),
(31, 'Shreyash  Mhatre', '8104650575', 'noImage.jpg', 'D6 58 70 03', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 11:57:50', '2021-02-01 11:57:50', 5, NULL),
(32, 'Nitin Rajbhar', '9920861712', 'noImage.jpg', '30 9B 65 B0', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 11:59:56', '2021-02-01 11:59:56', 5, NULL),
(33, 'Sudipt Waykar', '8693075950', 'noImage.jpg', 'C6 61 9C 03', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 12:02:06', '2021-02-01 12:02:06', 5, NULL),
(34, 'Vidhi Bhosle', '9821235498', 'noImage.jpg', '67 2B EC 7F', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 12:04:02', '2021-02-01 12:04:02', 5, NULL),
(35, 'Anisha Achary', '9137840755', 'noImage.jpg', '96 A4 7E 03', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 12:06:14', '2021-02-01 15:09:30', 5, NULL),
(36, 'Vidya Bordekar', '9987905636', 'noImage.jpg', '26 82 A7 03', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 12:08:35', '2021-02-01 12:08:35', 5, NULL),
(37, 'Sidhesh Patil', '9757107868', 'noImage.jpg', '36 9D 9E 03', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 12:10:46', '2021-02-01 12:10:46', 5, NULL),
(38, 'Vismaya Prakasan', '9167725089', 'noImage.jpg', '76 41 A3 03', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 12:14:02', '2021-02-01 12:14:02', 5, NULL),
(39, 'Romita Pawar', '9594754332', 'noImage.jpg', '26 50 99 03', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 12:16:39', '2021-02-01 12:16:39', 5, NULL),
(40, 'Kanojiya Abhijeet', '8097278582', 'noImage.jpg', 'D6 00 6C 03', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 12:18:26', '2021-02-01 12:18:26', 5, NULL),
(41, 'Sakshi Singh', '7387767794', 'noImage.jpg', 'B6 FD 99 03', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 12:21:17', '2021-02-01 12:21:17', 5, NULL),
(42, 'Pradnya Kokil', '8652743972', 'noImage.jpg', '46 9F A2 03', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 12:22:45', '2021-02-01 12:22:45', 5, NULL),
(43, 'Divya  Gadhvi', '9137232185', 'noImage.jpg', '96 78 6C 03', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 12:24:22', '2021-02-01 12:24:22', 5, NULL),
(44, 'Shrishti Shetty', '9819324306', 'noImage.jpg', '26 9E A8 1D', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 12:26:19', '2021-02-01 12:26:19', 5, NULL),
(45, 'Preeti Thite', '9867497059', 'noImage.jpg', 'F7 AF EB 7F', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 12:27:45', '2021-02-01 12:27:45', 5, NULL),
(46, 'Mayuri Salve', '8828957606', 'noImage.jpg', 'C6 2E 72 03', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 12:32:45', '2021-02-01 12:32:45', 5, NULL),
(47, 'Abhinaya Pillai', '1234567890', 'noImage.jpg', 'A6 85 A9 1D', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-02-01 12:36:05', '2021-02-01 12:36:40', 5, NULL),
(48, 'Rajesh Surve', '8999606684', 'noImage.jpg', '6A C3 76 85', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-03-10 14:15:09', '2021-03-10 14:15:09', 5, NULL),
(49, 'Pradip Mukne', '8208089961', 'noImage.jpg', 'BA 47 71 7F', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-03-10 14:16:05', '2021-03-10 14:16:05', 5, NULL),
(50, 'Sachin Ainkar', '7066686555', 'noImage.jpg', '67 9B 26 B3', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-03-10 14:16:58', '2021-03-10 14:16:58', 5, NULL),
(51, 'Sidharth Rapate', '7040747403', 'noImage.jpg', 'EC 7C D1 22', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-03-10 14:17:46', '2021-03-10 14:17:46', 5, NULL),
(52, 'Manish Khapre', '8960646447', 'noImage.jpg', 'FA 70 86 7F', 'NOT KNOWN', 'NOT KNOWN', 0, NULL, NULL, 1, '2021-03-19 14:37:52', '2021-03-19 14:37:52', 5, NULL),
(53, 'test user v2', '8787878787', 'bus8271_1619892884.jpg', 'AA AA AA BB', 'Kavesar', 'Coral Squiare', 1, '2021-03-01', '2021-06-30', 1, '2021-05-01 17:16:09', '2021-05-01 18:14:44', 5, '7876787666'),
(54, 'Mihika', '3636353676', 'noImage.jpg', 'bb bb bb cc', 'Vijay Garden', 'Coral Heights', 0, '2021-05-02', '2021-05-02', 1, '2021-05-01 18:41:51', '2021-05-01 18:41:51', 22, '123412341234');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'web', '2020-08-09 16:38:18', '2020-08-09 16:38:18'),
(2, 'Location Admin', 'web', '2020-08-09 17:21:19', '2020-08-09 17:21:19'),
(3, 'Location User', 'web', '2020-08-09 17:21:38', '2020-08-09 17:21:38'),
(4, 'Site Admin', 'web', '2021-04-28 16:57:28', '2021-04-28 16:57:37'),
(6, 'App User', 'web', '2021-04-28 16:59:21', '2021-04-28 16:59:25');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(1, 2),
(2, 2),
(3, 2),
(4, 2),
(5, 2),
(6, 2),
(7, 2),
(6, 3),
(7, 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'testp1', 'testp1@test.com', NULL, '$2y$10$BZlfKnIAbnv9T5VnLURJEO2GqEOa8/wtJriI/WXEM6BIUv.f1.vGW', NULL, '2020-07-31 03:20:20', '2020-07-31 03:20:20'),
(2, 'testSecVM', 'testSecVM@test.com', NULL, '$2y$10$BZlfKnIAbnv9T5VnLURJEO2GqEOa8/wtJriI/WXEM6BIUv.f1.vGW', NULL, '2020-08-02 11:02:12', '2020-08-02 11:02:12'),
(3, 'sa', 'sa@ko-aaham.com', NULL, '$2y$10$BZlfKnIAbnv9T5VnLURJEO2GqEOa8/wtJriI/WXEM6BIUv.f1.vGW', 'mDRDeJ21kxI52dA1uak2ucaXCXpIHmEuzPDOz2VhDkvhfNEOBxIPNAXj9JDR', '2020-08-09 17:44:24', '2020-08-09 17:44:24'),
(4, 'Test User 2', 'testu2@test.com', NULL, '$2y$10$6MZhuyosmAjcr7KmwwEjueEc0SirCDzToNOeBVPGY1nw8DuabyLwm', NULL, '2020-08-09 22:04:04', '2020-08-09 22:16:41'),
(5, 'Dhananjay Thite', 'd.thite@yahoo.com', NULL, '$2y$10$hY8aNlzUiOBJmP2Laqukg.Tj3JQEejNlF2B6PenPWHDO123RhiAhy', NULL, '2020-08-10 07:08:24', '2020-08-10 07:08:24'),
(6, 'Yogesh Borse', 'yogesh.borse@ko-aaham.com', NULL, '$2y$10$Ioaaay80asNh86Jdv/nYSOZEvTH.no93ETVqm/I6jZYRPzi2K8E9S', NULL, '2020-08-10 19:45:09', '2020-08-10 19:45:09'),
(8, 'Terna College', 'terna@test.com', NULL, '$2y$10$DOBqwlF5GByNjor6KytDve7tePfKWaOiRgeKWHdhT2Gx.aEbGXkH6', NULL, '2020-09-09 08:33:33', '2020-09-09 08:33:33'),
(9, 'UI update', 'uiupdate@test.com', NULL, '$2y$10$TlBPpj723DoeH3F5UCiK6eUjWvowwQIg5MaZxLpzfY7ShCkJZvlCa', NULL, '2020-09-10 09:43:02', '2020-09-10 09:43:02'),
(10, 'Arun Dhaneshwar', 'a4appleindia@gmail.com', NULL, '$2y$10$yyVuELaK4KmUVXm66z38pOM21pfg4Cp2ZP81GDWKWWWlBa3ucM0aS', 'tiSZCB9y8naV4io17VGW1e6YsHsPOrxaQWQ9spNUek5ewZwVrtYpW5HywC8r', '2020-09-10 09:51:08', '2020-09-10 09:51:08'),
(11, 'Ashutosh  Sathe', 'ashutosh@pegsconsulting.com', NULL, '$2y$10$AgUmzQ6ydvuh7F9VAaIonOY8nUzqmh19XbK4yxsIeMxFEUm5IgTIm', 'WAj72s6XEdJ7BkJFetXIdbJsXunrHquBjlHbgd7L2mTjoR9RUo2uLnuoTxLE', '2020-09-10 09:58:57', '2020-12-29 19:09:54'),
(12, 'Bharati Parab', 'admin@ko-aaham.com', NULL, '$2y$10$XuD9ZEkHuSve1KaP5OgFs.kbju16M0JZ0nNRcjXjTSbBjIQ4RG3tC', NULL, '2020-09-17 10:39:51', '2020-09-17 10:39:51'),
(13, 'Jatin Mehta', 'jatin@abacusinfotech.net', NULL, '$2y$10$SrGyO7hg8lE.0akR9EG/Teiefj/FIqpecqwXrsTSmLAHf8T8mB3sa', NULL, '2020-09-25 14:01:01', '2020-09-25 14:01:01'),
(14, 'Shailendra', 'shailender@rtesfiltrationsystems.co.in', NULL, '$2y$10$H2B6VrWbN/C2TePGNWlhwuqxmnh.C5JL4e9YwRdNoibFWmV3cDNO2', NULL, '2020-10-03 15:23:42', '2020-10-03 15:23:42'),
(15, 'Admin Shrinathji Mandir', 'info@nathdwaratemple.org', NULL, '$2y$10$H3Howb4eks3YTps3qE/X7OEzYSyqQOF4iygLiGsRBDm2x4LaawYSe', NULL, '2020-10-14 14:42:29', '2020-10-14 14:42:29'),
(16, 'DemoSA', 'demosa@ko-aaham.com', NULL, '$2y$10$9wYjJJCLLSKVn7YJJ7l9ZO9uIHVtqWlxRKRMKoQoQ24YE13c/KS4e', NULL, '2020-11-25 21:41:18', '2020-11-25 21:41:18'),
(17, 'Samyak Daware', 'samyakdaware@gmail.com', NULL, '$2y$10$AgUmzQ6ydvuh7F9VAaIonOY8nUzqmh19XbK4yxsIeMxFEUm5IgTIm', 'IRtWmyy9jloWiDudl1YlSZb9wXhd85bGNtaRQbzfa00zXGexs0XixU66opaH', '2020-12-20 14:45:37', '2020-12-20 14:45:37'),
(18, 'ko-aaham demo user', 'ko-aaham @demo.com', NULL, '$2y$10$cUWakGBi/kdOH8hHzIMjNOKQXgHEJWemXUvkTUtFx8XdZ5J9CLWYW', NULL, '2020-12-25 10:05:25', '2020-12-25 10:05:25'),
(19, 'test user 101', 'test101@test.com', NULL, '$2y$10$Hj7EGtTZqWbCIAMb5XM5xuoRAWYPI0/vI2MhcPgJy54kxOPNLg2O.', NULL, '2021-06-24 01:42:18', '2021-06-24 01:42:18');

-- --------------------------------------------------------

--
-- Stand-in structure for view `vlocdev`
-- (See below for the actual view)
--
CREATE TABLE `vlocdev` (
`locationid` bigint(20) unsigned
,`name` varchar(255)
,`pincode` varchar(6)
,`city` varchar(255)
,`taluka` varchar(255)
,`district` varchar(255)
,`state` varchar(255)
,`locactive` tinyint(1)
,`created_at` timestamp
,`deviceid` bigint(20) unsigned
,`linkactive` tinyint(1)
,`serial_no` varchar(24)
,`devactive` tinyint(1)
,`smsnotification` tinyint(1)
);

-- --------------------------------------------------------

--
-- Structure for view `vlocdev`
--
DROP TABLE IF EXISTS `vlocdev`;

CREATE ALGORITHM=UNDEFINED DEFINER=`phpmyadmin`@`localhost` SQL SECURITY DEFINER VIEW `vlocdev`  AS SELECT `l`.`id` AS `locationid`, `l`.`name` AS `name`, `l`.`pincode` AS `pincode`, `l`.`city` AS `city`, `l`.`taluka` AS `taluka`, `l`.`district` AS `district`, `l`.`state` AS `state`, `l`.`isactive` AS `locactive`, `l`.`created_at` AS `created_at`, `lld`.`deviceid` AS `deviceid`, `lld`.`isactive` AS `linkactive`, `d`.`serial_no` AS `serial_no`, `d`.`isactive` AS `devactive`, `l`.`smsnotification` AS `smsnotification` FROM ((`location` `l` join `LinkLocDev` `lld` on((`l`.`id` = `lld`.`locationid`))) join `device` `d` on((`lld`.`deviceid` = `d`.`id`))) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bedMaster`
--
ALTER TABLE `bedMaster`
  ADD PRIMARY KEY (`id`),
  ADD KEY `i_bedmaster_pk` (`id`),
  ADD KEY `i_bedmaster_locationid` (`locationId`),
  ADD KEY `idx_bedMaster_locationId` (`locationId`);

--
-- Indexes for table `billplan`
--
ALTER TABLE `billplan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `billplan_name_unique` (`name`);

--
-- Indexes for table `devauth`
--
ALTER TABLE `devauth`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_devauth_deviceid` (`deviceid`),
  ADD KEY `idx_devauth_token` (`token`);

--
-- Indexes for table `device`
--
ALTER TABLE `device`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_device_serial_no` (`serial_no`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `iotdata`
--
ALTER TABLE `iotdata`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_iotdata_identifier` (`identifier`),
  ADD KEY `idx_iotdata_deviceid` (`deviceid`),
  ADD KEY `idx_iotdata_created_at` (`created_at`),
  ADD KEY `idx_iotdata_flagstatus` (`flagstatus`);

--
-- Indexes for table `iotdatasummary`
--
ALTER TABLE `iotdatasummary`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_iotdatasummary_device_fordate_unique` (`device`,`fordate`),
  ADD KEY `ix_device` (`device`),
  ADD KEY `ix_fordate` (`fordate`),
  ADD KEY `ix_device_date` (`device`,`fordate`),
  ADD KEY `idx_iotdatasummary_device` (`device`),
  ADD KEY `idx_iotdatasummary_fordate` (`fordate`),
  ADD KEY `idx_iotdatasummary_device_fordate` (`device`,`fordate`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`(191));

--
-- Indexes for table `linkHospitalBedUser`
--
ALTER TABLE `linkHospitalBedUser`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_linkhbu_location` (`locationId`),
  ADD KEY `fk_linkhbu_bed` (`bedId`),
  ADD KEY `fk_linkhbu_user` (`patientId`);

--
-- Indexes for table `LinkLocDev`
--
ALTER TABLE `LinkLocDev`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_LinkLocDev_locationid` (`locationid`),
  ADD KEY `idx_LinkLocDev_deviceid` (`deviceid`),
  ADD KEY `idx_LinkLocDev_isactive` (`isactive`);

--
-- Indexes for table `linklocusers`
--
ALTER TABLE `linklocusers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_linklocusers_locationid` (`locationid`),
  ADD KEY `idx_linklocusers_userid` (`userid`),
  ADD KEY `idx_linklocusers_isactive` (`isactive`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_location_id` (`id`),
  ADD KEY `idx_location_name` (`name`),
  ADD KEY `idx_location_isactive` (`isactive`),
  ADD KEY `idx_location_parent` (`parent`);

--
-- Indexes for table `locationbillplanlink`
--
ALTER TABLE `locationbillplanlink`
  ADD PRIMARY KEY (`id`),
  ADD KEY `locationbillplanlink_locationid_foreign` (`locationid`),
  ADD KEY `locationbillplanlink_planid_foreign` (`planid`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reguser`
--
ALTER TABLE `reguser`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reguser_tagid` (`tagid`),
  ADD KEY `idx_reguser_resiarea` (`resiarea`),
  ADD KEY `idx_reguser_resilandmark` (`resilandmark`),
  ADD KEY `idx_reguser_isactive` (`isactive`),
  ADD KEY `idx_reguser_locationid` (`locationid`),
  ADD KEY `idx_reguser_AadharNo` (`AadharNo`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bedMaster`
--
ALTER TABLE `bedMaster`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `billplan`
--
ALTER TABLE `billplan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `devauth`
--
ALTER TABLE `devauth`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `device`
--
ALTER TABLE `device`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `iotdata`
--
ALTER TABLE `iotdata`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71351;

--
-- AUTO_INCREMENT for table `iotdatasummary`
--
ALTER TABLE `iotdatasummary`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5414;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `linkHospitalBedUser`
--
ALTER TABLE `linkHospitalBedUser`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `LinkLocDev`
--
ALTER TABLE `LinkLocDev`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `linklocusers`
--
ALTER TABLE `linklocusers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `locationbillplanlink`
--
ALTER TABLE `locationbillplanlink`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reguser`
--
ALTER TABLE `reguser`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bedMaster`
--
ALTER TABLE `bedMaster`
  ADD CONSTRAINT `fk_bedmaster_location_locationid` FOREIGN KEY (`locationId`) REFERENCES `location` (`id`);

--
-- Constraints for table `devauth`
--
ALTER TABLE `devauth`
  ADD CONSTRAINT `fk_devauth_dev_devid` FOREIGN KEY (`deviceid`) REFERENCES `device` (`serial_no`);

--
-- Constraints for table `iotdata`
--
ALTER TABLE `iotdata`
  ADD CONSTRAINT `fk_iotdata_device` FOREIGN KEY (`deviceid`) REFERENCES `device` (`serial_no`);

--
-- Constraints for table `iotdatasummary`
--
ALTER TABLE `iotdatasummary`
  ADD CONSTRAINT `fk_iotsummary_deviceid` FOREIGN KEY (`device`) REFERENCES `device` (`serial_no`);

--
-- Constraints for table `linkHospitalBedUser`
--
ALTER TABLE `linkHospitalBedUser`
  ADD CONSTRAINT `fk_linkhbu_bed` FOREIGN KEY (`bedId`) REFERENCES `bedMaster` (`id`),
  ADD CONSTRAINT `fk_linkhbu_location` FOREIGN KEY (`locationId`) REFERENCES `location` (`id`),
  ADD CONSTRAINT `fk_linkhbu_user` FOREIGN KEY (`patientId`) REFERENCES `reguser` (`id`);

--
-- Constraints for table `LinkLocDev`
--
ALTER TABLE `LinkLocDev`
  ADD CONSTRAINT `fk_lld_device` FOREIGN KEY (`deviceid`) REFERENCES `device` (`id`),
  ADD CONSTRAINT `fk_lld_location` FOREIGN KEY (`locationid`) REFERENCES `location` (`id`);

--
-- Constraints for table `linklocusers`
--
ALTER TABLE `linklocusers`
  ADD CONSTRAINT `fl_llu_location` FOREIGN KEY (`locationid`) REFERENCES `location` (`id`),
  ADD CONSTRAINT `fl_llu_user` FOREIGN KEY (`userid`) REFERENCES `users` (`id`);

--
-- Constraints for table `locationbillplanlink`
--
ALTER TABLE `locationbillplanlink`
  ADD CONSTRAINT `locationbillplanlink_locationid_foreign` FOREIGN KEY (`locationid`) REFERENCES `location` (`id`),
  ADD CONSTRAINT `locationbillplanlink_planid_foreign` FOREIGN KEY (`planid`) REFERENCES `billplan` (`id`);

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reguser`
--
ALTER TABLE `reguser`
  ADD CONSTRAINT `fk_reguser_location` FOREIGN KEY (`locationid`) REFERENCES `location` (`id`);

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
