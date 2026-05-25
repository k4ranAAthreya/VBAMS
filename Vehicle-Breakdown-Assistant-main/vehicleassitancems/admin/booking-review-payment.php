<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
include('../includes/payment-config.php');

if (strlen($_SESSION['vamsaid'])==0) {
    header('location:logout.php');
} else {

$editid = isset($_GET['editid']) ? intval($_GET['editid']) : null;
$bookid = isset($_GET['bookid']) ? intval($_GET['bookid']) : null;

// Handle approval
if (isset($_POST['approve'])) {
    $driverid = isset($_POST['driverid']) ? sanitizeInput($_POST['driverid']) : null;
    
    if (!$driverid) {
        $approveMsg = "Please select a service provider";
    } else {
        try {
            // Update booking status
            $sql_update = "UPDATE tblbook SET Status = 'Approved', BookingStatusFlow = 'Confirmed', AssignTo = :driver_id, AdminApprovalDate = NOW()
                           WHERE ID = :id AND BookingNumber = :booking_number";
            
            $query_update = $dbh->prepare($sql_update);
            $query_update->bindParam(':driver_id', $driverid, PDO::PARAM_STR);
            $query_update->bindParam(':id', $editid, PDO::PARAM_INT);
            $query_update->bindParam(':booking_number', $bookid, PDO::PARAM_INT);
            $query_update->execute();
            
            // Create tracking record
            $sql_track = "INSERT INTO tbltracking(BookingNumber, Status, Remark, UpdationDate)
                         VALUES(:booking_number, 'Approved', 'Request approved by admin. Service provider assigned.', NOW())";
            $query_track = $dbh->prepare($sql_track);
            $query_track->bindParam(':booking_number', $bookid, PDO::PARAM_INT);
            $query_track->execute();
            
            // Log audit action
            logAuditAction($_SESSION['vamsaid'], $bookid, null, 'Booking Approved', "Booking approved and assigned to driver: {$driverid}");
            
            $approveMsg = "Booking approved successfully!";
            
        } catch (Exception $e) {
            $approveMsg = "Error: " . $e->getMessage();
        }
    }
}

// Handle rejection and refund
if (isset($_POST['reject'])) {
    $rejectionReason = isset($_POST['rejection_reason']) ? sanitizeInput($_POST['rejection_reason']) : 'No reason provided';
    
    try {
        // Update booking status
        $sql_update = "UPDATE tblbook SET Status = 'Rejected', BookingStatusFlow = 'Rejected', RejectionReason = :reason, AdminApprovalDate = NOW()
                       WHERE ID = :id AND BookingNumber = :booking_number";
        
        $query_update = $dbh->prepare($sql_update);
        $query_update->bindParam(':reason', $rejectionReason, PDO::PARAM_STR);
        $query_update->bindParam(':id', $editid, PDO::PARAM_INT);
        $query_update->bindParam(':booking_number', $bookid, PDO::PARAM_INT);
        $query_update->execute();
        
        // Get payment details for refund
        $sql_payment = "SELECT * FROM tblpayments WHERE BookingNumber = :booking_number AND PaymentStatus = 'Completed'";
        $query_payment = $dbh->prepare($sql_payment);
        $query_payment->bindParam(':booking_number', $bookid, PDO::PARAM_INT);
        $query_payment->execute();
        
        if ($query_payment->rowCount() > 0) {
            $payment = $query_payment->fetch(PDO::FETCH_OBJ);
            
            // Process refund via API
            $_POST['booking_number'] = $bookid;
            $_POST['refund_reason'] = $rejectionReason . ' (Automatic refund on rejection)';
            
            // Initiate refund
            $refundResponse = processRefundRequest($payment->PaymentID, $bookid, $payment->Amount, $rejectionReason);
        }
        
        // Create tracking record
        $sql_track = "INSERT INTO tbltracking(BookingNumber, Status, Remark, UpdationDate)
                     VALUES(:booking_number, 'Rejected', :reason, NOW())";
        $query_track = $dbh->prepare($sql_track);
        $query_track->bindParam(':booking_number', $bookid, PDO::PARAM_INT);
        $query_track->bindParam(':reason', $rejectionReason, PDO::PARAM_STR);
        $query_track->execute();
        
        // Log audit action
        logAuditAction($_SESSION['vamsaid'], $bookid, null, 'Booking Rejected', "Booking rejected. Reason: {$rejectionReason}. Refund initiated.");
        
        $approveMsg = "Booking rejected and refund has been initiated!";
        
    } catch (Exception $e) {
        $approveMsg = "Error: " . $e->getMessage();
    }
}

// Fetch booking details
$sql = "SELECT b.*, p.PaymentStatus, p.Amount as PaymentAmount, p.PaymentDate, p.TransactionID
        FROM tblbook b
        LEFT JOIN tblpayments p ON b.BookingNumber = p.BookingNumber
        WHERE b.ID = :id AND b.BookingNumber = :booking_number";

$query = $dbh->prepare($sql);
$query->bindParam(':id', $editid, PDO::PARAM_INT);
$query->bindParam(':booking_number', $bookid, PDO::PARAM_INT);
$query->execute();

$booking = $query->fetch(PDO::FETCH_OBJ);

if (!$booking) {
    echo '<script>alert("Booking not found"); window.history.back();</script>';
    exit;
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function logAuditAction($adminID, $bookingNumber, $paymentID, $action, $description) {
    global $dbh;
    
    try {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        
        $sql = "INSERT INTO tblauditlog (AdminID, BookingNumber, PaymentID, Action, Description, IPAddress)
                VALUES (:admin_id, :booking_number, :payment_id, :action, :description, :ip_address)";
        
        $query = $dbh->prepare($sql);
        $query->bindParam(':admin_id', $adminID, PDO::PARAM_INT);
        $query->bindParam(':booking_number', $bookingNumber, PDO::PARAM_INT);
        $query->bindParam(':payment_id', $paymentID, PDO::PARAM_INT);
        $query->bindParam(':action', $action, PDO::PARAM_STR);
        $query->bindParam(':description', $description, PDO::PARAM_STR);
        $query->bindParam(':ip_address', $ipAddress, PDO::PARAM_STR);
        
        return $query->execute();
    } catch (Exception $e) {
        return false;
    }
}

function processRefundRequest($paymentID, $bookingNumber, $amount, $reason) {
    global $dbh;
    
    try {
        // Create refund record
        $sql = "INSERT INTO tblrefunds (PaymentID, BookingNumber, RefundAmount, RefundReason, RefundStatus, ProcessedBy)
                VALUES (:payment_id, :booking_number, :amount, :reason, 'Initiated', :admin_id)";
        
        $query = $dbh->prepare($sql);
        $query->bindParam(':payment_id', $paymentID, PDO::PARAM_INT);
        $query->bindParam(':booking_number', $bookingNumber, PDO::PARAM_INT);
        $query->bindParam(':amount', $amount, PDO::PARAM_STR);
        $query->bindParam(':reason', $reason, PDO::PARAM_STR);
        $query->bindParam(':admin_id', $_SESSION['vamsaid'], PDO::PARAM_INT);
        
        return $query->execute();
    } catch (Exception $e) {
        return false;
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <title>Booking Review with Payment Details</title>
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .booking-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .section-title { border-bottom: 3px solid #2196F3; padding-bottom: 10px; margin: 20px 0 15px 0; font-weight: bold; color: #333; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .detail-label { font-weight: 500; color: #555; }
        .detail-value { color: #333; }
        .payment-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-success { background: #4CAF50; color: white; }
        .badge-pending { background: #FF9800; color: white; }
        .badge-failed { background: #f44336; color: white; }
        .payment-info { background: #e3f2fd; border-left: 4px solid #2196F3; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .warning-info { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .success-info { background: #d4edda; border-left: 4px solid #4CAF50; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .action-section { margin: 30px 0; padding: 20px; background: #f9f9f9; border-radius: 5px; }
        .action-btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 3px; cursor: pointer; font-weight: bold; }
        .btn-approve { background: #4CAF50; color: white; }
        .btn-approve:hover { background: #45a049; }
        .btn-reject { background: #f44336; color: white; }
        .btn-reject:hover { background: #da190b; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 500; margin-bottom: 5px; }
        .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
    </style>
</head>
<body class="theme-indigo">
    <?php include_once('includes/header.php');?>
    <div class="main_content">
        <?php include_once('includes/sidebar.php');?>
        <div class="page">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="javascript:void(0);">Booking Review & Payment Details</a>
            </nav>
            
            <div class="container-fluid">
                <?php if (isset($approveMsg)) { ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($approveMsg); ?>
                        <button type="button" class="close" data-dismiss="alert">×</button>
                    </div>
                <?php } ?>
                
                <!-- Booking Details -->
                <div class="booking-card">
                    <div class="section-title"><i class="fa fa-info-circle"></i> Booking Information</div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Booking Number:</span>
                        <span class="detail-value"><strong><?php echo htmlspecialchars($booking->BookingNumber); ?></strong></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Booking Status:</span>
                        <span class="detail-value">
                            <span class="payment-badge 
                                <?php 
                                if ($booking->Status == 'Approved') echo 'badge-success';
                                elseif ($booking->Status == 'Rejected') echo 'badge-failed';
                                else echo 'badge-pending';
                                ?>">
                                <?php echo $booking->Status ?: 'Pending Review'; ?>
                            </span>
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Booking Status Flow:</span>
                        <span class="detail-value"><strong><?php echo htmlspecialchars($booking->BookingStatusFlow); ?></strong></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Request Date:</span>
                        <span class="detail-value"><?php echo date('d-M-Y H:i', strtotime($booking->DateofRequest)); ?></span>
                    </div>
                </div>
                
                <!-- User Details -->
                <div class="booking-card">
                    <div class="section-title"><i class="fa fa-user"></i> User Details</div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking->Name); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking->Email); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Phone Number:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking->PhoneNumber); ?></span>
                    </div>
                </div>
                
                <!-- Service Details -->
                <div class="booking-card">
                    <div class="section-title"><i class="fa fa-wrench"></i> Service Details</div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Service Type:</span>
                        <span class="detail-value"><strong><?php echo htmlspecialchars($booking->ServiceType); ?></strong></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Vehicle Type:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking->VehicleType); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Pickup Location:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking->PickupLoc); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Destination:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking->Destination); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Pickup Date & Time:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking->PickupDate . ' at ' . $booking->PickupTime); ?></span>
                    </div>
                </div>
                
                <!-- Payment Details -->
                <div class="booking-card">
                    <div class="section-title"><i class="fa fa-credit-card"></i> Payment Information</div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Total Service Charge:</span>
                        <span class="detail-value"><strong>₹<?php echo number_format($booking->TotalAmount, 2); ?></strong></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Advance Payment Amount:</span>
                        <span class="detail-value"><strong>₹<?php echo number_format($booking->AdvancePaymentAmount, 2); ?></strong></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Remaining Balance:</span>
                        <span class="detail-value"><strong>₹<?php echo number_format($booking->TotalAmount - $booking->AdvancePaymentAmount, 2); ?></strong></span>
                    </div>
                    
                    <hr>
                    
                    <div class="detail-row">
                        <span class="detail-label">Payment Status:</span>
                        <span class="detail-value">
                            <span class="payment-badge 
                                <?php 
                                if ($booking->PaymentStatus == 'Confirmed') echo 'badge-success';
                                elseif ($booking->PaymentStatus == 'Pending') echo 'badge-pending';
                                else echo 'badge-failed';
                                ?>">
                                <?php echo htmlspecialchars($booking->PaymentStatus); ?>
                            </span>
                        </span>
                    </div>
                    
                    <?php if ($booking->PaymentStatus === 'Confirmed') { ?>
                        <div class="detail-row">
                            <span class="detail-label">Payment Date:</span>
                            <span class="detail-value"><?php echo date('d-M-Y H:i', strtotime($booking->PaymentDate)); ?></span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Transaction ID:</span>
                            <span class="detail-value"><code><?php echo htmlspecialchars($booking->TransactionID); ?></code></span>
                        </div>
                        
                        <div class="success-info">
                            <i class="fa fa-check-circle"></i> <strong>Payment Received!</strong><br>
                            <small>Advance payment of ₹<?php echo number_format($booking->AdvancePaymentAmount, 2); ?> has been successfully received from the user.</small>
                        </div>
                    <?php } else { ?>
                        <div class="warning-info">
                            <i class="fa fa-exclamation-circle"></i> <strong>Payment Not Confirmed</strong><br>
                            <small>User has not completed the payment process yet. Booking cannot be approved without payment confirmation.</small>
                        </div>
                    <?php } ?>
                </div>
                
                <!-- Action Section -->
                <?php if ($booking->Status !== 'Approved' && $booking->Status !== 'Rejected') { ?>
                    <div class="booking-card action-section">
                        <div class="section-title"><i class="fa fa-tasks"></i> Admin Decision</div>
                        
                        <?php if ($booking->PaymentStatus !== 'Confirmed') { ?>
                            <div class="warning-info">
                                <strong>⚠️ Cannot Process This Booking</strong><br>
                                Payment must be confirmed before approving or rejecting this booking. Current payment status: <strong><?php echo $booking->PaymentStatus; ?></strong>
                            </div>
                        <?php } else { ?>
                            <form method="post" id="approvalForm">
                                <!-- Approve Section -->
                                <h5 style="color: #4CAF50; margin-top: 20px;">Approve Booking</h5>
                                
                                <div class="form-group">
                                    <label for="driverid">Select Service Provider/Driver *</label>
                                    <select name="driverid" id="driverid" required>
                                        <option value="">-- Select Service Provider --</option>
                                        <?php
                                        $sql_driver = "SELECT DriverID, Name FROM tbldriver WHERE Status='Active' ORDER BY Name";
                                        $query_driver = $dbh->prepare($sql_driver);
                                        $query_driver->execute();
                                        $drivers = $query_driver->fetchAll(PDO::FETCH_OBJ);
                                        
                                        foreach ($drivers as $driver) {
                                            echo '<option value="' . htmlspecialchars($driver->DriverID) . '">' . htmlspecialchars($driver->Name) . ' (' . htmlspecialchars($driver->DriverID) . ')</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <button type="submit" name="approve" class="action-btn btn-approve">
                                    <i class="fa fa-check"></i> Approve Booking
                                </button>
                                
                                <hr style="margin: 30px 0;">
                                
                                <!-- Reject Section -->
                                <h5 style="color: #f44336; margin-top: 20px;">Reject Booking</h5>
                                <p style="color: #666; font-size: 13px;">If you reject this booking, the advance payment of ₹<?php echo number_format($booking->AdvancePaymentAmount, 2); ?> will be automatically refunded to the user.</p>
                                
                                <div class="form-group">
                                    <label for="rejection_reason">Rejection Reason *</label>
                                    <textarea name="rejection_reason" id="rejection_reason" rows="4" placeholder="Provide a clear reason for rejection..." required></textarea>
                                </div>
                                
                                <button type="submit" name="reject" class="action-btn btn-reject" onclick="return confirm('Are you sure? This will initiate a refund of ₹<?php echo number_format($booking->AdvancePaymentAmount, 2); ?>');">
                                    <i class="fa fa-times"></i> Reject & Refund
                                </button>
                            </form>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="booking-card" style="background: #f5f5f5;">
                        <div class="section-title">Decision Status</div>
                        <p style="color: #666;">
                            <strong>Status:</strong> <?php echo $booking->Status; ?><br>
                            <strong>Date:</strong> <?php echo date('d-M-Y H:i', strtotime($booking->AdminApprovalDate)); ?><br>
                            <?php if ($booking->AssignTo) { ?>
                                <strong>Assigned to:</strong> <?php echo htmlspecialchars($booking->AssignTo); ?><br>
                            <?php } ?>
                            <?php if ($booking->RejectionReason) { ?>
                                <strong>Rejection Reason:</strong> <?php echo htmlspecialchars($booking->RejectionReason); ?><br>
                            <?php } ?>
                        </p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/bundles/libscripts.bundle.js"></script>
    <script src="../assets/bundles/vendorscripts.bundle.js"></script>
    <script src="../assets/bundles/mainscripts.bundle.js"></script>
</body>
</html>
<?php } ?>
