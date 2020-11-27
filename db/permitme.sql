-- phpMyAdmin SQL Dump
-- version 4.9.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 27, 2020 at 05:00 PM
-- Server version: 5.6.41-84.1
-- PHP Version: 7.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `koaahuap_pm_4nov20`
--

DELIMITER $$
--
-- Procedures
--
CREATE PROCEDURE `getCountByCity` (IN `inVal` VARCHAR(255))  BEGIN
		        declare done int default false;
                declare qVal int ;
                declare curForLoop cursor for 
			        select location.id from permitmemass.location 
				        where location.city = inVal;
		        declare continue handler for not found set done = true;
                
                open curForLoop;        
			        read_loop: loop
				        fetch curForLoop into qVal;
                        if done then
					        leave read_loop;
				        end if;
                        select l.pincode, l.id, ifnull(count(*),0) as cnt from permitmemass.location as l 
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
  `bedNo` varchar(10) NOT NULL,
  `locationId` bigint(20) NOT NULL,
  `isactive` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB;

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
-- Table structure for table `device`
--

CREATE TABLE `device` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `serial_no` varchar(24)  NOT NULL,
  `isactive` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB ;

--
-- Dumping data for table `device`
--

INSERT INTO `device` (`id`, `serial_no`, `isactive`, `created_at`, `updated_at`) VALUES
(1, 'DEVICE0', 0, '2020-07-31 12:24:05', '2020-07-31 12:24:05'),
(2, 'DEVICE1', 1, '2020-07-31 12:24:05', '2020-07-31 12:24:05'),
(3, 'DEVICE2', 1, '2020-07-31 12:24:05', '2020-07-31 12:24:05'),
(4, 'DEVICE3', 1, '2020-07-31 12:24:05', '2020-07-31 12:24:05'),
(5, 'DEVICE4', 0, '2020-07-31 12:24:05', '2020-07-31 12:24:05'),
(6, 'DEVICE06', 1, '2020-07-31 17:49:56', '2020-07-31 17:49:56'),
(7, 'DEVICE7', 0, '2020-07-31 17:50:07', '2020-07-31 17:50:07'),
(8, 'DEVICE08', 1, '2020-08-08 20:47:22', '2020-08-08 20:50:52'),
(9, 'DEVICEHR01', 1, '2020-08-10 07:01:19', '2020-08-10 07:01:19'),
(10, 'DEVTEST01', 1, '2020-08-23 18:44:10', '2020-08-23 18:44:10'),
(11, 'DEVTEST02', 1, '2020-08-23 18:44:40', '2020-08-23 18:44:40'),
(12, 'DEVTEST03', 1, '2020-08-23 18:44:50', '2020-08-23 18:44:50'),
(13, 'DEVTEST04', 1, '2020-08-23 18:45:00', '2020-08-23 18:45:00'),
(14, 'DEVTEST05', 1, '2020-08-23 18:45:10', '2020-08-23 18:45:10'),
(15, 'DEVTEST06', 1, '2020-08-23 18:45:23', '2020-08-23 18:45:23'),
(16, 'DEVTEST07', 1, '2020-08-23 18:45:34', '2020-08-23 18:45:34'),
(17, 'DEVTEST08', 1, '2020-08-23 18:45:44', '2020-08-23 18:45:44'),
(18, 'DEVTEST09', 1, '2020-08-23 18:45:52', '2020-08-23 18:45:52'),
(19, 'DEVTEST10', 1, '2020-08-23 18:46:01', '2020-08-23 18:46:01'),
(20, 'DEVTEST11', 1, '2020-08-23 18:46:13', '2020-08-23 18:46:13'),
(21, 'DEVTEST12', 1, '2020-08-23 18:46:22', '2020-08-23 18:46:22'),
(22, 'DEVTEST13', 1, '2020-08-23 18:46:34', '2020-08-23 18:46:34'),
(23, 'DEVTEST14', 1, '2020-08-23 18:46:43', '2020-08-23 18:46:43'),
(24, 'DEVTEST15', 1, '2020-08-23 18:46:52', '2020-08-23 18:46:52'),
(25, 'DEVTEST16', 1, '2020-08-23 18:47:01', '2020-08-23 18:47:01'),
(26, 'DEVTEST17', 1, '2020-08-23 18:47:09', '2020-08-23 18:47:09'),
(27, 'DEVTEST18', 1, '2020-08-23 18:47:18', '2020-08-23 18:47:18'),
(28, 'DEVTEST19', 1, '2020-08-23 18:47:26', '2020-08-23 18:47:26'),
(29, 'DEVTEST20', 1, '2020-08-23 18:47:34', '2020-08-23 18:47:34'),
(30, 'DEVTEST21', 1, '2020-08-23 18:47:43', '2020-08-23 18:47:43'),
(31, 'DEVTEST22', 1, '2020-08-23 18:47:53', '2020-08-23 18:47:53'),
(32, 'DEVTEST23', 1, '2020-08-23 18:48:04', '2020-08-23 18:48:04'),
(33, 'DEVTEST24', 1, '2020-08-23 18:48:13', '2020-08-23 18:48:13'),
(34, 'DEVTEST25', 1, '2020-08-23 18:48:24', '2020-08-23 18:48:24'),
(35, 'DEVTEST26', 1, '2020-08-23 18:48:34', '2020-08-23 18:48:34'),
(36, 'DEVTEST26', 0, '2020-08-23 18:48:45', '2020-08-23 18:49:50'),
(37, 'DEVTEST27', 1, '2020-08-23 18:48:57', '2020-08-23 18:48:57'),
(38, 'DEVTEST28', 1, '2020-08-23 18:49:05', '2020-08-23 18:49:05'),
(39, 'DEVTEST29', 1, '2020-08-23 18:49:14', '2020-08-23 18:49:14'),
(40, 'DEVTEST30', 1, '2020-08-23 18:49:28', '2020-08-23 18:49:28'),
(41, 'DEVKODEMO1', 1, '2020-09-09 00:14:53', '2020-09-09 00:14:53'),
(42, 'DEVKODEMO2', 1, '2020-09-09 09:10:56', '2020-09-09 09:10:56'),
(43, 'DEV-KO-001', 1, '2020-09-09 10:27:29', '2020-09-09 10:27:29'),
(44, 'DEVKODEMO3', 1, '2020-09-09 12:03:09', '2020-09-09 12:03:09'),
(45, 'DEVKODEMO4', 1, '2020-09-09 15:54:54', '2020-09-09 15:54:54'),
(46, 'DEVKODEMO5', 1, '2020-09-18 13:29:10', '2020-09-18 13:29:10'),
(47, 'DEVKODEMO9', 1, '2020-09-19 11:20:21', '2020-09-19 11:20:21'),
(48, 'DEVKODEMO6', 1, '2020-09-22 12:15:41', '2020-09-22 12:15:41'),
(49, 'DEVKODEMO7', 1, '2020-09-25 12:49:27', '2020-09-25 12:49:27'),
(50, 'DEVKODEMO8', 1, '2020-09-25 12:49:40', '2020-09-25 12:49:40'),
(51, 'DEV-KO-002', 1, '2020-10-14 15:17:24', '2020-10-14 15:17:24'),
(52, 'DEV-KO-003', 1, '2020-10-22 14:13:45', '2020-10-22 14:13:45'),
(53, 'dupgrade1', 0, '2020-11-04 23:04:19', '2020-11-04 23:04:19'),
(54, 'DEVKODEMO12', 1, '2020-11-07 15:09:14', '2020-11-07 15:09:14');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection` text  NOT NULL,
  `queue` text  NOT NULL,
  `payload` longtext  NOT NULL,
  `exception` longtext  NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB ;

-- --------------------------------------------------------

--
-- Table structure for table `iotdata`
--

CREATE TABLE `iotdata` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `deviceid` varchar(255)  NOT NULL,
  `temp` double(8,2) NOT NULL,
  `spo2` int(11) NOT NULL,
  `hbcount` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `flagstatus` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB ;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255)NOT NULL,
  `payload` longtext  NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB ;

-- --------------------------------------------------------

--
-- Table structure for table `linkHospitalBedUser`
--

CREATE TABLE `linkHospitalBedUser` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `locationId` bigint(20) NOT NULL,
  `bedId` bigint(20) NOT NULL,
  `patientId` bigint(20) NOT NULL,
  `isactive` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB ;

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
  `locationid` bigint(20) NOT NULL,
  `deviceid` bigint(20) NOT NULL,
  `isactive` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB ;

--
-- Dumping data for table `LinkLocDev`
--

INSERT INTO `LinkLocDev` (`id`, `locationid`, `deviceid`, `isactive`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2020-07-31 19:49:07', '2020-07-31 20:12:03'),
(2, 1, 2, 1, '2020-08-08 18:54:28', '2020-08-08 18:54:28'),
(3, 4, 3, 1, '2020-08-09 09:58:37', '2020-08-09 09:58:37'),
(4, 5, 8, 1, '2020-08-09 18:06:54', '2020-08-09 18:06:54'),
(5, 7, 6, 1, '2020-08-09 22:41:37', '2020-08-09 22:41:54'),
(6, 8, 9, 1, '2020-08-10 07:07:18', '2020-08-10 07:07:18'),
(7, 16, 10, 1, '2020-08-23 18:59:32', '2020-08-23 18:59:32'),
(8, 14, 11, 1, '2020-08-23 18:59:45', '2020-08-23 18:59:45'),
(9, 15, 12, 1, '2020-08-23 18:59:58', '2020-08-23 18:59:58'),
(10, 13, 13, 1, '2020-08-23 19:00:16', '2020-08-23 19:00:16'),
(11, 11, 14, 1, '2020-08-23 19:00:31', '2020-08-23 19:00:31'),
(12, 9, 15, 1, '2020-08-23 19:00:43', '2020-08-23 19:00:43'),
(13, 12, 16, 1, '2020-08-23 19:00:59', '2020-08-23 19:00:59'),
(14, 10, 17, 1, '2020-08-23 19:01:15', '2020-08-23 19:01:15'),
(15, 17, 18, 1, '2020-08-23 19:06:22', '2020-08-23 19:06:22'),
(16, 18, 19, 1, '2020-08-23 19:07:56', '2020-08-23 19:07:56'),
(17, 19, 20, 1, '2020-08-23 19:09:28', '2020-08-23 19:09:28'),
(18, 20, 21, 1, '2020-08-23 19:10:45', '2020-08-23 19:10:45'),
(19, 21, 22, 1, '2020-08-23 19:11:45', '2020-08-23 19:11:45'),
(20, 22, 23, 1, '2020-08-23 19:13:08', '2020-08-23 19:13:08'),
(21, 23, 24, 1, '2020-08-23 19:14:51', '2020-08-23 19:14:51'),
(22, 24, 25, 1, '2020-08-23 19:20:15', '2020-08-23 19:20:15'),
(23, 25, 26, 1, '2020-08-23 19:21:18', '2020-08-23 19:21:18'),
(24, 26, 27, 1, '2020-08-23 19:22:21', '2020-08-23 19:22:21'),
(25, 27, 28, 1, '2020-08-23 19:23:52', '2020-08-23 19:23:52'),
(26, 28, 29, 1, '2020-08-23 19:24:50', '2020-08-23 19:24:50'),
(27, 8, 30, 1, '2020-08-23 19:26:05', '2020-08-23 19:26:05'),
(28, 5, 31, 1, '2020-08-23 19:26:20', '2020-08-23 19:26:20'),
(29, 6, 32, 1, '2020-08-23 19:26:38', '2020-08-23 19:26:38'),
(30, 4, 33, 1, '2020-08-23 19:26:56', '2020-08-23 19:26:56'),
(31, 1, 34, 1, '2020-08-23 19:27:10', '2020-08-23 19:27:10'),
(32, 29, 41, 1, '2020-09-09 00:15:22', '2020-09-09 00:15:22'),
(33, 30, 42, 1, '2020-09-09 09:11:19', '2020-09-09 09:11:19'),
(34, 5, 43, 1, '2020-09-09 10:27:43', '2020-09-09 10:27:43'),
(35, 5, 44, 1, '2020-09-09 12:04:14', '2020-09-09 12:04:14'),
(36, 5, 45, 1, '2020-09-09 15:56:03', '2020-09-09 15:56:03'),
(37, 5, 46, 1, '2020-09-18 13:32:06', '2020-09-18 13:32:06'),
(39, 6, 48, 1, '2020-09-22 12:15:55', '2020-09-22 12:15:55'),
(40, 31, 47, 1, '2020-10-14 14:43:42', '2020-10-14 14:43:42'),
(41, 5, 51, 1, '2020-10-14 15:17:53', '2020-10-14 15:17:53'),
(42, 31, 49, 1, '2020-10-29 10:43:10', '2020-10-29 10:43:10'),
(43, 5, 52, 1, '2020-11-05 16:21:58', '2020-11-05 16:21:58'),
(45, 29, 50, 1, '2020-11-09 17:26:00', '2020-11-09 17:26:00');

-- --------------------------------------------------------

--
-- Table structure for table `linklocusers`
--

CREATE TABLE `linklocusers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `locationid` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `phoneno1` varchar(255)  NOT NULL,
  `isactive` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB ;

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
(9, 29, 10, 'Management', '9833848283', 1, '2020-09-10 09:52:27', '2020-09-10 09:52:27'),
(10, 5, 11, 'Officer', '9822574956', 1, '2020-09-10 10:00:06', '2020-09-10 10:00:06'),
(11, 5, 12, 'HR', '7718865003', 1, '2020-09-17 10:40:50', '2020-09-17 10:40:50'),
(12, 30, 13, 'Management', '9820324306', 1, '2020-09-25 14:04:11', '2020-09-25 14:04:11'),
(13, 6, 14, 'Management', '9885315327', 1, '2020-10-03 15:25:07', '2020-10-03 15:25:07'),
(14, 31, 15, 'Management', '9920650618', 1, '2020-10-14 14:46:02', '2020-10-14 14:46:02'),
(15, 5, 16, 'Other', '8779126851', 1, '2020-11-25 21:42:33', '2020-11-25 21:42:33');

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `noofresidents` varchar(255)  NOT NULL,
  `address1` varchar(255)  NOT NULL,
  `address2` varchar(255)  NOT NULL,
  `pincode` varchar(6)  NOT NULL,
  `city` varchar(255)  NOT NULL,
  `taluka` varchar(255)  DEFAULT NULL,
  `district` varchar(255)  DEFAULT NULL,
  `state` varchar(255)  NOT NULL,
  `isactive` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `smsnotification` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB ;

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`id`, `name`, `noofresidents`, `address1`, `address2`, `pincode`, `city`, `taluka`, `district`, `state`, `isactive`, `created_at`, `updated_at`, `smsnotification`) VALUES
(1, 'Vidarbh Mahesh CHS', '85', 'Coral Heights Compound,', 'G. B. Road, Thane West', '400615', 'Thane', 'Thane', 'Thane', 'Maharashtra', 1, '2020-07-31 18:12:26', '2020-07-31 18:12:26', 0),
(4, 'Swastik Palms', '1000', 'Brahmand', 'Phase 6', '400607', 'Thane', 'Thane', 'Thane', 'Maharashtra', 1, '2020-07-31 18:26:52', '2020-10-13 17:30:48', 0),
(5, 'Ko-Aaham Technologies LLP', '20', 'Bhumi World Industrial Estate', 'Pimplas Village', '421302', 'Thane', 'Thane', 'Thane', 'Maharashtra', 1, '2020-08-09 17:47:29', '2020-11-07 16:08:29', 0),
(6, 'Realtime Engineering Systems', '30', 'Masabthank', 'Banjara Hills', '500028', 'Hyderabad', 'Hyderabad', 'Hyderabad', 'Telangana', 1, '2020-08-09 17:49:54', '2020-10-13 17:31:01', 0),
(7, 'Test Location', '10', 'test address 1', 'test address 2', '400001', 'Mumbai', 'Mumbai', 'Mumbai', 'Maharashtra', 1, '2020-08-09 17:51:29', '2020-08-09 17:51:29', 0),
(8, 'Hiranandani Estate', '15000', 'Patlipada', 'G. B. Road, Thane West', '400607', 'Thane', 'Thane', 'Thane', 'Maharashtra', 1, '2020-08-10 07:00:32', '2020-08-10 07:00:32', 0),
(9, 'Shivani Textiles', '200', 'Rajive Gandhi Road', 'Adhalgaon', '413701', 'Ahmednagar', 'Adhalgaon', 'Ahmednagar', 'Maharashtra', 1, '2020-08-23 18:34:27', '2020-10-13 17:30:30', 0),
(10, 'Vijaya Printers', '20', 'M. G. Road', 'Ahmednagar City', '414001', 'Ahmednagar', 'Ahmednagar City', 'Ahmednagar', 'Maharashtra', 1, '2020-08-23 18:35:32', '2020-08-23 18:35:32', 0),
(11, 'Ritika Collections', '30', '1089', 'Tree Top Lane', '414701', 'Ajnuj', 'Ajnuj', 'Ahmednagar', 'Maharashtra', 1, '2020-08-23 18:36:34', '2020-08-23 18:36:34', 0),
(12, 'Vijay Vilas Residency', '200', 'Savarkar Road', 'Akola', '414102', 'Akola', 'Akola', 'Ahmednagar', 'Maharashtra', 1, '2020-08-23 18:37:20', '2020-08-23 18:37:20', 0),
(13, 'Rajguru Pvt. Ltd.', '25', '2nd Floor,', 'M. K. Plaza', '413728', 'Chikhali', 'Chikhali', 'Ahmednagar', 'Maharashtra', 1, '2020-08-23 18:38:43', '2020-08-23 18:38:43', 0),
(14, 'Mezzanine', '35', 'Mezzanine floor', 'Navratna D\'Mello Road', '424405', 'Shirpur', 'Shirpur', 'Dhule', 'Maharashtra', 1, '2020-08-23 18:40:32', '2020-08-23 18:40:32', 0),
(15, 'Patel Ashwamegh', '40', 'Sahayaji Gunj', 'Dhule Market yard', '424004', 'Dhule', 'Dhule Market yard', 'Dhule', 'Maharashtra', 1, '2020-08-23 18:41:19', '2020-08-23 18:41:19', 0),
(16, 'Coral Square', '50', '13/3', 'Opp Bata Showroom', '424001', 'Devpur', 'Devpur', 'Dhule', 'Maharashtra', 1, '2020-08-23 18:42:04', '2020-08-23 18:42:04', 0),
(17, 'i-Phone Park', '45', '208 Regent Chanbers', 'Above Status Restaurant', '425406', 'Shindkheda', 'Shindkheda', 'Dhule', 'Maharashtra', 1, '2020-08-23 19:06:06', '2020-08-23 19:06:06', 0),
(18, 'Piezers Park', '600', 'Above Shiva Hotel', 'Ram Nagar', '424304', 'Sakri', 'Sakri', 'Dhule', 'Maharashtra', 1, '2020-08-23 19:07:35', '2020-08-23 19:07:35', 0),
(19, 'Kalandi Textile', '60', 'Shalimar Garden Road', 'S P', '416215', 'Shahuwadi', 'Shahuwadi', 'Kolhapur', 'Maharashtra', 1, '2020-08-23 19:09:15', '2020-08-23 19:09:15', 0),
(20, 'Lakme Acadamy', '50', 'Shahibabad', 'M G Road', '416201', 'Panahala', 'Panahala', 'Kolhapur', 'Maharashtra', 1, '2020-08-23 19:10:31', '2020-08-23 19:10:31', 0),
(21, 'Arunoday Publications', '54', '11th floor', 'G-Corp', '416103', 'Shirol', 'Shirol', 'Kolhapur', 'Maharashtra', 1, '2020-08-23 19:11:28', '2020-08-23 19:11:28', 0),
(22, 'Mamata Maternity home', '40', 'Castelmills naka', 'Chitalsar', '416502', 'Gadhinglaj', 'Gadhinglaj', 'Kolhapur', 'Maharashtra', 1, '2020-08-23 19:12:51', '2020-08-23 19:12:51', 0),
(23, 'Raheja Complex', '1500', 'M K Road', 'Opp Mac', '416212', 'Radhanagari', 'Radhanagari', 'Kolhapur', 'Maharashtra', 1, '2020-08-23 19:14:37', '2020-08-23 19:14:37', 0),
(24, 'Reva Tech', '605', '2nd Floor', 'Shalimar Business Park', '400050', 'Bandra', 'Mumbai', 'Mumbai', 'Maharashtra', 1, '2020-08-23 19:19:32', '2020-08-23 19:19:32', 0),
(25, 'Shiva prasad publications', '400', 'Thakar bappa road', 'Kurla', '400070', 'Mumbai', 'Mumbai', 'Mumbai', 'Maharashtra', 1, '2020-08-23 19:21:01', '2020-08-23 19:21:01', 0),
(26, 'Manisha Garden', '200', 'Nr. Mulund Gymkhana', 'Mulund', '400081', 'Mumbai', 'Mumbai', 'Mumbai', 'Maharashtra', 1, '2020-08-23 19:22:08', '2020-08-23 19:22:08', 0),
(27, 'Mafco Pvt. Ltd.', '100', 'Sahakarnagar', 'No 5, Chembur', '400071', 'Mumbai', 'Mumbai', 'Mumbai', 'Maharashtra', 1, '2020-08-23 19:23:12', '2020-08-23 19:23:12', 0),
(28, 'Elegant Business Park', '250', 'Bh. Kohinoor Continental', 'Andheri', '400069', 'Mumabi', 'Andheri', 'Mumbai', 'Maharashtra', 1, '2020-08-23 19:24:38', '2020-08-23 19:24:38', 0),
(29, 'Ko-Aahan Demo', '50', 'G B Road', 'Chandroday', '400607', 'Thane', 'Thane', 'Thane', 'Maharashtra', 1, '2020-09-08 14:20:07', '2020-09-22 19:41:31', 0),
(30, 'Abacus Infotech', '10', '211, Blue Rose Industrial Premises, 2nd Floor, Above Maruti Showroom', 'Datta Pada, Opp. Western Express Highway, Borivali (East)', '400066', 'Mumbai', 'Mumbai', 'Mumbai', 'Maharashtra', 1, '2020-09-25 14:03:01', '2020-09-25 14:03:01', 0),
(31, 'Shrinathji Temple', '500', 'Nathdwara', 'Nathdwara', '313301', 'Nathdwara', 'Nathdwara', 'Nathdwara', 'Rajasthan', 1, '2020-10-14 14:40:29', '2020-10-14 14:40:29', 0);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB ;

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
(21, '2020_10_11_161435_add_bedmaster', 15);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB ;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB ;

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
(1, 'App\\User', 16);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255)  NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB ;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255)  NOT NULL,
  `guard_name` varchar(255)  NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB ;

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
  `name` varchar(50) NOT NULL,
  `phoneno` varchar(255)  NOT NULL,
  `coverimage` varchar(255)  NOT NULL DEFAULT 'noImage.jpg',
  `tagid` varchar(255)  NOT NULL,
  `isactive` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `locationid` bigint(20) NOT NULL,
  `AadharNo` varchar(16)  DEFAULT NULL
) ENGINE=InnoDB ;

