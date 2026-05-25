-- =========================================================================
-- ADD MISSING PAYMENT SYSTEM TABLES
-- =========================================================================

SET FOREIGN_KEY_CHECKS=0;

-- =========================================================
-- Table: tblrefunds (Missing - Create it)
-- =========================================================
CREATE TABLE IF NOT EXISTS `tblrefunds` (
  `RefundID` int(10) NOT NULL AUTO_INCREMENT,
  `PaymentID` int(10) NOT NULL,
  `BookingNumber` int(10) NOT NULL,
  `RefundAmount` decimal(10,2) NOT NULL,
  `RefundReason` varchar(500) DEFAULT NULL,
  `RefundStatus` varchar(50) DEFAULT 'Initiated' COMMENT 'Initiated, Processing, Completed, Failed',
  `RefundTransactionID` varchar(100) DEFAULT NULL COMMENT 'Gateway refund reference',
  `ProcessedBy` varchar(200) DEFAULT NULL COMMENT 'Admin who initiated refund',
  `InitiatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CompletedAt` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`RefundID`),
  KEY `BookingNumber` (`BookingNumber`),
  KEY `PaymentID` (`PaymentID`),
  KEY `RefundStatus` (`RefundStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- Table: tblserviceproviders (Missing - Create it)
-- =========================================================
CREATE TABLE IF NOT EXISTS `tblserviceproviders` (
  `ProviderID` int(10) NOT NULL AUTO_INCREMENT,
  `ProviderName` varchar(200) NOT NULL,
  `ProviderType` varchar(100) DEFAULT NULL COMMENT 'Towing, Battery Jump, Fuel Delivery, Tire Replacement',
  `MobileNumber` bigint(10) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `Address` mediumtext DEFAULT NULL,
  `VehicleDetails` varchar(200) DEFAULT NULL,
  `RegistrationNumber` varchar(50) DEFAULT NULL,
  `LicenseNumber` varchar(50) DEFAULT NULL,
  `IsActive` tinyint(1) DEFAULT 1,
  `VerificationStatus` varchar(50) DEFAULT 'Pending' COMMENT 'Pending, Verified, Rejected',
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ProviderID`),
  KEY `ProviderType` (`ProviderType`),
  KEY `IsActive` (`IsActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- Table: tblservicecharges (Missing - Create it)
-- =========================================================
CREATE TABLE IF NOT EXISTS `tblservicecharges` (
  `ChargeID` int(10) NOT NULL AUTO_INCREMENT,
  `ServiceType` varchar(100) NOT NULL,
  `BaseCharge` decimal(10,2) NOT NULL COMMENT 'Fixed base charge',
  `AdvancePaymentPercentage` decimal(5,2) DEFAULT 50 COMMENT 'Percentage of total to be paid in advance',
  `PricePerKM` decimal(8,2) DEFAULT 0.00 COMMENT 'Additional charge per kilometer',
  `MinimumCharge` decimal(10,2) DEFAULT 0.00,
  `MaximumCharge` decimal(10,2) DEFAULT 0.00,
  `Description` varchar(500) DEFAULT NULL,
  `IsActive` tinyint(1) DEFAULT 1,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ChargeID`),
  KEY `ServiceType` (`ServiceType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- Table: tblextraservices (Missing - Create it)
-- =========================================================
CREATE TABLE IF NOT EXISTS `tblextraservices` (
  `ExtraServiceID` int(10) NOT NULL AUTO_INCREMENT,
  `BookingNumber` int(10) NOT NULL,
  `ServiceName` varchar(200) NOT NULL,
  `ServiceCharge` decimal(10,2) NOT NULL,
  `Quantity` int(3) DEFAULT 1,
  `TotalCharge` decimal(10,2) NOT NULL,
  `IsApplied` tinyint(1) DEFAULT 0,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ExtraServiceID`),
  KEY `BookingNumber` (`BookingNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- Table: tblpaymentnotifications (Missing - Create it)
-- =========================================================
CREATE TABLE IF NOT EXISTS `tblpaymentnotifications` (
  `NotificationID` int(10) NOT NULL AUTO_INCREMENT,
  `BookingNumber` int(10) NOT NULL,
  `UserEmail` varchar(200) DEFAULT NULL,
  `UserPhone` bigint(10) DEFAULT NULL,
  `NotificationType` varchar(100) DEFAULT NULL COMMENT 'Payment Initiated, Payment Success, Payment Failed, Refund Initiated, Refund Complete',
  `Subject` varchar(200) DEFAULT NULL,
  `Message` longtext DEFAULT NULL,
  `Status` varchar(50) DEFAULT 'Pending' COMMENT 'Pending, Sent, Failed',
  `SentAt` timestamp NULL DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`NotificationID`),
  KEY `BookingNumber` (`BookingNumber`),
  KEY `Status` (`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- Table: tblauditlog (Missing - Create it)
-- =========================================================
CREATE TABLE IF NOT EXISTS `tblauditlog` (
  `AuditID` bigint(20) NOT NULL AUTO_INCREMENT,
  `AdminID` int(10) DEFAULT NULL,
  `BookingNumber` int(10) DEFAULT NULL,
  `PaymentID` int(10) DEFAULT NULL,
  `Action` varchar(200) NOT NULL,
  `Description` longtext DEFAULT NULL,
  `OldValue` longtext DEFAULT NULL,
  `NewValue` longtext DEFAULT NULL,
  `IPAddress` varchar(45) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`AuditID`),
  KEY `AdminID` (`AdminID`),
  KEY `BookingNumber` (`BookingNumber`),
  KEY `Action` (`Action`),
  KEY `CreatedAt` (`CreatedAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- INSERT DEFAULT SERVICE CHARGES (if not exists)
-- =========================================================
INSERT IGNORE INTO `tblservicecharges` (`ServiceType`, `BaseCharge`, `AdvancePaymentPercentage`, `PricePerKM`, `MinimumCharge`, `MaximumCharge`, `Description`) VALUES
('Towing', 500.00, 50, 15.00, 500.00, 5000.00, 'Vehicle towing service to nearest workshop'),
('Battery Jumpstart', 300.00, 50, 0.00, 300.00, 1000.00, 'Emergency battery jump start service'),
('Fuel Delivery', 200.00, 50, 5.00, 200.00, 2000.00, 'Emergency fuel delivery service'),
('Tire Replacement', 400.00, 50, 0.00, 400.00, 1500.00, 'Roadside tire repair/replacement service'),
('Local Ride Assistance', 250.00, 50, 8.00, 250.00, 3000.00, 'Ride assistance within city limits');

-- =========================================================
-- CREATE INDEXES FOR PERFORMANCE
-- =========================================================
CREATE INDEX IF NOT EXISTS idx_booking_status_flow ON tblbook(BookingStatusFlow);
CREATE INDEX IF NOT EXISTS idx_payment_status_date ON tblpayments(status, payment_date);
CREATE INDEX IF NOT EXISTS idx_refund_status_date ON tblrefunds(RefundStatus, InitiatedAt);
CREATE INDEX IF NOT EXISTS idx_booking_payment ON tblpayments(booking_number, status);

SET FOREIGN_KEY_CHECKS=1;

-- =========================================================================
-- COMPLETE: All missing payment system tables have been created!
-- =========================================================================
