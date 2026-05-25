-- =========================================================================
-- ADVANCED PAYMENT CONFIRMATION SYSTEM DATABASE SCHEMA
-- Vehicle Breakdown Assistance Management System
-- =========================================================================

-- ========== ALTER EXISTING TABLES ==========

-- Modify tblbook table to include payment and service information
ALTER TABLE `tblbook` ADD COLUMN `PaymentStatus` varchar(50) DEFAULT 'Pending' AFTER `Status`;
ALTER TABLE `tblbook` ADD COLUMN `ServiceType` varchar(100) DEFAULT NULL AFTER `PaymentStatus`;
ALTER TABLE `tblbook` ADD COLUMN `AdvancePaymentAmount` decimal(10,2) DEFAULT 0.00 AFTER `ServiceType`;
ALTER TABLE `tblbook` ADD COLUMN `TotalAmount` decimal(10,2) DEFAULT 0.00 AFTER `AdvancePaymentAmount`;
ALTER TABLE `tblbook` ADD COLUMN `AdminApprovalDate` timestamp NULL AFTER `UpdationDate`;
ALTER TABLE `tblbook` ADD COLUMN `RejectionReason` varchar(500) DEFAULT NULL AFTER `AdminApprovalDate`;
ALTER TABLE `tblbook` ADD COLUMN `AssignedServiceProvider` varchar(200) DEFAULT NULL AFTER `AssignTo`;
ALTER TABLE `tblbook` ADD COLUMN `BookingStatusFlow` enum('Payment Pending','Payment Confirmed','Pending Admin Approval','Confirmed','On The Way','Completed','Rejected','Refunded') DEFAULT 'Payment Pending' AFTER `AssignedServiceProvider`;

-- ========== CREATE NEW TABLES ==========