--
-- Dumping data for table `reguser`
--

INSERT INTO `reguser` (`id`, `name`, `phoneno`, `coverimage`, `tagid`, `isactive`, `created_at`, `updated_at`, `locationid`, `AadharNo`) VALUES
(1, 'user 1', '1231231231', 'noImage_1596919254.jpg', 'AA BB CC DD', 1, '2020-08-08 19:51:17', '2020-08-08 20:43:36', 1, NULL),
(2, 'user 2', '9999999991', 'noImage.jpg', 'AA BB CC AA', 1, '2020-08-09 20:08:16', '2020-08-09 20:08:16', 1, NULL),
(3, 'Test User Office 1', '9999123456', 'noImage.jpg', 'C9 10 91 96', 1, '2020-08-12 13:06:44', '2020-08-12 13:06:44', 1, NULL),
(4, 'Yogesh Borse', '8983177587', 'Ko-Aaham_1604742624.png', '26 6A A3 AC', 1, '2020-08-13 15:25:32', '2020-11-07 15:20:24', 5, NULL),
(5, 'Bharati Parab', '8451006992', 'noImage.jpg', '14 F6 FA 40', 1, '2020-08-13 15:27:08', '2020-08-13 15:27:08', 5, NULL),
(6, 'Vikas kshirsagar', '9325468143', 'noImage.jpg', '80 C4 D5 E5', 1, '2020-08-13 15:28:20', '2020-08-13 15:28:20', 5, NULL),
(7, 'Chetan Sonavane', '8888152210', 'noImage.jpg', 'DA F2 32 47', 1, '2020-08-13 15:29:12', '2020-08-13 15:29:12', 5, NULL),
(8, 'Gitesh Kharik', '8668584811', 'noImage.jpg', '86 F4 2C AC', 1, '2020-08-13 15:29:43', '2020-08-13 15:29:43', 5, NULL),
(9, 'Ketan Kolge', '9967597305', 'noImage.jpg', 'A4 29 50 24', 1, '2020-08-18 15:01:32', '2020-10-07 11:47:12', 5, NULL),
(10, 'Kavita Sahu', '7499939307', 'noImage.jpg', '14 F3 AD 40', 1, '2020-08-18 15:02:53', '2020-08-18 15:02:53', 5, NULL),
(11, 'Test User Office 2', '9967597305', 'noImage.jpg', 'C0 D8 DC 1A', 1, '2020-08-25 15:00:12', '2020-09-09 09:17:29', 29, NULL),
(12, 'Test User Office 3', '1122334455', 'noImage.jpg', 'C0 D8 DC 1B', 1, '2020-08-25 15:00:43', '2020-08-25 15:00:43', 29, NULL),
(13, 'Test User Office 4', '5544332211', 'noImage.jpg', 'C9 10 91 96', 1, '2020-08-25 15:01:09', '2020-08-25 15:01:09', 29, NULL),
(14, 'Test User Office 5', '9988774455', 'noImage.jpg', '67 9F C3 59', 1, '2020-08-25 15:01:41', '2020-08-25 15:01:41', 29, NULL),
(15, 'Karishma Palvi', '9689413068', 'noImage.jpg', '85 7D 57 BE', 1, '2020-09-17 10:50:05', '2020-09-17 10:50:05', 5, NULL),
(16, 'Test User Office 6', '1231231231', 'noImage.jpg', '1A 56 65 48', 1, '2020-10-08 11:02:14', '2020-10-08 11:02:14', 5, NULL),
(17, 'Test User 7', '1122334455', 'noImage.jpg', 'F0 27 EB FC', 1, '2020-10-10 18:34:48', '2020-10-10 18:34:48', 5, NULL),
(18, 'Test User 8', '1122334455', 'noImage.jpg', '5E 3A DD 46', 1, '2020-10-10 18:36:04', '2020-10-10 18:36:04', 5, NULL),
(19, 'Test User 9', '1122334455', 'noImage.jpg', '04 F2 C6 EA 5F 28 80', 1, '2020-10-10 18:49:52', '2020-10-10 18:52:09', 5, NULL),
(20, 'LD Purohit', '9784294608', 'noImage.jpg', '04 44 D0 EA 5F 28 80', 1, '2020-10-30 16:28:02', '2020-10-30 16:32:16', 31, NULL),
(21, 'Ketan Demo card', '7718865005', 'noImage.jpg', '5E 3A DD 46', 1, '2020-11-25 09:08:25', '2020-11-26 16:39:02', 5, NULL),
(22, 'Test User 10', '1234657981', 'noImage.jpg', 'F0 38 1C 1B', 1, '2020-11-26 16:39:42', '2020-11-26 16:39:42', 5, NULL),
(23, 'Ketan Demo card1', '1234567989', 'noImage.jpg', '04 A8 E1 EA 5F 28 80', 1, '2020-11-26 16:50:41', '2020-11-26 16:50:41', 5, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255)  NOT NULL,
  `guard_name` varchar(255)  NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB ;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'web', '2020-08-09 16:38:18', '2020-08-09 16:38:18'),
