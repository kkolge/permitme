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
SET time_zone = "+05:30";


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
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


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
(1, 'App\\User', 1);

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
(1, 'sa', 'sa@ko-aaham.com', NULL, '$2y$10$BZlfKnIAbnv9T5VnLURJEO2GqEOa8/wtJriI/WXEM6BIUv.f1.vGW', 'mDRDeJ21kxI52dA1uak2ucaXCXpIHmEuzPDOz2VhDkvhfNEOBxIPNAXj9JDR', '2020-08-09 17:44:24', '2020-08-09 17:44:24');

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

--
-- Structure for view `vlocdev`
--
DROP TABLE IF EXISTS `vlocdev`;

CREATE VIEW `vlocdev`  AS SELECT `l`.`id` AS `locationid`, `l`.`name` AS `name`, `l`.`pincode` AS `pincode`, `l`.`city` AS `city`, `l`.`taluka` AS `taluka`, `l`.`district` AS `district`, `l`.`state` AS `state`, `l`.`isactive` AS `locactive`, `l`.`created_at` AS `created_at`, `lld`.`deviceid` AS `deviceid`, `lld`.`isactive` AS `linkactive`, `d`.`serial_no` AS `serial_no`, `d`.`isactive` AS `devactive`, `l`.`smsnotification` AS `smsnotification` FROM ((`location` `l` join `LinkLocDev` `lld` on((`l`.`id` = `lld`.`locationid`))) join `device` `d` on((`lld`.`deviceid` = `d`.`id`))) ;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `billplan`
--
ALTER TABLE `billplan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `devauth`
--
ALTER TABLE `devauth`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device`
--
ALTER TABLE `device`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `iotdata`
--
ALTER TABLE `iotdata`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `iotdatasummary`
--
ALTER TABLE `iotdatasummary`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `linkHospitalBedUser`
--
ALTER TABLE `linkHospitalBedUser`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `LinkLocDev`
--
ALTER TABLE `LinkLocDev`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `linklocusers`
--
ALTER TABLE `linklocusers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locationbillplanlink`
--
ALTER TABLE `locationbillplanlink`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
