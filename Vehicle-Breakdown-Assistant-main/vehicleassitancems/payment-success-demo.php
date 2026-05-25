<?php
include('includes/dbconnection.php');
session_start();
error_reporting(0);

// Get booking ID from URL
$booking_id = isset($_GET['booking_id']) ? htmlspecialchars($_GET['booking_id']) : null;

if (!$booking_id) {
    echo '<script>alert("Invalid request"); window.location.href="index.php";</script>';
    exit;
}

// Fetch booking and payment details
try {
    $sql = "SELECT b.*, p.amount as paid_amount, p.created_at as payment_date 
            FROM tblbook b 
            LEFT JOIN tblpayments p ON b.BookingNumber = p.booking_number 
            WHERE b.BookingNumber = :booking_id 
            LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':booking_id', $booking_id, PDO::PARAM_STR);
    $query->execute();
    $booking = $query->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        echo '<script>alert("Booking not found"); window.location.href="index.php";</script>';
        exit;
    }
} catch (Exception $e) {
    echo '<script>alert("Error: " . $e->getMessage()); window.location.href="index.php";</script>';
    exit;
}

$paidAmount = $booking['paid_amount'] ?? $booking['AdvancePaymentAmount'];
$totalCharge = $booking['TotalAmount'] ?? 1000;
$remainingAmount = $totalCharge - $paidAmount;
$paymentDate = $booking['payment_date'] ?? date('Y-m-d H:i:s');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
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
        
        .success-container {
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
        
        .success-header {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
            color: white;
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .success-header::before {
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
        
        .success-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: checkmark 0.6s ease-out;
            position: relative;
            z-index: 1;
        }

        @keyframes checkmark {
            0% {
                transform: scale(0) rotate(45deg);
            }
            50% {
                transform: scale(1.2) rotate(45deg);
            }
            100% {
                transform: scale(1) rotate(0deg);
            }
        }
        
        .success-header h2 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .success-header p {
            margin: 0;
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .success-body {
            padding: 40px;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--success-color);
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 30px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .receipt-details {
            background: var(--light-color);
            padding: 30px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            border: 1px solid #e5e7eb;
        }
        
        .receipt-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 15px;
            transition: background 0.2s ease;
        }

        .receipt-row:hover {
            background: rgba(99, 102, 241, 0.05);
            margin: 0 -15px;
            padding: 15px;
        }
        
        .receipt-row:last-child {
            border-bottom: none;
        }
        
        .receipt-label {
            font-weight: 600;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .receipt-value {
            color: var(--dark-color);
            text-align: right;
            font-weight: 500;
        }
        
        .receipt-row.total {
            border-top: 2px solid #e5e7eb;
            padding-top: 20px;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--success-color);
        }
        
        .payment-info {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border-left: 4px solid var(--success-color);
            padding: 25px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
        }
        
        .payment-info h4 {
            color: #065f46;
            margin-top: 0;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .payment-info p {
            margin: 10px 0;
            color: #047857;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .next-steps {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid var(--warning-color);
            padding: 25px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
        }
        
        .next-steps h4 {
            color: #92400e;
            margin-top: 0;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .next-steps ol {
            margin: 0;
            padding-left: 20px;
            color: #92400e;
        }
        
        .next-steps li {
            margin: 12px 0;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .btn-primary {
            flex: 1;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            text-decoration: none;
            color: white;
        }
        
        .btn-secondary {
            flex: 1;
            padding: 15px;
            background: #f3f4f6;
            color: var(--dark-color);
            border: 1px solid #e5e7eb;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
        }
        
        .btn-secondary:hover {
            background: #e5e7eb;
            transform: translateY(-2px);
            text-decoration: none;
            color: var(--dark-color);
        }

        .confirmation-number {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 25px;
            border-radius: var(--border-radius);
            text-align: center;
            margin-bottom: 30px;
            border: 2px dashed #3b82f6;
        }

        .confirmation-number .label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .confirmation-number .value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            font-family: 'Courier New', monospace;
            letter-spacing: 3px;
        }

        .demo-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--warning-color);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 25px;
        }

        @media (max-width: 768px) {
            .success-container {
                margin: 10px;
            }
            
            .success-header {
                padding: 30px 20px;
            }
            
            .success-header h2 {
                font-size: 2rem;
            }
            
            .success-body {
                padding: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-header">
            <div class="success-icon">✓</div>
            <h2>Payment Successful!</h2>
            <p>Your advance payment has been processed</p>
        </div>
        
        <div class="success-body">
            
            <div class="demo-badge">⚠️ DEMO MODE</div>
            
            <!-- Status Badge -->
            <div class="status-badge">✓ Payment Confirmed</div>
            
            <!-- Confirmation Number -->
            <div class="confirmation-number">
                <div class="label">Transaction Reference</div>
                <div class="value"><?php echo 'TXN' . date('YmdHis'); ?></div>
            </div>
            
            <!-- Receipt Details -->
            <div class="receipt-details">
                <h4 style="margin-top: 0; color: #333; margin-bottom: 15px;">📋 Payment Receipt</h4>
                
                <div class="receipt-row">
                    <span class="receipt-label">Booking ID:</span>
                    <span class="receipt-value"><strong><?php echo htmlspecialchars($booking['BookingNumber']); ?></strong></span>
                </div>
                
                <div class="receipt-row">
                    <span class="receipt-label">Customer Name:</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($booking['Name']); ?></span>
                </div>
                
                <div class="receipt-row">
                    <span class="receipt-label">Email:</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($booking['Email']); ?></span>
                </div>
                
                <div class="receipt-row">
                    <span class="receipt-label">Service Type:</span>
                    <span class="receipt-value"><?php echo htmlspecialchars($booking['ServiceType'] ?? 'General'); ?></span>
                </div>
                
                <div class="receipt-row">
                    <span class="receipt-label">Total Service Charge:</span>
                    <span class="receipt-value">₹<?php echo number_format($totalCharge, 2); ?></span>
                </div>
                
                <div class="receipt-row" style="background: #e8f5e9; padding: 12px;">
                    <span class="receipt-label" style="color: #2e7d32;">Advance Payment (50%):</span>
                    <span class="receipt-value" style="color: #2e7d32; font-weight: bold;">₹<?php echo number_format($paidAmount, 2); ?></span>
                </div>
                
                <div class="receipt-row">
                    <span class="receipt-label">Remaining Amount:</span>
                    <span class="receipt-value">₹<?php echo number_format($remainingAmount, 2); ?></span>
                </div>
                
                <div class="receipt-row">
                    <span class="receipt-label">Payment Date & Time:</span>
                    <span class="receipt-value"><?php echo date('d M Y, H:i:s', strtotime($paymentDate)); ?></span>
                </div>
                
                <div class="receipt-row">
                    <span class="receipt-label">Payment Status:</span>
                    <span class="receipt-value"><span style="background: #4caf50; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">PAID</span></span>
                </div>
            </div>
            
            <!-- Payment Info -->
            <div class="payment-info">
                <h4>💳 Payment Information</h4>
                <p><strong>✓ Amount Paid:</strong> ₹<?php echo number_format($paidAmount, 2); ?> (50% Advance)</p>
                <p><strong>⏳ Pending:</strong> ₹<?php echo number_format($remainingAmount, 2); ?> (Due after service completion)</p>
                <p><strong>📌 Payment Method:</strong> Demo Payment (Prototype)</p>
            </div>
            
            <!-- Next Steps -->
            <div class="next-steps">
                <h4>📝 What Happens Next?</h4>
                <ol>
                    <li><strong>Booking Status:</strong> Your booking status is now "Payment Confirmed"</li>
                    <li><strong>Admin Review:</strong> Our admin will review your booking within 24 hours</li>
                    <li><strong>Approval/Rejection:</strong> You'll receive an email notification about the approval status</li>
                    <li><strong>If Approved:</strong> A service provider will be assigned to you</li>
                    <li><strong>Service Completion:</strong> You'll need to pay the remaining amount after service completion</li>
                    <li><strong>If Rejected:</strong> Your advance payment will be refunded to your original payment method</li>
                </ol>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="track-service.php?booking_id=<?php echo urlencode($booking_id); ?>" class="btn-primary">
                    📍 Track Booking
                </a>
                <a href="index.php" class="btn-secondary">
                    🏠 Home
                </a>
            </div>
            
            <!-- Important Notice -->
            <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; font-size: 12px; color: #666; border-left: 4px solid #999;">
                <strong>✓ Important:</strong> This is a demo payment system. No actual payment has been charged. 
                To test with real payment gateway integration, please contact support.
            </div>
        </div>
    </div>
</body>
</html>