-- =========================================================
-- Table: tblpayments
-- Purpose: Store payment transaction details
-- =========================================================
CREATE TABLE IF NOT EXISTS `tblpayments` (
  `PaymentID` int(10) NOT NULL AUTO_INCREMENT,
  `BookingNumber` int(10) NOT NULL,
  `UserID` int(10) DEFAULT NULL,
  `PaymentMethod` varchar(50) DEFAULT 'Credit Card' COMMENT 'Credit Card, Debit Card, Net Banking, Wallet, UPI',
  `TransactionID` varchar(100) UNIQUE DEFAULT NULL COMMENT 'Gateway transaction reference',
  `Amount` decimal(10,2) NOT NULL,
  `PaymentStatus` varchar(50) DEFAULT 'Pending' COMMENT 'Pending, Completed, Failed, Cancelled',
  `PaymentGateway` varchar(50) DEFAULT 'Razorpay' COMMENT 'Razorpay, PayPal, Stripe, etc.',
  `GatewayResponse` longtext DEFAULT NULL COMMENT 'Full API response from gateway',
  `PaymentDate` timestamp NULL DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PaymentID`),
  KEY `BookingNumber` (`BookingNumber`),
  KEY `PaymentStatus` (`PaymentStatus`),
  KEY `PaymentDate` (`PaymentDate`),
  FOREIGN KEY (`BookingNumber`) REFERENCES `tblbook`(`BookingNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- Table: tblrefunds
-- Purpose: Track refund transactions
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
  KEY `RefundStatus` (`RefundStatus`),
  FOREIGN KEY (`PaymentID`) REFERENCES `tblpayments`(`PaymentID`),
  FOREIGN KEY (`BookingNumber`) REFERENCES `tblbook`(`BookingNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- Table: tblserviceproviders
-- Purpose: Store service provider/towing partner information
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
-- Table: tblservicecharges
-- Purpose: Store pricing configuration
-- =========================================================
CREATE TABLE IF NOT EXISTS `tblservicecharges` (
  `ChargeID` int(10) NOT NULL AUTO_INCREMENT,
  `ServiceType` varchar(100) NOT NULL UNIQUE,
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
-- Table: tblextraservices
-- Purpose: Store optional service charges
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
  KEY `BookingNumber` (`BookingNumber`),
  FOREIGN KEY (`BookingNumber`) REFERENCES `tblbook`(`BookingNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- Table: tblpaymentnotifications
-- Purpose: Store notification logs
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
-- Table: tblauditlog
-- Purpose: Track admin actions for transparency
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
-- INSERT DEFAULT SERVICE CHARGES
-- =========================================================
INSERT INTO `tblservicecharges` (`ServiceType`, `BaseCharge`, `AdvancePaymentPercentage`, `PricePerKM`, `MinimumCharge`, `MaximumCharge`, `Description`) VALUES
('Towing', 500.00, 50, 15.00, 500.00, 5000.00, 'Vehicle towing service to nearest workshop'),
('Battery Jumpstart', 300.00, 50, 0.00, 300.00, 1000.00, 'Emergency battery jump start service'),
('Fuel Delivery', 200.00, 50, 5.00, 200.00, 2000.00, 'Emergency fuel delivery service'),
('Tire Replacement', 400.00, 50, 0.00, 400.00, 1500.00, 'Roadside tire repair/replacement service'),
('Local Ride Assistance', 250.00, 50, 8.00, 250.00, 3000.00, 'Ride assistance within city limits');

-- =========================================================
-- CREATE VIEWS FOR REPORTING
-- =========================================================

-- View: Payment Summary Report
CREATE OR REPLACE VIEW `vw_payment_summary` AS
SELECT 
  b.BookingNumber,
  b.Name as UserName,
  b.Email,
  b.PhoneNumber,
  b.ServiceType,
  b.AdvancePaymentAmount,
  b.TotalAmount,
  p.PaymentStatus,
  p.PaymentDate,
  b.Status as BookingStatus,
  b.BookingStatusFlow,
  CASE 
    WHEN b.BookingStatusFlow = 'Refunded' THEN 'Refunded'
    WHEN b.BookingStatusFlow = 'Rejected' THEN 'Rejected'
    WHEN b.Status = 'Completed' THEN 'Completed'
    ELSE 'Pending'
  END as CurrentStatus
FROM tblbook b
LEFT JOIN tblpayments p ON b.BookingNumber = p.BookingNumber
ORDER BY p.PaymentDate DESC;

-- View: Refund Status Report
CREATE OR REPLACE VIEW `vw_refund_status` AS
SELECT 
  r.RefundID,
  r.BookingNumber,
  b.Name as UserName,
  b.Email,
  r.RefundAmount,
  r.RefundStatus,
  r.InitiatedAt,
  r.CompletedAt,
  DATEDIFF(IFNULL(r.CompletedAt, NOW()), r.InitiatedAt) as DaysToComplete,
  r.ProcessedBy
FROM tblrefunds r
JOIN tblbook b ON r.BookingNumber = b.BookingNumber
ORDER BY r.InitiatedAt DESC;

-- View: Payment Performance Metrics
CREATE OR REPLACE VIEW `vw_payment_metrics` AS
SELECT 
  DATE(p.PaymentDate) as PaymentDate,
  COUNT(DISTINCT b.BookingNumber) as TotalBookings,
  SUM(CASE WHEN p.PaymentStatus = 'Completed' THEN 1 ELSE 0 END) as SuccessfulPayments,
  SUM(CASE WHEN p.PaymentStatus = 'Failed' THEN 1 ELSE 0 END) as FailedPayments,
  SUM(CASE WHEN p.PaymentStatus = 'Completed' THEN p.Amount ELSE 0 END) as TotalRevenue,
  AVG(CASE WHEN p.PaymentStatus = 'Completed' THEN p.Amount ELSE 0 END) as AvgPaymentAmount
FROM tblpayments p
JOIN tblbook b ON p.BookingNumber = b.BookingNumber
WHERE p.PaymentDate IS NOT NULL
GROUP BY DATE(p.PaymentDate)
ORDER BY PaymentDate DESC;

-- =========================================================================
-- INDEXES FOR PERFORMANCE OPTIMIZATION
-- =========================================================================

CREATE INDEX idx_booking_status_flow ON tblbook(BookingStatusFlow);
CREATE INDEX idx_payment_status_date ON tblpayments(PaymentStatus, PaymentDate);
CREATE INDEX idx_refund_status_date ON tblrefunds(RefundStatus, InitiatedAt);
CREATE INDEX idx_booking_payment ON tblbook(BookingNumber, PaymentStatus);

-- =========================================================================
-- END OF SCHEMA
-- =========================================================================
