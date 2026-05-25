-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 09, 2026 at 06:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vehassitancemsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `service_ratings`
--

CREATE TABLE `service_ratings` (
  `rating_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `booking_number` int(11) NOT NULL,
  `driver_rating` int(11) DEFAULT NULL CHECK (`driver_rating` >= 1 and `driver_rating` <= 5),
  `service_rating` int(11) DEFAULT NULL CHECK (`service_rating` >= 1 and `service_rating` <= 5),
  `feedback` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_ratings`
--

INSERT INTO `service_ratings` (`rating_id`, `booking_id`, `booking_number`, `driver_rating`, `service_rating`, `feedback`, `submitted_at`) VALUES
(2, 19, 513479313, 4, 5, 'no', '2026-03-05 07:19:25'),
(3, 21, 137310292, 4, 5, 'had a very  good experience', '2026-03-09 08:32:00'),
(4, 41, 599444539, 5, 5, 'very fast', '2026-03-09 15:16:20');

-- --------------------------------------------------------

--
-- Table structure for table `tbladmin`
--

CREATE TABLE `tbladmin` (
  `ID` int(10) NOT NULL,
  `AdminName` varchar(120) DEFAULT NULL,
  `UserName` varchar(120) DEFAULT NULL,
  `MobileNumber` bigint(10) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `Password` varchar(200) DEFAULT NULL,
  `AdminRegdate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbladmin`
--

INSERT INTO `tbladmin` (`ID`, `AdminName`, `UserName`, `MobileNumber`, `Email`, `Password`, `AdminRegdate`) VALUES
(1, 'Admin', 'admin', 8979555557, 'admin@gmail.com', 'f925916e2754e5e03f75dd58a5733251', '2020-01-02 12:23:36');

-- --------------------------------------------------------

--
-- Table structure for table `tblbook`
--

CREATE TABLE `tblbook` (
  `ID` int(10) NOT NULL,
  `BookingNumber` int(10) DEFAULT NULL,
  `Name` varchar(200) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `PhoneNumber` bigint(10) DEFAULT NULL,
  `PickupLoc` varchar(200) DEFAULT NULL,
  `Destination` varchar(200) DEFAULT NULL,
  `PickupDate` varchar(200) DEFAULT NULL,
  `PickupTime` varchar(200) DEFAULT NULL,
  `VehicleType` varchar(200) DEFAULT NULL,
  `DateofRequest` timestamp NOT NULL DEFAULT current_timestamp(),
  `Remark` varchar(200) DEFAULT NULL,
  `Status` varchar(200) DEFAULT NULL,
  `PaymentStatus` varchar(50) DEFAULT 'Pending',
  `ServiceType` varchar(100) DEFAULT NULL,
  `AdvancePaymentAmount` decimal(10,2) DEFAULT 0.00,
  `TotalAmount` decimal(10,2) DEFAULT 0.00,
  `AssignTo` varchar(200) DEFAULT NULL,
  `AssignedServiceProvider` varchar(200) DEFAULT NULL,
  `BookingStatusFlow` enum('Payment Pending','Payment Confirmed','Pending Admin Approval','Confirmed','On The Way','Completed','Rejected','Refunded') DEFAULT 'Payment Pending',
  `UpdationDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `AdminApprovalDate` timestamp NULL DEFAULT NULL,
  `RejectionReason` varchar(500) DEFAULT NULL,
  `Latitude` decimal(10,8) DEFAULT NULL,
  `Longitude` decimal(11,8) DEFAULT NULL,
  `request_uuid` varchar(36) NOT NULL DEFAULT uuid() COMMENT 'Unique request identifier',
  `payment_status` enum('pending','paid','refunded','failed') NOT NULL DEFAULT 'pending' COMMENT 'Payment status',
  `advance_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Advance amount paid',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total service cost',
  `admin_review_status` enum('waiting','approved','rejected') NOT NULL DEFAULT 'waiting' COMMENT 'Admin review status',
  `admin_review_reason` text DEFAULT NULL COMMENT 'Admin review reason',
  `admin_review_by` int(11) DEFAULT NULL COMMENT 'Admin ID who reviewed',
  `admin_review_date` timestamp NULL DEFAULT NULL COMMENT 'Admin review timestamp',
  `payment_ip` varchar(45) DEFAULT NULL COMMENT 'IP address of payment',
  `payment_user_agent` text DEFAULT NULL COMMENT 'User agent of payment',
  `refund_id` varchar(100) DEFAULT NULL COMMENT 'Razorpay refund ID',
  `refund_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Refund amount',
  `refund_date` timestamp NULL DEFAULT NULL COMMENT 'Refund timestamp',
  `refund_reason` text DEFAULT NULL COMMENT 'Refund reason',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `razorpay_payment_id` varchar(100) DEFAULT NULL COMMENT 'Razorpay payment ID',
  `request_status` enum('Pending Admin Review','Approved','Rejected') NOT NULL DEFAULT 'Pending Admin Review' COMMENT 'Request approval status',
  `payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Advance payment amount',
  `payment_date` timestamp NULL DEFAULT NULL COMMENT 'Payment completion date'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblbook`
--

INSERT INTO `tblbook` (`ID`, `BookingNumber`, `Name`, `Email`, `PhoneNumber`, `PickupLoc`, `Destination`, `PickupDate`, `PickupTime`, `VehicleType`, `DateofRequest`, `Remark`, `Status`, `PaymentStatus`, `ServiceType`, `AdvancePaymentAmount`, `TotalAmount`, `AssignTo`, `AssignedServiceProvider`, `BookingStatusFlow`, `UpdationDate`, `AdminApprovalDate`, `RejectionReason`, `Latitude`, `Longitude`, `request_uuid`, `payment_status`, `advance_amount`, `total_amount`, `admin_review_status`, `admin_review_reason`, `admin_review_by`, `admin_review_date`, `payment_ip`, `payment_user_agent`, `refund_id`, `refund_amount`, `refund_date`, `refund_reason`, `created_at`, `updated_at`, `razorpay_payment_id`, `request_status`, `payment_amount`, `payment_date`) VALUES
(1, 816217717, 'kjh', 'jkjkh@gmail.com', 7897897, 'kjhk', 'kjhkj', '2021-08-27', '14:34', NULL, '2021-08-25 04:03:19', 'Address not sufficient', 'Rejected', 'Pending', NULL, 0.00, 0.00, '', NULL, 'Payment Pending', '2021-08-25 08:49:45', NULL, NULL, NULL, NULL, '4166255b-1bae-11f1-912c-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 11:51:06', '2026-03-09 11:51:06', NULL, 'Pending Admin Review', 0.00, NULL),
(18, 134628040, 'Afnan', 'afnannjr94@gmail.com', 8748093066, '12.448536, 75.984970', 'Mysore', '2026-12-10', '11:11', 'Bus/Truck', '2026-03-04 14:05:18', '5 min', 'On The Way', 'Pending', NULL, 0.00, 0.00, 'test123', NULL, 'Payment Pending', '2026-03-04 14:07:31', NULL, NULL, 12.44853624, 75.98496958, '41662774-1bae-11f1-912c-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 11:51:06', '2026-03-09 11:51:06', NULL, 'Pending Admin Review', 0.00, NULL),
(19, 513479313, 'karann', 'karan@gmail.com', 1234567899, '12.441687, 75.977037', 'mysore', '2026-11-11', '11:11', 'Car', '2026-03-05 07:11:16', 'done ', 'Completed', 'Pending', NULL, 0.00, 0.00, 'test123', NULL, 'Payment Pending', '2026-03-05 07:18:52', NULL, NULL, 12.44168699, 75.97703680, '416628e1-1bae-11f1-912c-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 11:51:06', '2026-03-09 11:51:06', NULL, 'Pending Admin Review', 0.00, NULL),
(20, 595685281, 'sufail', 'sufail@gmail.com', 1234567999, '12.441703, 75.977015', 'mysore', '2026-03-10', '12:12', 'Car', '2026-03-09 08:17:37', '.', 'Approved', 'Pending', NULL, 0.00, 0.00, '', NULL, 'Payment Pending', '2026-03-09 08:24:33', NULL, NULL, 12.44170349, 75.97701473, '416629ee-1bae-11f1-912c-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 11:51:06', '2026-03-09 11:51:06', NULL, 'Pending Admin Review', 0.00, NULL),
(21, 137310292, 'abd', 'abd@gmail.com', 1245678963, '12.441685, 75.977037', 'koppa', '2026-12-11', '12:12', 'Car', '2026-03-09 08:27:45', 'done', 'Completed', 'Pending', NULL, 0.00, 0.00, 'test123', NULL, 'Payment Pending', '2026-03-09 08:31:23', NULL, NULL, 12.44168549, 75.97703680, '41662afc-1bae-11f1-912c-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 11:51:06', '2026-03-09 11:51:06', NULL, 'Pending Admin Review', 0.00, NULL),
(22, 811655794, 'Afnan', 'afnannjr94@gmail.com', 6366198001, '12.448638, 75.984898', 'kopppa', '2026-03-09', '04:28', 'Car', '2026-03-09 10:58:58', NULL, NULL, 'Pending', NULL, 0.00, 0.00, NULL, NULL, 'Payment Pending', '2026-03-09 10:58:58', NULL, NULL, 12.44863779, 75.98489845, '41662bff-1bae-11f1-912c-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 11:51:06', '2026-03-09 11:51:06', NULL, 'Pending Admin Review', 0.00, NULL),
(23, 605938638, 'Afnan', 'afnannjr94@gmail.com', 9739882395, '12.448871, 75.984784', 'Mysore', '2026-03-09', '11:11', 'Car', '2026-03-09 11:00:48', 'vip. make it soon\r\n', 'Approved', 'Pending', NULL, 0.00, 0.00, '', NULL, 'Payment Pending', '2026-03-09 11:01:46', NULL, NULL, 12.44887070, 75.98478350, '41662cf9-1bae-11f1-912c-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 11:51:06', '2026-03-09 11:51:06', NULL, 'Pending Admin Review', 0.00, NULL),
(24, 338774349, 'Afnan', 'afnannjr94@gmail.com', 9739882395, '12.448612, 75.984907', 'mysore', '2026-03-09', '04:49', 'Car', '2026-03-09 11:19:30', NULL, NULL, 'Pending', NULL, 0.00, 0.00, NULL, NULL, 'Payment Pending', '2026-03-09 11:19:30', NULL, NULL, 12.44861204, 75.98490659, '41662e12-1bae-11f1-912c-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 11:51:06', '2026-03-09 11:51:06', NULL, 'Pending Admin Review', 0.00, NULL),
(25, 392728076, 'Afnan', 'afnannjr94@gmail.com', 1234567899, '12.448518, 75.985010', 'Mysore', '2026-03-06', '11:11', 'Car', '2026-03-09 12:03:02', NULL, NULL, 'Pending', NULL, 0.00, 0.00, NULL, NULL, 'Payment Pending', '2026-03-09 12:03:02', NULL, NULL, 12.44851798, 75.98500967, 'ec337a1f-1baf-11f1-912c-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 12:03:02', '2026-03-09 12:03:02', NULL, 'Pending Admin Review', 0.00, NULL),
(26, 261726487, 'Afnan', 'afnannjr94@gmail.com', 9739882395, '12.448640, 75.984875', 'Mysore', '2026-03-09', '11:11', 'Car', '2026-03-09 12:16:31', NULL, NULL, 'Pending', NULL, 0.00, 0.00, NULL, NULL, 'Payment Pending', '2026-03-09 12:16:31', NULL, NULL, 12.44864000, 75.98487500, 'ceccfe10-1bb1-11f1-912c-cc28aa169755', 'pending', 100.00, 500.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 12:16:31', '2026-03-09 12:16:31', NULL, 'Pending Admin Review', 0.00, NULL),
(27, 433639588, 'Afnan', 'afnannjr94@gmail.com', 9739882395, '12.448640, 75.984875', 'Mysore', '2026-03-09', '11:11', 'Car', '2026-03-09 12:16:37', NULL, NULL, 'Pending', NULL, 0.00, 0.00, NULL, NULL, 'Payment Pending', '2026-03-09 12:16:37', NULL, NULL, 0.00000000, 0.00000000, 'd2629f01-1bb1-11f1-912c-cc28aa169755', 'pending', 100.00, 500.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 12:16:37', '2026-03-09 12:16:37', NULL, 'Pending Admin Review', 0.00, NULL),
(28, 682639044, 'Afnan', 'afnannjr94@gmail.com', 9739882395, '12.448640, 75.984875', 'Mysore', '2026-03-09', '11:11', 'Car', '2026-03-09 12:16:44', NULL, NULL, 'Pending', NULL, 0.00, 0.00, NULL, NULL, 'Payment Pending', '2026-03-09 12:16:44', NULL, NULL, 0.00000000, 0.00000000, 'd69adc1f-1bb1-11f1-912c-cc28aa169755', 'pending', 100.00, 500.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 12:16:44', '2026-03-09 12:16:44', NULL, 'Pending Admin Review', 0.00, NULL),
(29, 896645797, 'Afnan', 'afnannjr94@gmail.com', 9739882395, '12.448640, 75.984875', 'Mysore', '2026-03-09', '11:11', 'Car', '2026-03-09 12:17:41', NULL, NULL, 'Pending', NULL, 0.00, 0.00, NULL, NULL, 'Payment Pending', '2026-03-09 12:17:41', NULL, NULL, 0.00000000, 0.00000000, 'f852e680-1bb1-11f1-912c-cc28aa169755', 'pending', 100.00, 500.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 12:17:41', '2026-03-09 12:17:41', NULL, 'Pending Admin Review', 0.00, NULL),
(30, 946893498, 'Afnan', 'afnannjr94@gmail.com', 1234567899, '12.448640, 75.984875', 'Mysore', '2026-03-06', '11:11', 'Bus/Truck', '2026-03-09 12:18:08', NULL, NULL, 'Pending', NULL, 0.00, 0.00, NULL, NULL, 'Payment Pending', '2026-03-09 12:18:08', NULL, NULL, 12.44864000, 75.98487500, '0877358b-1bb2-11f1-912c-cc28aa169755', 'pending', 160.00, 800.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 12:18:08', '2026-03-09 12:18:08', NULL, 'Pending Admin Review', 0.00, NULL),
(31, 288226724, 'Afnan', 'afnannjr94@gmail.com', 9739882395, '12.448640, 75.984875', 'Mysore', '2026-03-09', '11:11', 'Car', '2026-03-09 12:25:18', NULL, NULL, 'Pending', NULL, 0.00, 0.00, NULL, NULL, 'Payment Pending', '2026-03-09 12:25:18', NULL, NULL, 0.00000000, 0.00000000, '08e9b77f-1bb3-11f1-912c-cc28aa169755', 'pending', 100.00, 500.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 12:25:18', '2026-03-09 12:25:18', NULL, 'Pending Admin Review', 0.00, NULL),
(32, 817883925, 'Afnan', 'afnannjr94@gmail.com', 9739882395, '12.448531, 75.984951', 'Mysore', '2026-03-09', '11:11', 'Car', '2026-03-09 12:38:27', NULL, NULL, 'Pending', NULL, 0.00, 0.00, NULL, NULL, 'Payment Pending', '2026-03-09 12:38:27', NULL, NULL, 12.44853150, 75.98495100, 'debe269a-1bb4-11f1-912c-cc28aa169755', 'pending', 100.00, 500.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 12:38:27', '2026-03-09 12:38:27', NULL, 'Pending Admin Review', 0.00, NULL),
(33, 317810990, 'Afnan', 'afnannjr94@gmail.com', 9739882395, '12.448531, 75.984951', 'Mysore', '2026-03-09', '11:11', 'Car', '2026-03-09 12:39:42', NULL, NULL, 'Pending', NULL, 0.00, 0.00, NULL, NULL, 'Payment Pending', '2026-03-09 12:39:42', NULL, NULL, 0.00000000, 0.00000000, '0bf68f45-1bb5-11f1-912c-cc28aa169755', 'pending', 100.00, 500.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 12:39:42', '2026-03-09 12:39:42', NULL, 'Pending Admin Review', 0.00, NULL),
(35, 963135522, 'Afnan', 'afnannjr94@gmail.com', 9739882395, '12.448640, 75.984875', 'Mysore', '2026-03-09', '11:11', 'Car', '2026-03-09 13:53:05', 'go', 'Approved', 'Pending', NULL, 0.00, 0.00, '', NULL, 'Payment Pending', '2026-03-09 15:19:40', NULL, NULL, 0.00000000, 0.00000000, '4c04c43b-1bbf-11f1-8a97-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 13:53:05', '2026-03-09 15:19:40', NULL, 'Pending Admin Review', 0.00, NULL),
(36, 415328031, 'Afnan', 'afnannjr94@gmail.com', 9739882395, '12.448640, 75.984875', 'Mysore', '2026-03-06', '11:11', 'Car', '2026-03-09 13:59:24', 'kk', 'Approved', 'Pending', 'Towing', 0.00, NULL, 'test123', NULL, 'Payment Pending', '2026-03-09 14:12:08', NULL, NULL, 0.00000000, 0.00000000, '2dcb2b7e-1bc0-11f1-8a97-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 13:59:24', '2026-03-09 14:12:08', NULL, 'Pending Admin Review', 0.00, NULL),
(37, 422620984, 'Afnan', 'afnannjr94@gmail.com', 9739882395, '12.448640, 75.984875', 'Mysore', '2026-03-09', '11:11', 'Car', '2026-03-09 14:08:27', NULL, 'Approved', 'Paid', 'Towing', 0.00, NULL, NULL, NULL, 'Confirmed', '2026-03-09 14:11:22', '2026-03-09 14:11:22', NULL, 0.00000000, 0.00000000, '71ea2d72-1bc1-11f1-8a97-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 14:08:27', '2026-03-09 14:11:22', NULL, 'Pending Admin Review', 0.00, NULL),
(38, 349864695, 'Afnan', 'afnannjr94@gmail.com', 8748093066, '12.448638, 75.984898', 'Mysore', '2026-03-09', '11:11', 'Bus/Truck', '2026-03-09 14:18:12', 'done\r\n', 'Completed', 'Paid', 'Towing', 100.00, 0.00, 'test123', NULL, 'Payment Confirmed', '2026-03-09 14:21:43', NULL, NULL, 0.00000000, 0.00000000, 'ce3f19ea-1bc2-11f1-8a97-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 14:18:12', '2026-03-09 14:21:43', NULL, 'Pending Admin Review', 0.00, NULL),
(39, 286613713, 'payment', 'afnannjr94@gmail.com', 9739882395, '12.448640, 75.984875', 'Mysore', '2026-03-09', '11:11', 'Bus/Truck', '2026-03-09 14:23:12', NULL, 'Rejected', 'Paid', 'Towing', 100.00, 0.00, NULL, NULL, 'Rejected', '2026-03-09 14:24:15', '2026-03-09 14:24:15', 'no driver', 0.00000000, 0.00000000, '8166c073-1bc3-11f1-8a97-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 14:23:12', '2026-03-09 14:24:15', NULL, 'Pending Admin Review', 0.00, NULL),
(40, 229299596, 'Afnan', 'afnannjr94@gmail.com', 9739882395, '12.448640, 75.984875', 'Mysore', '2026-03-09', '11:11', 'Bus/Truck', '2026-03-09 14:30:40', 'done', 'Completed', 'Paid', 'Towing', 100.00, 0.00, 'Kamleshhh', NULL, 'Confirmed', '2026-03-09 15:06:16', NULL, NULL, 0.00000000, 0.00000000, '8c348ccf-1bc4-11f1-8a97-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 14:30:40', '2026-03-09 15:06:16', NULL, 'Pending Admin Review', 0.00, NULL),
(41, 599444539, 'jackon', 'afnannjr94@gmail.com', 6366198001, '12.448640, 75.984875', 'Mysore', '2026-03-09', '12:30', 'Bus/Truck', '2026-03-09 15:12:19', 'done\r\n', 'Completed', 'Paid', 'Towing', 100.00, 0.00, 'Kamleshhh', NULL, 'Confirmed', '2026-03-09 15:15:49', NULL, NULL, 0.00000000, 0.00000000, '5dfeb45f-1bca-11f1-8a97-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 15:12:19', '2026-03-09 15:15:49', NULL, 'Pending Admin Review', 0.00, NULL),
(42, 575494532, 'Afnan', 'afnannjr94@gmail.com', 9739882395, '12.448506, 75.984978', 'Mysore', '2026-03-10', '11:11', 'Bus/Truck', '2026-03-09 15:16:56', NULL, 'Rejected', 'Paid', 'Towing', 100.00, 0.00, NULL, NULL, 'Rejected', '2026-03-09 15:17:36', '2026-03-09 15:17:36', 'no driver\r\n', 12.44850600, 75.98497800, '03150505-1bcb-11f1-8a97-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 15:16:56', '2026-03-09 15:17:36', NULL, 'Pending Admin Review', 0.00, NULL),
(43, 368551813, 'AP', 'afnannjr94@gmail.com', 8748093066, '12.448536, 75.984970', 'Mysore', '2026-03-09', '11:11', '', '2026-03-09 16:35:55', 'done \r\n', 'Completed', 'Paid', 'Car Towing', 500.00, 0.00, 'Kamleshhh', NULL, 'Confirmed', '2026-03-09 16:39:23', NULL, NULL, 12.44853624, 75.98496958, '0b6f3490-1bd6-11f1-8a97-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 16:35:55', '2026-03-09 16:39:23', NULL, 'Pending Admin Review', 0.00, NULL),
(44, 553314489, 'Af', 'afnannjr94@gmail.com', 6366198001, '12.448638, 75.984898', 'Mysore', '2026-03-10', '11:11', '', '2026-03-09 16:55:22', 'done', 'Completed', 'Paid', 'Car Towing', 500.00, 0.00, 'Kamleshhh', NULL, 'Confirmed', '2026-03-09 16:56:54', NULL, NULL, 12.44863779, 75.98489845, 'c359fd0f-1bd8-11f1-8a97-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 16:55:22', '2026-03-09 16:56:54', NULL, 'Pending Admin Review', 0.00, NULL),
(45, 448128771, 'sj', 'afnannjr94@gmail.com', 9739882395, '12.448531, 75.984951', 'Mysore', '2026-03-09', '11:11', '', '2026-03-09 17:00:07', NULL, 'Approved', 'Paid', 'Car Towing', 500.00, 0.00, 'Kamleshhh', NULL, 'Confirmed', '2026-03-09 17:01:52', NULL, NULL, 12.44853150, 75.98495100, '6d5cfe85-1bd9-11f1-8a97-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 17:00:07', '2026-03-09 17:01:52', NULL, 'Pending Admin Review', 0.00, NULL),
(46, 785417651, 'Afnan', 'afnannjr94@gmail.com', 1234567890, '12.448536, 75.984970', 'kushalnagar', '2026-03-09', '11:11', '', '2026-03-09 17:31:06', NULL, NULL, 'Pending', 'Car Towing', 500.00, 0.00, NULL, NULL, 'Payment Pending', '2026-03-09 17:31:06', NULL, NULL, 12.44853624, 75.98496958, 'c132baf7-1bdd-11f1-8a97-cc28aa169755', 'pending', 0.00, 0.00, 'waiting', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, '2026-03-09 17:31:06', '2026-03-09 17:31:06', NULL, 'Pending Admin Review', 0.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbldriver`
--

CREATE TABLE `tbldriver` (
  `ID` int(10) NOT NULL,
  `DriverID` varchar(20) DEFAULT NULL,
  `Name` varchar(200) DEFAULT NULL,
  `MobileNumber` bigint(10) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `Address` mediumtext DEFAULT NULL,
  `Password` varchar(200) DEFAULT NULL,
  `JoiningDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbldriver`
--

INSERT INTO `tbldriver` (`ID`, `DriverID`, `Name`, `MobileNumber`, `Email`, `Address`, `Password`, `JoiningDate`) VALUES
(6, 'test123', 'John Dore', 1234567890, 'johndoe@gmail.com', 'New Delhi 110001', 'f925916e2754e5e03f75dd58a5733251', '2021-09-07 17:04:44'),
(9, 'kamleshhh69', 'Kamleshhh', 9972984915, 'kamleshsingh@gmail.com', 'Ajabpura bhopal MP', 'f925916e2754e5e03f75dd58a5733251', '2026-02-26 17:04:44');

-- --------------------------------------------------------

--
-- Table structure for table `tblpage`
--

CREATE TABLE `tblpage` (
  `ID` int(10) NOT NULL,
  `PageType` varchar(200) DEFAULT NULL,
  `PageTitle` mediumtext DEFAULT NULL,
  `PageDescription` mediumtext DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `MobileNumber` bigint(10) DEFAULT NULL,
  `UpdationDate` date DEFAULT NULL,
  `Timing` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblpage`
--

INSERT INTO `tblpage` (`ID`, `PageType`, `PageTitle`, `PageDescription`, `Email`, `MobileNumber`, `UpdationDate`, `Timing`) VALUES
(1, 'aboutus', 'About Us', 'offers the Roadside Assistance Package – a 24x7 emergency support provided in the event of any mechanical/electrical breakdown or traffic accident of the vehicle. ... If your car has met with a major accident and is immovable, we will help you to tow your car to the nearest workshop or legal authorities', NULL, NULL, NULL, ''),
(2, 'contactus', 'Contact Us', 'City Centre Complex, BM Rd, Kushalnagar, Karnataka 571234)', 'mdafnan7266@gmail.com', 6366198001, NULL, '10:30 am to 7:30 pm');

-- --------------------------------------------------------

--
-- Table structure for table `tblpayments`
--

CREATE TABLE `tblpayments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `booking_number` varchar(50) NOT NULL,
  `payment_uuid` varchar(36) NOT NULL DEFAULT uuid(),
  `razorpay_order_id` varchar(100) NOT NULL,
  `razorpay_payment_id` varchar(100) DEFAULT NULL,
  `razorpay_signature` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'INR',
  `status` enum('created','authorized','captured','refunded','partial_refund','failed') NOT NULL DEFAULT 'created',
  `payment_method` varchar(50) NOT NULL DEFAULT 'razorpay',
  `payment_ip` varchar(45) DEFAULT NULL,
  `payment_user_agent` text DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `refund_id` varchar(100) DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT 0.00,
  `refund_status` enum('none','pending','processed','failed') NOT NULL DEFAULT 'none',
  `refund_date` timestamp NULL DEFAULT NULL,
  `refund_reason` text DEFAULT NULL,
  `webhook_processed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblpayments`
--

INSERT INTO `tblpayments` (`id`, `booking_id`, `booking_number`, `payment_uuid`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`, `amount`, `currency`, `status`, `payment_method`, `payment_ip`, `payment_user_agent`, `payment_date`, `failure_reason`, `refund_id`, `refund_amount`, `refund_status`, `refund_date`, `refund_reason`, `webhook_processed`, `created_at`, `updated_at`) VALUES
(1, 0, '422620984', '760b1bf2-1bc1-11f1-8a97-cc28aa169755', '', NULL, NULL, 0.00, 'INR', 'captured', 'demo', NULL, NULL, NULL, NULL, NULL, 0.00, 'none', NULL, NULL, 0, '2026-03-09 14:08:34', '2026-03-09 14:08:34'),
(4, 0, '349864695', 'demo-349864695-1773065960', 'DEMO_20260309151920_99237', 'DEMOPAY_20260309151920_37821', NULL, 100.00, 'INR', 'authorized', 'demo', NULL, NULL, NULL, NULL, NULL, 0.00, 'none', NULL, NULL, 0, '2026-03-09 14:19:20', '2026-03-09 15:27:12'),
(5, 0, '286613713', 'demo-286613713-1773066198', 'DEMO_20260309152318_36626', 'DEMOPAY_20260309152318_85956', NULL, 100.00, 'INR', 'refunded', 'demo', NULL, NULL, NULL, NULL, NULL, 0.00, 'processed', NULL, NULL, 0, '2026-03-09 14:23:18', '2026-03-09 14:24:15'),
(6, 0, '229299596', 'demo-229299596-1773066646', 'DEMO_20260309153046_48566', 'DEMOPAY_20260309153046_45647', NULL, 100.00, 'INR', 'authorized', 'demo', NULL, NULL, NULL, NULL, NULL, 0.00, 'none', NULL, NULL, 0, '2026-03-09 14:30:46', '2026-03-09 14:31:28'),
(7, 0, '599444539', 'demo-599444539-1773069146', 'DEMO_20260309161226_68188', 'DEMOPAY_20260309161226_14553', NULL, 100.00, 'INR', 'authorized', 'demo', NULL, NULL, NULL, NULL, NULL, 0.00, 'none', NULL, NULL, 0, '2026-03-09 15:12:26', '2026-03-09 15:13:04'),
(8, 0, '575494532', 'demo-575494532-1773069422', 'DEMO_20260309161702_96759', 'DEMOPAY_20260309161702_37396', NULL, 100.00, 'INR', 'refunded', 'demo', NULL, NULL, NULL, NULL, NULL, 0.00, 'processed', NULL, NULL, 0, '2026-03-09 15:17:02', '2026-03-09 15:17:36'),
(9, 0, '368551813', 'demo-368551813-1773074163', 'DEMO_20260309173603_51401', 'DEMOPAY_20260309173603_91939', NULL, 500.00, 'INR', 'authorized', 'demo', NULL, NULL, NULL, NULL, NULL, 0.00, 'none', NULL, NULL, 0, '2026-03-09 16:36:03', '2026-03-09 16:36:38'),
(10, 0, '553314489', 'demo-553314489-1773075326', 'DEMO_20260309175526_83367', 'DEMOPAY_20260309175526_91699', NULL, 500.00, 'INR', 'authorized', 'demo', NULL, NULL, NULL, NULL, NULL, 0.00, 'none', NULL, NULL, 0, '2026-03-09 16:55:26', '2026-03-09 16:55:39'),
(11, 0, '448128771', 'demo-448128771-1773075611', 'DEMO_20260309180011_33557', 'DEMOPAY_20260309180011_18685', NULL, 500.00, 'INR', 'authorized', 'demo', NULL, NULL, NULL, NULL, NULL, 0.00, 'none', NULL, NULL, 0, '2026-03-09 17:00:11', '2026-03-09 17:01:26');

-- --------------------------------------------------------

--
-- Table structure for table `tblpayment_settings`
--

CREATE TABLE `tblpayment_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('string','number','boolean','json') NOT NULL DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `is_encrypted` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether value is encrypted',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='System configuration settings';

--
-- Dumping data for table `tblpayment_settings`
--

INSERT INTO `tblpayment_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_encrypted`, `created_at`, `updated_at`) VALUES
(1, 'razorpay_key_id', 'rzp_test_XXXXXXXXXXXX', 'string', 'Razorpay Key ID', 0, '2026-03-09 11:51:06', '2026-03-09 11:51:06'),
(2, 'razorpay_key_secret', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', 'string', 'Razorpay Key Secret', 0, '2026-03-09 11:51:06', '2026-03-09 11:51:06'),
(3, 'razorpay_webhook_secret', 'your_webhook_secret_here', 'string', 'Razorpay Webhook Secret', 0, '2026-03-09 11:51:06', '2026-03-09 11:51:06'),
(4, 'advance_amount_percentage', '20.00', 'number', 'Advance amount as percentage of total service cost', 0, '2026-03-09 11:51:06', '2026-03-09 11:51:06'),
(5, 'min_advance_amount', '100.00', 'number', 'Minimum advance amount in INR', 0, '2026-03-09 11:51:06', '2026-03-09 11:51:06'),
(6, 'max_advance_amount', '5000.00', 'number', 'Maximum advance amount in INR', 0, '2026-03-09 11:51:06', '2026-03-09 11:51:06'),
(7, 'payment_timeout_minutes', '15', 'number', 'Payment session timeout in minutes', 0, '2026-03-09 11:51:06', '2026-03-09 11:51:06'),
(8, 'auto_refund_enabled', '1', 'boolean', 'Enable automatic refund on rejection', 0, '2026-03-09 11:51:06', '2026-03-09 11:51:06'),
(9, 'max_refund_attempts', '3', 'number', 'Maximum refund retry attempts', 0, '2026-03-09 11:51:06', '2026-03-09 11:51:06'),
(10, 'rate_limit_per_minute', '10', 'number', 'API rate limit per minute per IP', 0, '2026-03-09 11:51:06', '2026-03-09 11:51:06'),
(11, 'duplicate_payment_window', '300', 'number', 'Duplicate payment check window in seconds', 0, '2026-03-09 11:51:06', '2026-03-09 11:51:06'),
(12, 'webhook_retry_attempts', '5', 'number', 'Maximum webhook retry attempts', 0, '2026-03-09 11:51:06', '2026-03-09 11:51:06'),
(13, 'security_log_retention_days', '90', 'number', 'Security log retention period in days', 0, '2026-03-09 11:51:06', '2026-03-09 11:51:06'),
(27, 'advance_percentage', '20', 'string', 'Advance payment percentage (20%)', 0, '2026-03-09 11:55:52', '2026-03-09 11:55:52');

-- --------------------------------------------------------

--
-- Table structure for table `tblrefunds`
--

CREATE TABLE `tblrefunds` (
  `RefundID` int(10) NOT NULL,
  `PaymentID` int(10) NOT NULL,
  `BookingNumber` int(10) NOT NULL,
  `RefundAmount` decimal(10,2) NOT NULL,
  `RefundReason` varchar(500) DEFAULT NULL,
  `RefundStatus` varchar(50) DEFAULT 'Initiated' COMMENT 'Initiated, Processing, Completed, Failed',
  `RefundTransactionID` varchar(100) DEFAULT NULL COMMENT 'Gateway refund reference',
  `ProcessedBy` varchar(200) DEFAULT NULL COMMENT 'Admin who initiated refund',
  `InitiatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `CompletedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblrefunds`
--

INSERT INTO `tblrefunds` (`RefundID`, `PaymentID`, `BookingNumber`, `RefundAmount`, `RefundReason`, `RefundStatus`, `RefundTransactionID`, `ProcessedBy`, `InitiatedAt`, `CompletedAt`) VALUES
(1, 5, 286613713, 100.00, 'no driver', 'Completed', NULL, 'Admin', '2026-03-09 14:24:15', '2026-03-09 14:24:15'),
(2, 8, 575494532, 100.00, 'no driver\r\n', 'Completed', NULL, 'Admin', '2026-03-09 15:17:36', '2026-03-09 15:17:36');

-- --------------------------------------------------------

--
-- Table structure for table `tblservicecharges`
--

CREATE TABLE `tblservicecharges` (
  `ChargeID` int(10) NOT NULL,
  `ServiceType` varchar(100) NOT NULL,
  `BaseCharge` decimal(10,2) NOT NULL COMMENT 'Fixed base charge',
  `AdvancePaymentPercentage` decimal(5,2) DEFAULT 50.00 COMMENT 'Percentage of total to be paid in advance',
  `PricePerKM` decimal(8,2) DEFAULT 0.00 COMMENT 'Additional charge per kilometer',
  `MinimumCharge` decimal(10,2) DEFAULT 0.00,
  `MaximumCharge` decimal(10,2) DEFAULT 0.00,
  `Description` varchar(500) DEFAULT NULL,
  `IsActive` tinyint(1) DEFAULT 1,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblservicecharges`
--

INSERT INTO `tblservicecharges` (`ChargeID`, `ServiceType`, `BaseCharge`, `AdvancePaymentPercentage`, `PricePerKM`, `MinimumCharge`, `MaximumCharge`, `Description`, `IsActive`, `CreatedAt`, `UpdatedAt`) VALUES
(11, 'Bike Towing', 1000.00, 50.00, 0.00, 0.00, 0.00, 'Towing service for bikes', 1, '2026-03-09 16:30:12', '2026-03-09 16:30:12'),
(12, 'Car Towing', 3000.00, 50.00, 0.00, 0.00, 0.00, 'Towing service for cars', 1, '2026-03-09 16:30:12', '2026-03-09 16:30:12'),
(13, 'Truck Towing', 6000.00, 50.00, 0.00, 0.00, 0.00, 'Towing service for trucks', 1, '2026-03-09 16:30:12', '2026-03-09 16:30:12');

-- --------------------------------------------------------

--
-- Table structure for table `tbltracking`
--

CREATE TABLE `tbltracking` (
  `ID` int(10) NOT NULL,
  `BookingNumber` int(10) DEFAULT NULL,
  `Remark` varchar(200) DEFAULT NULL,
  `Status` varchar(200) DEFAULT NULL,
  `UpdationDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbltracking`
--

INSERT INTO `tbltracking` (`ID`, `BookingNumber`, `Remark`, `Status`, `UpdationDate`) VALUES
(1, 407268812, 'We assist you', 'Approved', '2021-08-25 08:43:14'),
(2, 816217717, 'Address not sufficient', 'Rejected', '2021-08-25 08:49:45'),
(3, 407268812, 'On the way', 'On The Way', '2021-08-26 05:01:51'),
(4, 407268812, 'Task completed', 'Completed', '2021-08-26 10:54:16'),
(5, 697868423, 'Approved', 'Approved', '2021-08-31 03:53:08'),
(6, 697868423, 'I am on the way', 'On The Way', '2021-08-31 03:54:08'),
(7, 475299702, 'Your request has been approved', 'Approved', '2021-09-03 11:26:29'),
(8, 475299702, 'On the way', 'On The Way', '2021-09-03 12:49:35'),
(9, 475299702, 'Completed', 'Completed', '2021-09-03 12:52:56'),
(10, 573248326, 'Task Assigned', 'Approved', '2021-09-07 17:05:25'),
(11, 573248326, 'On the way for task', 'On The Way', '2021-09-07 17:06:30'),
(12, 573248326, 'Task Completed', 'Completed', '2021-09-07 17:07:10'),
(13, 276469640, 'no as such', 'Approved', '2026-02-13 06:44:11'),
(14, 276469640, 'on my way ', 'On The Way', '2026-02-13 06:46:12'),
(15, 276469640, 'the ', 'On The Way', '2026-02-13 06:51:01'),
(16, 276469640, 'not posssible', 'Completed', '2026-02-13 06:53:23'),
(17, 226241261, 'np', 'Approved', '2026-03-03 09:00:52'),
(18, 340030120, '222', 'Approved', '2026-03-03 10:35:05'),
(19, 702213687, 'vip customer', 'Approved', '2026-03-04 07:31:58'),
(20, 702213687, 'i will be there in 2 min', 'On The Way', '2026-03-04 07:33:29'),
(21, 340030120, 'done\r\n', 'Completed', '2026-03-04 07:34:20'),
(22, 558704270, 'Request submitted successfully. Awaiting admin review.', NULL, '2026-03-04 07:43:14'),
(23, 558704270, 'hgg', 'Approved', '2026-03-04 07:44:41'),
(24, 558704270, 'drrdetetr', 'On The Way', '2026-03-04 07:45:47'),
(25, 558704270, 'gfg', 'Completed', '2026-03-04 07:47:02'),
(26, 379370951, 'Request submitted successfully. Awaiting admin review.', NULL, '2026-03-04 12:42:21'),
(27, 134628040, 'Request submitted successfully. Awaiting admin review.', NULL, '2026-03-04 14:05:18'),
(28, 134628040, 'i am coming in 5 min', 'Approved', '2026-03-04 14:06:46'),
(29, 134628040, '5 min', 'On The Way', '2026-03-04 14:07:31'),
(30, 513479313, 'Request submitted successfully. Awaiting admin review.', NULL, '2026-03-05 07:11:16'),
(31, 513479313, 'vip', 'Approved', '2026-03-05 07:14:58'),
(32, 513479313, 'ill reach in 10mins', 'On The Way', '2026-03-05 07:16:13'),
(33, 513479313, 'done ', 'Completed', '2026-03-05 07:18:52'),
(34, 595685281, 'Request submitted successfully. Awaiting admin review.', NULL, '2026-03-09 08:17:37'),
(35, 595685281, '.', 'Approved', '2026-03-09 08:24:33'),
(36, 137310292, 'Request submitted successfully. Awaiting admin review.', NULL, '2026-03-09 08:27:45'),
(37, 137310292, 'vip customer', 'Approved', '2026-03-09 08:28:33'),
(38, 137310292, 'ill come in 10mins', 'On The Way', '2026-03-09 08:30:14'),
(39, 137310292, 'done', 'Completed', '2026-03-09 08:31:23'),
(40, 811655794, 'Request submitted successfully. Awaiting admin review.', NULL, '2026-03-09 10:58:59'),
(41, 605938638, 'Request submitted successfully. Awaiting admin review.', NULL, '2026-03-09 11:00:48'),
(42, 605938638, 'vip. make it soon\r\n', 'Approved', '2026-03-09 11:01:46'),
(43, 338774349, 'Request submitted successfully. Awaiting admin review.', NULL, '2026-03-09 11:19:30'),
(44, 392728076, 'Request submitted successfully. Awaiting admin review.', NULL, '2026-03-09 12:03:02'),
(45, 963135522, 'Request submitted successfully. Awaiting admin review.', NULL, '2026-03-09 13:53:05'),
(46, 415328031, 'kk', 'Approved', '2026-03-09 14:12:08'),
(47, 349864695, 'ok\r\n', 'Approved', '2026-03-09 14:20:56'),
(48, 349864695, 'done\r\n', 'Completed', '2026-03-09 14:21:43'),
(49, 229299596, 'done', 'Completed', '2026-03-09 15:06:16'),
(50, 599444539, 'coming \r\n', 'On The Way', '2026-03-09 15:14:42'),
(51, 599444539, 'done\r\n', 'Completed', '2026-03-09 15:15:49'),
(52, 963135522, 'go', 'Approved', '2026-03-09 15:19:40'),
(53, 368551813, 'coming', 'On The Way', '2026-03-09 16:38:16'),
(54, 368551813, 'done \r\n', 'Completed', '2026-03-09 16:39:23'),
(55, 553314489, 'done', 'Completed', '2026-03-09 16:56:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `service_ratings`
--
ALTER TABLE `service_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `unique_booking` (`booking_number`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `idx_booking_number` (`booking_number`),
  ADD KEY `idx_submitted_at` (`submitted_at`);

--
-- Indexes for table `tbladmin`
--
ALTER TABLE `tbladmin`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblbook`
--
ALTER TABLE `tblbook`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `BookingNumber` (`BookingNumber`),
  ADD KEY `idx_booking_payment` (`Status`),
  ADD KEY `idx_request_uuid` (`request_uuid`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_admin_review_status` (`admin_review_status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_booking_number_status` (`BookingNumber`,`payment_status`),
  ADD KEY `idx_composite_booking_status` (`payment_status`,`admin_review_status`,`created_at`),
  ADD KEY `idx_booking_status_flow` (`BookingStatusFlow`);

--
-- Indexes for table `tbldriver`
--
ALTER TABLE `tbldriver`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblpage`
--
ALTER TABLE `tblpage`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblpayments`
--
ALTER TABLE `tblpayments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_payment_uuid` (`payment_uuid`),
  ADD UNIQUE KEY `uk_razorpay_order_id` (`razorpay_order_id`),
  ADD UNIQUE KEY `uk_razorpay_payment_id` (`razorpay_payment_id`),
  ADD KEY `idx_booking_id` (`booking_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_date` (`payment_date`),
  ADD KEY `idx_payment_status_date` (`status`,`payment_date`),
  ADD KEY `idx_booking_payment` (`booking_number`,`status`);

--
-- Indexes for table `tblpayment_settings`
--
ALTER TABLE `tblpayment_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_setting_key` (`setting_key`),
  ADD KEY `idx_setting_type` (`setting_type`);

--
-- Indexes for table `tblrefunds`
--
ALTER TABLE `tblrefunds`
  ADD PRIMARY KEY (`RefundID`),
  ADD KEY `BookingNumber` (`BookingNumber`),
  ADD KEY `PaymentID` (`PaymentID`),
  ADD KEY `RefundStatus` (`RefundStatus`),
  ADD KEY `idx_refund_status_date` (`RefundStatus`,`InitiatedAt`);

--
-- Indexes for table `tblservicecharges`
--
ALTER TABLE `tblservicecharges`
  ADD PRIMARY KEY (`ChargeID`),
  ADD KEY `ServiceType` (`ServiceType`);

--
-- Indexes for table `tbltracking`
--
ALTER TABLE `tbltracking`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `service_ratings`
--
ALTER TABLE `service_ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbladmin`
--
ALTER TABLE `tbladmin`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tblbook`
--
ALTER TABLE `tblbook`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `tbldriver`
--
ALTER TABLE `tbldriver`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tblpage`
--
ALTER TABLE `tblpage`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tblpayments`
--
ALTER TABLE `tblpayments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tblpayment_settings`
--
ALTER TABLE `tblpayment_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `tblrefunds`
--
ALTER TABLE `tblrefunds`
  MODIFY `RefundID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tblservicecharges`
--
ALTER TABLE `tblservicecharges`
  MODIFY `ChargeID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tbltracking`
--
ALTER TABLE `tbltracking`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `service_ratings`
--
ALTER TABLE `service_ratings`
  ADD CONSTRAINT `service_ratings_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `tblbook` (`ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