(2, 'Admin', 'web', '2020-08-09 17:21:19', '2020-08-09 17:21:19'),
(3, 'Location User', 'web', '2020-08-09 17:21:38', '2020-08-09 17:21:38');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB ;

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
  `name` varchar(255) NOT NULL,
  `email` varchar(255)  NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255)  NOT NULL,
  `remember_token` varchar(100)  DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'testp1', 'testp1@test.com', NULL, '$2y$10$La.iZvpQ1fJQ5eNfZ2RtWurHL3/ZD5r/6P9jAZ7Vkj6y8VwFU5UWy', NULL, '2020-07-31 03:20:20', '2020-07-31 03:20:20'),
(2, 'testSecVM', 'testSecVM@test.com', NULL, '$2y$10$86ZJ3AuUGaItBsIJVhEe2u0j1DBlRt46O6qe2iBDlnVohyJPdi.R6', NULL, '2020-08-02 11:02:12', '2020-08-02 11:02:12'),
(3, 'sa', 'sa@ko-aaham.com', NULL, '$2y$10$BZlfKnIAbnv9T5VnLURJEO2GqEOa8/wtJriI/WXEM6BIUv.f1.vGW', 'qDG8FjWT1h4vp7GaKoQBOs163GiA0RKRkqGviZSoi2u19EHg8t9AWuiEC44u', '2020-08-09 17:44:24', '2020-08-09 17:44:24'),
(4, 'Test User 2', 'testu2@test.com', NULL, '$2y$10$6MZhuyosmAjcr7KmwwEjueEc0SirCDzToNOeBVPGY1nw8DuabyLwm', NULL, '2020-08-09 22:04:04', '2020-08-09 22:16:41'),
(5, 'Dhananjay Thite', 'd.thite@yahoo.com', NULL, '$2y$10$hY8aNlzUiOBJmP2Laqukg.Tj3JQEejNlF2B6PenPWHDO123RhiAhy', NULL, '2020-08-10 07:08:24', '2020-08-10 07:08:24'),
(6, 'Yogesh Borse', 'yogesh.borse@ko-aaham.com', NULL, '$2y$10$Ioaaay80asNh86Jdv/nYSOZEvTH.no93ETVqm/I6jZYRPzi2K8E9S', NULL, '2020-08-10 19:45:09', '2020-08-10 19:45:09'),
(8, 'Terna College', 'terna@test.com', NULL, '$2y$10$DOBqwlF5GByNjor6KytDve7tePfKWaOiRgeKWHdhT2Gx.aEbGXkH6', NULL, '2020-09-09 08:33:33', '2020-09-09 08:33:33'),
(9, 'UI update', 'uiupdate@test.com', NULL, '$2y$10$TlBPpj723DoeH3F5UCiK6eUjWvowwQIg5MaZxLpzfY7ShCkJZvlCa', NULL, '2020-09-10 09:43:02', '2020-09-10 09:43:02'),
(10, 'Arun Dhaneshwar', 'a4appleindia@gmail.com', NULL, '$2y$10$yyVuELaK4KmUVXm66z38pOM21pfg4Cp2ZP81GDWKWWWlBa3ucM0aS', 'UlYpnQyJMuTg8CCcCIQUFhqCO2P88veXFzkaUwpzwbdPZTGxnhbijgXIWNYs', '2020-09-10 09:51:08', '2020-09-10 09:51:08'),
(11, 'Ashutosh  Sathe', 'ashutosh@pegsconsulting.com', NULL, '$2y$10$LcD8XWX0ivUXCEpTEZkq5ubC8JUmBr2bat6bQ89pAsjx6mL12o4hG', NULL, '2020-09-10 09:58:57', '2020-09-10 09:58:57'),
(12, 'Bharati Parab', 'admin@ko-aaham.com', NULL, '$2y$10$XuD9ZEkHuSve1KaP5OgFs.kbju16M0JZ0nNRcjXjTSbBjIQ4RG3tC', NULL, '2020-09-17 10:39:51', '2020-09-17 10:39:51'),
(13, 'Jatin Mehta', 'jatin@abacusinfotech.net', NULL, '$2y$10$SrGyO7hg8lE.0akR9EG/Teiefj/FIqpecqwXrsTSmLAHf8T8mB3sa', NULL, '2020-09-25 14:01:01', '2020-09-25 14:01:01'),
(14, 'Shailendra', 'shailender@rtesfiltrationsystems.co.in', NULL, '$2y$10$H2B6VrWbN/C2TePGNWlhwuqxmnh.C5JL4e9YwRdNoibFWmV3cDNO2', NULL, '2020-10-03 15:23:42', '2020-10-03 15:23:42'),
(15, 'Admin Shrinathji Mandir', 'info@nathdwaratemple.org', NULL, '$2y$10$H3Howb4eks3YTps3qE/X7OEzYSyqQOF4iygLiGsRBDm2x4LaawYSe', NULL, '2020-10-14 14:42:29', '2020-10-14 14:42:29'),
(16, 'DemoSA', 'demosa@ko-aaham.com', NULL, '$2y$10$9wYjJJCLLSKVn7YJJ7l9ZO9uIHVtqWlxRKRMKoQoQ24YE13c/KS4e', NULL, '2020-11-25 21:41:18', '2020-11-25 21:41:18');

