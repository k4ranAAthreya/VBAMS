<?php
include('includes/dbconnection.php');
include('includes/payment-config.php');
session_start();
error_reporting(0);

// Get booking ID from URL
$booking_id = isset($_GET['booking_id']) ? sanitizeInput($_GET['booking_id']) : null;

if (!$booking_id) {
    echo '<script>alert("Invalid booking request"); window.location.href="index.php";</script>';
    exit;
}

function sanitizeInput($input) {
    return htmlspecialchars(stripslashes(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Fetch booking details
try {
    $sql = "SELECT * FROM tblbook WHERE BookingNumber = :booking_id LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':booking_id', $booking_id, PDO::PARAM_STR);
    $query->execute();
    $booking = $query->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        echo '<script>alert("Booking not found"); window.location.href="index.php";</script>';
        exit;
    }
} catch (Exception $e) {
    echo '<script>alert("Database error: " . $e->getMessage()); window.location.href="index.php";</script>';
    exit;
}

// Get service charge if available
$serviceType = $booking['ServiceType'] ?? 'General';
$totalCharge = $booking['TotalAmount'] ?? 1000; // Default to 1000 if not set
$advancePayment = $booking['AdvancePaymentAmount'] ?? ($totalCharge * 0.5); // 50% if not set

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    try {
        // Start transaction
        $dbh->beginTransaction();
        
        // Generate unique demo payment IDs
        $demoOrderId = 'DEMO_' . date('YmdHis') . '_' . mt_rand(10000, 99999);
        $demoPaymentId = 'DEMOPAY_' . date('YmdHis') . '_' . mt_rand(10000, 99999);
        $paymentUuid = 'demo-' . $booking_id . '-' . time();
        
        // Update booking status to "Advance Paid"
        $updateBooking = "UPDATE tblbook SET PaymentStatus = 'Paid', BookingStatusFlow = 'Payment Confirmed' WHERE BookingNumber = :booking_id";
        $stmtBooking = $dbh->prepare($updateBooking);
        $stmtBooking->bindParam(':booking_id', $booking_id, PDO::PARAM_STR);
        $stmtBooking->execute();
        
        // Insert payment record with all required fields
        $insertPayment = "INSERT INTO tblpayments (booking_number, payment_uuid, razorpay_order_id, razorpay_payment_id, amount, status, payment_method, currency, created_at) 
                         VALUES (:booking_id, :uuid, :order_id, :payment_id, :amount, 'captured', 'demo', 'INR', NOW())";
        $stmtPayment = $dbh->prepare($insertPayment);
        $stmtPayment->bindParam(':booking_id', $booking_id, PDO::PARAM_STR);
        $stmtPayment->bindParam(':uuid', $paymentUuid, PDO::PARAM_STR);
        $stmtPayment->bindParam(':order_id', $demoOrderId, PDO::PARAM_STR);
        $stmtPayment->bindParam(':payment_id', $demoPaymentId, PDO::PARAM_STR);
        $stmtPayment->bindParam(':amount', $advancePayment, PDO::PARAM_STR);
        $stmtPayment->execute();
        
        // Commit transaction
        $dbh->commit();
        
        // Redirect to success page
        echo "<script>window.location.href='payment-success-demo.php?booking_id=" . urlencode($booking_id) . "'</script>";
        exit;
    } catch (Exception $e) {
        $dbh->rollBack();
        $error = "Payment processing failed: " . $e->getMessage();
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advance Payment - DEMO</title>
    <link rel="stylesheet" href="css1/bootstrap.min.css">
    <link rel="stylesheet" href="css1/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-color: #f9fafb;
            --border-radius: 12px;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.07);
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .payment-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            max-width: 800px;
            margin: 0 auto;
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .payment-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .payment-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .payment-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .payment-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .payment-body {
            padding: 40px;
        }
        
        .booking-details {
            background: var(--light-color);
            padding: 30px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            border: 1px solid #e5e7eb;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #e5e7eb;
            transition: background 0.2s ease;
        }

        .detail-row:hover {
            background: rgba(99, 102, 241, 0.05);
            margin: 0 -15px;
            padding: 15px;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .detail-value {
            color: var(--dark-color);
            font-weight: 600;
        }
        
        .payment-summary {
            background: linear-gradient(135deg, #e8f4f8 0%, #d1fae5 100%);
            border-left: 4px solid var(--success-color);
            padding: 30px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            font-size: 16px;
            align-items: center;
        }
        
        .summary-row.total {
            font-weight: bold;
            font-size: 1.5rem;
            color: var(--primary-color);
            border-top: 2px solid #e5e7eb;
            padding-top: 20px;
            margin-top: 20px;
        }
        
        .summary-label {
            color: #6b7280;
            font-weight: 500;
        }
        
        .summary-value {
            color: var(--dark-color);
            font-weight: 600;
        }
        
        .payment-note {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid var(--warning-color);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            font-size: 14px;
            color: #92400e;
        }
        
        .payment-note strong {
            display: block;
            color: #78350f;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .demo-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--warning-color);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 25px;
        }
        
        .payment-button {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .payment-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .payment-button:hover::before {
            left: 100%;
        }
        
        .payment-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .payment-button:active {
            transform: translateY(0);
        }
        
        .error-message {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border-left: 4px solid var(--danger-color);
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        
        .back-link a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .security-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border-radius: var(--border-radius);
            font-size: 14px;
            color: #065f46;
            margin-top: 20px;
            border: 1px solid #a7f3d0;
        }

        .security-info i {
            font-size: 18px;
            color: var(--success-color);
        }

        .amount-highlight {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            margin: 20px 0;
        }

        @media (max-width: 768px) {
            .payment-container {
                margin: 10px;
            }
            
            .payment-header h2 {
                font-size: 2rem;
            }
            
            .payment-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h2>💳 Advance Payment</h2>
            <p>Secure Payment Processing (DEMO)</p>
        </div>
        
        <div class="payment-body">
            <!-- Error Message -->
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <strong>Error:</strong> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- Demo Badge -->
            <div class="demo-badge">⚠️ DEMO MODE - No Real Payment</div>
            
            <!-- Booking Details -->
            <div class="booking-details">
                <h4 style="margin-top: 0; color: #333;">Booking Details</h4>
                
                <div class="detail-row">
                    <span class="detail-label">Booking ID:</span>
                    <span class="detail-value"><strong><?php echo htmlspecialchars($booking['BookingNumber']); ?></strong></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Customer Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['Name']); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['Email']); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Service Type:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($serviceType); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Pickup Location:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['PickupLoc']); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Pickup Date & Time:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['PickupDate'] . ' ' . $booking['PickupTime']); ?></span>
                </div>
            </div>
            
            <!-- Payment Summary -->
            <div class="payment-summary">
                <h4 style="margin-top: 0; color: #0c5460;">Payment Summary</h4>
                
                <div class="summary-row">
                    <span class="summary-label">Total Service Charge:</span>
                    <span class="summary-value">₹<?php echo number_format($totalCharge, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Advance Payment Required (50%):</span>
                    <span class="summary-value">₹<?php echo number_format($advancePayment, 2); ?></span>
                </div>
                
                <div class="summary-row total">
                    <span class="summary-label">Amount to Pay Now:</span>
                    <span class="summary-value">₹<?php echo number_format($advancePayment, 2); ?></span>
                </div>
            </div>
            
            <!-- Payment Note -->
            <div class="payment-note">
                <strong>📌 Important Information:</strong>
                You are paying 50% of the total service charge as advance payment. 
                The remaining 50% (₹<?php echo number_format($totalCharge - $advancePayment, 2); ?>) 
                will be payable after service completion. Refunds are applicable if booking is rejected by admin.
            </div>
            
            <!-- Payment Form -->
            <form method="POST" action="">
                <button type="submit" name="confirm_payment" class="payment-button">
                    🔒 Confirm Advance Payment
                </button>
                
                <div class="security-info">
                    <i class="fa fa-shield"></i>
                    <span>This is a secure demo payment. No real payment will be charged.</span>
                </div>
            </form>
            
            <!-- Terms & Conditions -->
            <div style="background: #f5f5f5; padding: 12px; border-radius: 4px; margin-top: 20px; font-size: 12px; color: #666;">
                <input type="checkbox" id="terms" style="margin-right: 8px;">
                <label for="terms" style="cursor: pointer;">
                    I agree to the <strong>Terms & Conditions</strong> and understand that this advance payment 
                    will be adjusted towards the final service charge.
                </label>
            </div>
            
            <!-- Back Link -->
            <div class="back-link">
                <a href="track-service.php?booking_id=<?php echo urlencode($booking_id); ?>">← Back to Booking</a>
            </div>
        </div>
    </div>
</body>
</html>
