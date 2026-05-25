<?php
include('includes/dbconnection.php');
session_start();
error_reporting(0);

// Check if admin is logged in (basic check - you may have your own auth system)
// For demo purposes, we'll allow access - modify this based on your auth system

// Handle booking approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $booking_id = htmlspecialchars($_POST['booking_id']);
    $action = htmlspecialchars($_POST['action']);
    
    try {
        if ($action === 'accept_payment') {
            // Accept/verify payment
            $sql = "UPDATE tblpayments SET status = 'authorized' WHERE booking_number = :booking_id";
            $query = $dbh->prepare($sql);
            $query->bindParam(':booking_id', $booking_id, PDO::PARAM_STR);
            $query->execute();
            $success = "✓ Payment accepted! Now you can approve the booking with driver assignment.";
        } elseif ($action === 'approve') {
            // Approve booking with MANDATORY driver assignment
            $assignTo = htmlspecialchars($_POST['assign_driver'] ?? '');
            
            // Driver assignment is mandatory
            if (!$assignTo || empty($assignTo)) {
                throw new Exception("Driver assignment is mandatory. Please select a driver before approving.");
            }
            
            // Verify driver exists
            $verifyDriver = "SELECT DriverID FROM tbldriver WHERE Name = :driver_name LIMIT 1";
            $verifyQuery = $dbh->prepare($verifyDriver);
            $verifyQuery->bindParam(':driver_name', $assignTo, PDO::PARAM_STR);
            $verifyQuery->execute();
            
            if ($verifyQuery->rowCount() === 0) {
                throw new Exception("Selected driver '" . $assignTo . "' not found in database.");
            }
            
            $sql = "UPDATE tblbook SET BookingStatusFlow = 'Confirmed', Status = 'Approved', AdminApprovalDate = NOW(), AssignTo = :driver WHERE BookingNumber = :id";
            $query = $dbh->prepare($sql);
            $query->bindParam(':id', $booking_id, PDO::PARAM_STR);
            $query->bindParam(':driver', $assignTo, PDO::PARAM_STR);
            $result = $query->execute();
            
            if ($result && $query->rowCount() > 0) {
                $success = "✓ Booking approved successfully! Driver assigned: " . $assignTo;
            } else {
                throw new Exception("Booking update failed (0 rows affected).");
            }
        } elseif ($action === 'reject') {
            $reason = htmlspecialchars($_POST['rejection_reason'] ?? 'Admin rejected booking');
            
            // Start transaction
            $dbh->beginTransaction();
            
            // Reject booking
            $sql = "UPDATE tblbook SET BookingStatusFlow = 'Rejected', Status = 'Rejected', RejectionReason = :reason, AdminApprovalDate = NOW() WHERE BookingNumber = :id";
            $query = $dbh->prepare($sql);
            $query->bindParam(':id', $booking_id, PDO::PARAM_STR);
            $query->bindParam(':reason', $reason, PDO::PARAM_STR);
            $query->execute();
            
            // Process refund
            $fetchPayment = "SELECT * FROM tblpayments WHERE booking_number = :id LIMIT 1";
            $paymentQuery = $dbh->prepare($fetchPayment);
            $paymentQuery->bindParam(':id', $booking_id, PDO::PARAM_STR);
            $paymentQuery->execute();
            $payment = $paymentQuery->fetch(PDO::FETCH_ASSOC);
            
            if ($payment) {
                // Update payment status to refunded
                $updatePayment = "UPDATE tblpayments SET status = 'refunded', refund_status = 'processed' WHERE id = :payment_id";
                $paymentUpdateQuery = $dbh->prepare($updatePayment);
                $paymentUpdateQuery->bindParam(':payment_id', $payment['id'], PDO::PARAM_STR);
                $paymentUpdateQuery->execute();
                
                // Create refund record
                $insertRefund = "INSERT INTO tblrefunds (PaymentID, BookingNumber, RefundAmount, RefundReason, RefundStatus, ProcessedBy, CompletedAt) 
                               VALUES (:payment_id, :booking_id, :amount, :reason, 'Completed', 'Admin', NOW())";
                $refundQuery = $dbh->prepare($insertRefund);
                $refundQuery->bindParam(':payment_id', $payment['id'], PDO::PARAM_STR);
                $refundQuery->bindParam(':booking_id', $booking_id, PDO::PARAM_STR);
                $refundQuery->bindParam(':amount', $payment['amount'], PDO::PARAM_STR);
                $refundQuery->bindParam(':reason', $reason, PDO::PARAM_STR);
                $refundQuery->execute();
            }
            
            $dbh->commit();
            $success = "Booking rejected and refund initiated!";
        }
    } catch (Exception $e) {
        $dbh->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch all bookings with payment status and driver information
try {
    $sql = "SELECT 
            b.ID,
            b.BookingNumber,
            b.Name,
            b.Email,
            b.PhoneNumber,
            b.ServiceType,
            b.PaymentStatus,
            b.AdvancePaymentAmount,
            b.TotalAmount,
            b.BookingStatusFlow,
            b.PickupDate,
            b.PickupTime,
            b.PickupLoc,
            b.Status,
            b.AdminApprovalDate,
            b.AssignTo,
            p.amount as paid_amount,
            p.status as payment_db_status,
            p.created_at as payment_date,
            d.DriverID as driver_id,
            d.Name as DriverName,
            d.MobileNumber as driver_phone,
            d.Email as driver_email
            FROM tblbook b 
            LEFT JOIN tblpayments p ON b.BookingNumber = p.booking_number
            LEFT JOIN tbldriver d ON b.AssignTo = d.Name
            ORDER BY b.DateofRequest DESC";
    
    $query = $dbh->prepare($sql);
    $query->execute();
    $bookings = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Database Error: " . $e->getMessage();
    $bookings = array();
}

// Get statistics
$paidCount = count(array_filter($bookings, function($b) { return $b['PaymentStatus'] === 'Paid' || $b['payment_db_status'] === 'captured'; }));
$pendingPaymentCount = count(array_filter($bookings, function($b) { return $b['PaymentStatus'] === 'Pending' || !$b['payment_db_status']; }));
$approvedCount = count(array_filter($bookings, function($b) { return $b['BookingStatusFlow'] === 'Confirmed'; }));
$rejectedCount = count(array_filter($bookings, function($b) { return $b['BookingStatusFlow'] === 'Rejected'; }));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Payment & Booking Management</title>
    <link rel="stylesheet" href="../css1/bootstrap.min.css">
    <link rel="stylesheet" href="../css1/font-awesome.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-container {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .admin-header h1 {
            margin: 0;
            font-size: 32px;
        }
        
        .admin-header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card h3 {
            margin: 0;
            font-size: 14px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #333;
            margin: 10px 0 0 0;
        }
        
        .stat-card.paid {
            border-left: 4px solid #4caf50;
        }
        
        .stat-card.pending {
            border-left: 4px solid #ff9800;
        }
        
        .stat-card.approved {
            border-left: 4px solid #2196f3;
        }
        
        .stat-card.rejected {
            border-left: 4px solid #f44336;
        }
        
        .stat-value.paid { color: #4caf50; }
        .stat-value.pending { color: #ff9800; }
        .stat-value.approved { color: #2196f3; }
        .stat-value.rejected { color: #f44336; }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filters select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            margin: 0;
        }
        
        thead {
            background: #f8f9fa;
            border-bottom: 2px solid #ddd;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge.paid {
            background: #c8e6c9;
            color: #2e7d32;
        }
        
        .badge.pending {
            background: #ffe0b2;
            color: #e65100;
        }
        
        .badge.confirmed {
            background: #bbdefb;
            color: #1565c0;
        }
        
        .badge.rejected {
            background: #ffcdd2;
            color: #c62828;
        }
        
        .badge.refunded {
            background: #f3e5f5;
            color: #6a1b9a;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-sm {
            padding: 6px 12px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .btn-approve {
            color: #2e7d32;
            border-color: #4caf50;
        }
        
        .btn-approve:hover {
            background: #e8f5e9;
        }
        
        .btn-reject {
            color: #c62828;
            border-color: #f44336;
        }
        
        .btn-reject:hover {
            background: #ffebee;
        }
        
        .success-message {
            background: #c8e6c9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #4caf50;
        }
        
        .error-message {
            background: #ffcdd2;
            color: #c62828;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #f44336;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
        }

        .modal-content h3 {
            margin-top: 0;
            color: #333;
        }

        .modal-content textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            margin-bottom: 15px;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
        }

        .modal-buttons button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .modal-buttons .btn-confirm {
            background: #f44336;
            color: white;
        }

        .modal-buttons .btn-cancel {
            background: #f5f5f5;
            color: #333;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .demo-badge {
            display: inline-block;
            background: #ffc107;
            color: #333;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <div class="admin-header">
            <h1>📊 Admin Dashboard</h1>
            <p>Payment & Booking Management System (DEMO)</p>
        </div>

        <!-- Demo Badge -->
        <div class="demo-badge">⚠️ DEMO MODE - Prototype System</div>

        <!-- Messages -->
        <?php if (isset($success)): ?>
            <div class="success-message">✓ <?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error-message">✗ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="stats-cards">
            <div class="stat-card paid">
                <h3>Advance Payments Received</h3>
                <div class="stat-value paid"><?php echo $paidCount; ?></div>
            </div>
            
            <div class="stat-card pending">
                <h3>Pending Payments</h3>
                <div class="stat-value pending"><?php echo $pendingPaymentCount; ?></div>
            </div>
            
            <div class="stat-card approved">
                <h3>Approved Bookings</h3>
                <div class="stat-value approved"><?php echo $approvedCount; ?></div>
            </div>
            
            <div class="stat-card rejected">
                <h3>Rejected Bookings</h3>
                <div class="stat-value rejected"><?php echo $rejectedCount; ?></div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters">
            <label for="filterPayment">Filter by Payment Status:</label>
            <select id="filterPayment" onchange="filterTable('payment', this.value)">
                <option value="">All</option>
                <option value="paid">Paid</option>
                <option value="pending">Pending</option>
            </select>
            
            <label for="filterBooking">Filter by Booking Status:</label>
            <select id="filterBooking" onchange="filterTable('booking', this.value)">
                <option value="">All</option>
                <option value="confirmed">Confirmed</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        
        <!-- Bookings Table -->
        <div class="table-container">
            <?php if (count($bookings) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Service Type</th>
                            <th>Driver</th>
                            <th>Payment Status</th>
                            <th>Advance Amount</th>
                            <th>Booking Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr class="booking-row" data-payment="<?php echo strtolower($booking['PaymentStatus'] ?? 'pending'); ?>" data-booking="<?php echo strtolower(str_replace(' ', '', $booking['BookingStatusFlow'])); ?>">
                                <td><strong><?php echo htmlspecialchars($booking['BookingNumber']); ?></strong></td>
                                <td>
                                    <div><?php echo htmlspecialchars($booking['Name']); ?></div>
                                    <small style="color: #999;"><?php echo htmlspecialchars($booking['Email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($booking['ServiceType'] ?? 'General'); ?></td>
                                <td>
                                    <?php if ($booking['DriverName']): ?>
                                        <div style="font-weight: 600;"><?php echo htmlspecialchars($booking['DriverName']); ?></div>
                                        <small style="color: #999;">📱 <?php echo htmlspecialchars($booking['driver_phone'] ?? 'N/A'); ?></small>
                                        <div style="font-size: 11px; color: #667eea; margin-top: 3px;">
                                            <span style="color: #4caf50;">● Assigned</span>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #999; font-style: italic;">Not Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $paymentStatus = ($booking['PaymentStatus'] === 'Paid' || $booking['payment_db_status'] === 'captured') ? 'Paid' : 'Pending';
                                    ?>
                                    <span class="badge <?php echo strtolower($paymentStatus); ?>">
                                        <?php echo $paymentStatus; ?>
                                    </span>
                                </td>
                                <td>₹<?php echo number_format($booking['AdvancePaymentAmount'] ?? 0, 2); ?></td>
                                <td>
                                    <?php 
                                    $status = $booking['BookingStatusFlow'];
                                    if ($status === 'Payment Confirmed') {
                                        $statusBadge = 'pending';
                                        $statusText = 'Pending Approval';
                                    } elseif ($status === 'Confirmed') {
                                        $statusBadge = 'confirmed';
                                        $statusText = 'Confirmed';
                                    } elseif ($status === 'Rejected') {
                                        $statusBadge = 'rejected';
                                        $statusText = 'Rejected';
                                    } else {
                                        $statusBadge = 'pending';
                                        $statusText = $status;
                                    }
                                    ?>
                                    <span class="badge <?php echo $statusBadge; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($booking['PickupDate'])); ?></td>
                                <td>
                                    <?php if ($booking['BookingStatusFlow'] === 'Payment Confirmed'): ?>
                                        <div class="action-buttons" style="display: flex; flex-direction: column; gap: 5px;">
                                            <?php 
                                            // Check payment acceptance status
                                            $paymentDBStatus = $booking['payment_db_status'] ?? '';
                                            
                                            if ($paymentDBStatus === 'captured') {
                                                // Payment received but not accepted yet
                                                echo '<form method="POST" style="display: inline;"><input type="hidden" name="booking_id" value="' . htmlspecialchars($booking['BookingNumber']) . '"><input type="hidden" name="action" value="accept_payment"><button type="submit" class="btn-sm" style="background: #ff9800; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: 600;">✓ Accept Payment</button></form>';
                                            } else if ($paymentDBStatus === 'authorized') {
                                                // Payment accepted, now can approve
                                                echo '<button type="button" class="btn-sm btn-approve" onclick="openApproveModal(\'' . htmlspecialchars($booking['BookingNumber']) . '\');" style="background: #4caf50; color: white; border: none;">✓ Approve & Assign Driver</button>';
                                            }
                                            // Reject button always available
                                            echo '<button type="button" class="btn-sm btn-reject" onclick="openRejectModal(\'' . htmlspecialchars($booking['BookingNumber']) . '\');" style="background: #f44336; color: white; border: none;">✕ Reject</button>';
                                            ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 12px;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <h3>No Bookings Yet</h3>
                    <p>There are no bookings in the system yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Approve Modal with Driver Assignment -->
    <div id="approveModal" class="modal">
        <div class="modal-content">
            <h3>Approve Booking</h3>
            <p><strong>⚠️ Driver assignment is REQUIRED:</strong></p>
            <form id="approveForm" method="POST" onsubmit="return validateApproveForm();">
                <input type="hidden" id="approveBookingId" name="booking_id">
                <input type="hidden" name="action" value="approve">
                <select name="assign_driver" id="driverSelect" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px;" required>
                    <option value="">-- Select Driver (REQUIRED) --</option>
                    <?php 
                    try {
                        // Fetch all drivers from database
                        $driverSql = "SELECT DriverID, Name, MobileNumber FROM tbldriver ORDER BY Name";
                        $driverQuery = $dbh->prepare($driverSql);
                        $driverQuery->execute();
                        $drivers = $driverQuery->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($drivers) > 0) {
                            foreach ($drivers as $driver) {
                                echo '<option value="' . htmlspecialchars($driver['Name']) . '">' . htmlspecialchars($driver['Name']) . ' (📱 ' . htmlspecialchars($driver['MobileNumber']) . ')</option>';
                            }
                        } else {
                            echo '<option value="">No drivers found in database</option>';
                        }
                    } catch (Exception $e) {
                        echo '<option value="">Error loading drivers: ' . htmlspecialchars($e->getMessage()) . '</option>';
                    }
                    ?>
                </select>
                <div id="approveError" style="color: #f44336; font-weight: bold; margin-bottom: 10px; display: none;">
                    ⚠️ Please select a driver before confirming approval
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn-confirm" style="background: #4caf50;">✓ Confirm Approval</button>
                    <button type="button" class="btn-cancel" onclick="closeApproveModal();">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <h3>Reject Booking</h3>
            <p>Please provide a reason for rejection:</p>
            <form id="rejectForm" method="POST">
                <input type="hidden" id="modalBookingId" name="booking_id">
                <input type="hidden" name="action" value="reject">
                <textarea name="rejection_reason" placeholder="Enter rejection reason..." required></textarea>
                <div class="modal-buttons">
                    <button type="submit" class="btn-confirm">Confirm Rejection</button>
                    <button type="button" class="btn-cancel" onclick="closeRejectModal();">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validateApproveForm() {
            const driverSelect = document.getElementById('driverSelect').value;
            const errorDiv = document.getElementById('approveError');
            
            if (!driverSelect || driverSelect.trim() === '') {
                errorDiv.style.display = 'block';
                return false;
            } else {
                errorDiv.style.display = 'none';
                return true;
            }
        }

        function openApproveModal(bookingId) {
            document.getElementById('approveBookingId').value = bookingId;
            document.getElementById('driverSelect').value = '';
            document.getElementById('approveError').style.display = 'none';
            document.getElementById('approveModal').classList.add('active');
        }

        function closeApproveModal() {
            document.getElementById('approveModal').classList.remove('active');
        }

        function openRejectModal(bookingId) {
            document.getElementById('modalBookingId').value = bookingId;
            document.getElementById('rejectModal').classList.add('active');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.remove('active');
        }

        function filterTable(type, value) {
            const rows = document.querySelectorAll('.booking-row');
            rows.forEach(row => {
                let show = true;
                if (type === 'payment' && value) {
                    show = row.getAttribute('data-payment') === value.toLowerCase();
                } else if (type === 'booking' && value) {
                    show = row.getAttribute('data-booking').includes(value.toLowerCase());
                }
                row.style.display = show ? '' : 'none';
            });
        }

        // Close modal when clicking outside
        document.getElementById('approveModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeApproveModal();
            }
        });

        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });
    </script>
</body>
</html>