-- --------------------------------------------------------

--
-- Stand-in structure for view `vlocdev`
-- (See below for the actual view)
--
CREATE TABLE `vlocdev` (
`name` varchar(255)
,`pincode` varchar(6)
,`city` varchar(255)
,`taluka` varchar(255)
,`district` varchar(255)
,`state` varchar(255)
,`locactive` tinyint(1)
,`created_at` timestamp
,`deviceid` bigint(20)
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

CREATE VIEW `vlocdev`  AS  select `l`.`name` AS `name`,`l`.`pincode` AS `pincode`,`l`.`city` AS `city`,`l`.`taluka` AS `taluka`,`l`.`district` AS `district`,`l`.`state` AS `state`,`l`.`isactive` AS `locactive`,`l`.`created_at` AS `created_at`,`lld`.`deviceid` AS `deviceid`,`lld`.`isactive` AS `linkactive`,`d`.`serial_no` AS `serial_no`,`d`.`isactive` AS `devactive`,`l`.`smsnotification` AS `smsnotification` from ((`location` `l` join `LinkLocDev` `lld` on((`l`.`id` = `lld`.`locationid`))) join `device` `d` on((`lld`.`deviceid` = `d`.`id`))) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bedMaster`
--
ALTER TABLE `bedMaster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `device`
--
ALTER TABLE `device`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `iotdata`
--
ALTER TABLE `iotdata`
  ADD PRIMARY KEY (`id`);

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
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `LinkLocDev`
--
ALTER TABLE `LinkLocDev`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `linklocusers`
--
ALTER TABLE `linklocusers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`id`);

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
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `device`
--
ALTER TABLE `device`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `iotdata`
--
ALTER TABLE `iotdata`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19628;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `linklocusers`
--
ALTER TABLE `linklocusers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reguser`
--
ALTER TABLE `reguser`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

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
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
