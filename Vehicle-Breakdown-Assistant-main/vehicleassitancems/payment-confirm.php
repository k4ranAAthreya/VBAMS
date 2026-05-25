<?php
include('includes/dbconnection.php');
include('includes/payment-config.php');
session_start();
error_reporting(0);

// Get booking number
$bookingNumber = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : null;

if (!$bookingNumber) {
    echo '<script>alert("Invalid booking"); window.location.href = "index.php";</script>';
    exit;
}

// Fetch booking details
$sql = "SELECT * FROM tblbook WHERE BookingNumber = :booking_number";
$query = $dbh->prepare($sql);
$query->bindParam(':booking_number', $bookingNumber, PDO::PARAM_INT);
$query->execute();

if ($query->rowCount() === 0) {
    echo '<script>alert("Booking not found"); window.location.href = "index.php";</script>';
    exit;
}

$booking = $query->fetch(PDO::FETCH_OBJ);

// Check if payment already completed
if ($booking->PaymentStatus === 'Confirmed') {
    echo '<script>alert("Payment already confirmed for this booking"); window.location.href = "track-service.php?booking_id=' . $bookingNumber . '";</script>';
    exit;
}

$advanceAmount = $booking->AdvancePaymentAmount;
$totalAmount = $booking->TotalAmount;
?>
<!DOCTYPE html>
<html class="no-js" lang="zxx">
    <head>
        <title>VBAMS || Payment Confirmation</title>
        <link rel="stylesheet" href="css1/normalize.css">
        <link rel="stylesheet" href="css1/animate.css">
        <link rel="stylesheet" href="css1/bootstrap.min.css">
        <link rel="stylesheet" href="css1/meanmenu.min.css">
        <link rel="stylesheet" href="css1/font-awesome.min.css">
        <link rel="stylesheet" href="css1/icofont.css">
        <link rel="stylesheet" href="css1/change-text.css">
        <link rel="stylesheet" href="css1/main.css">
        <link rel="stylesheet" href="css1/owl.carousel.css">
        <link rel="stylesheet" href="css1/owl.theme.css">
        <link rel="stylesheet" href="css1/owl.transitions.css">
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="css1/responsive.css">
        <style>
            .payment-container { max-width: 600px; margin: 50px auto; }
            .payment-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 15px rgba(0,0,0,0.1); }
            .booking-details { background: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0; }
            .detail-row { display: flex; justify-content: space-between; margin: 10px 0; }
            .detail-label { font-weight: 500; color: #333; }
            .detail-value { color: #666; }
            .amount-section { background: #e3f2fd; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #2196F3; }
            .amount-display { font-size: 28px; font-weight: bold; color: #2196F3; margin: 10px 0; }
            .security-badge { text-align: center; margin: 20px 0; color: #4CAF50; }
            .pay-button { width: 100%; padding: 15px; background: #2196F3; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 20px; }
            .pay-button:hover { background: #1976D2; }
            .cancel-button { width: 100%; padding: 12px; background: #f44336; color: white; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; margin-top: 10px; }
            .cancel-button:hover { background: #da190b; }
            .approved-badge { display: inline-block; background: #4CAF50; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; }
            .loading { text-align: center; padding: 20px; }
            .spinner { border: 4px solid #f3f3f3; border-top: 4px solid #2196F3; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto; }
            @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        </style>
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    </head>
    <body>
        <?php include_once('includes/header.php');?>
        
        <div class="page-title-area overlay">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="page-title">
                            <h2>Payment Confirmation</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="payment-container">
            <div class="payment-card">
                <h3 style="margin-bottom: 25px; text-align: center;"><i class="fa fa-lock"></i> Secure Payment</h3>
                
                <!-- Booking Details -->
                <div class="booking-details">
                    <h5 style="margin-bottom: 15px;">Booking Information</h5>
                    
                    <div class="detail-row">
                        <span class="detail-label">Booking Number:</span>
                        <span class="detail-value"><strong><?php echo htmlspecialchars($booking->BookingNumber); ?></strong></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking->Name); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Service Type:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking->ServiceType); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Pickup Location:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking->PickupLoc); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Pickup Date & Time:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking->PickupDate . ' at ' . $booking->PickupTime); ?></span>
                    </div>
                </div>
                
                <!-- Amount Section -->
                <div class="amount-section">
                    <h5>Payment Amount</h5>
                    
                    <div class="detail-row" style="margin-top: 15px;">
                        <span class="detail-label">Total Service Charge:</span>
                        <span class="detail-value">₹<?php echo number_format($totalAmount, 2); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Advance Payment (50%):</span>
                        <span class="detail-value"><strong>₹<?php echo number_format($advanceAmount, 2); ?></strong></span>
                    </div>
                    
                    <div class="detail-row" style="border-top: 2px solid #90CAF9; padding-top: 15px; margin-top: 15px;">
                        <span class="detail-label">Amount to Pay Now:</span>
                        <span class="amount-display">₹<?php echo number_format($advanceAmount, 2); ?></span>
                    </div>
                    
                    <small style="color: #666; display: block; margin-top: 10px;">
                        Remaining ₹<?php echo number_format($totalAmount - $advanceAmount, 2); ?> will be payable after service completion.
                    </small>
                </div>
                
                <!-- Security Info -->
                <div class="security-badge">
                    <i class="fa fa-shield fa-2x"></i>
                    <p style="margin: 10px 0;"><strong>Secure Payment</strong></p>
                    <small>Your payment information is encrypted and secure. Only advance payment is required now.</small>
                </div>
                
                <!-- Payment Button -->
                <button class="pay-button" id="payButton" onclick="initiatePayment()">
                    <i class="fa fa-credit-card"></i> Pay Now with Razorpay
                </button>
                
                <!-- Cancel Button -->
                <button class="cancel-button" onclick="if(confirm('Are you sure you want to cancel this booking?')) { window.location.href='track-service.php?booking_id=<?php echo $bookingNumber; ?>'; }">
                    Cancel Booking
                </button>
                
                <!-- Terms & Conditions -->
                <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-top: 20px; font-size: 12px;">
                    <input type="checkbox" id="termsCheck" required> 
                    <label for="termsCheck">
                        I agree that this advance payment confirms my booking and understand that cancellation policies apply.
                    </label>
                </div>
                
                <!-- Info Box -->
                <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-top: 20px; border-left: 4px solid #ffc107;">
                    <h5 style="margin-top: 0;"><i class="fa fa-info-circle"></i> Important Information</h5>
                    <ul style="margin: 10px 0; padding-left: 20px; font-size: 12px;">
                        <li>Your booking will be under admin review after successful payment</li>
                        <li>You will receive confirmation once admin approves your request</li>
                        <li>If rejected, full advance amount will be refunded within <?php echo MAX_REFUND_PROCESSING_TIME; ?> business days</li>
                        <li>Service provider will be assigned upon approval</li>
                        <li>Keep your booking number safe for tracking</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div id="loadingDiv" class="loading" style="display: none;">
            <div class="spinner"></div>
            <p>Processing payment...</p>
        </div>

        <?php include_once('includes/footer.php');?>

        <script>
        function initiatePayment() {
            // Validate terms
            if (!document.getElementById('termsCheck').checked) {
                alert('Please agree to terms and conditions');
                return;
            }
            
            var bookingNumber = <?php echo $bookingNumber; ?>;
            var advanceAmount = <?php echo $advanceAmount; ?>;
            var totalAmount = <?php echo $totalAmount; ?>;
            var userEmail = '<?php echo htmlspecialchars($booking->Email); ?>';
            var userName = '<?php echo htmlspecialchars($booking->Name); ?>';
            var userPhone = '<?php echo htmlspecialchars($booking->PhoneNumber); ?>';
            var serviceType = '<?php echo htmlspecialchars($booking->ServiceType); ?>';
            
            // Show loading
            document.getElementById('payButton').disabled = true;
            document.getElementById('loadingDiv').style.display = 'block';
            
            // Call backend to initiate payment
            fetch('api/payment-process.php?action=initiate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'booking_number=' + bookingNumber + 
                      '&service_type=' + encodeURIComponent(serviceType) +
                      '&total_amount=' + totalAmount +
                      '&user_email=' + encodeURIComponent(userEmail) +
                      '&user_name=' + encodeURIComponent(userName) +
                      '&user_phone=' + userPhone +
                      '&payment_method=Credit Card'
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingDiv').style.display = 'none';
                document.getElementById('payButton').disabled = false;
                
                if (data.success && data.data) {
                    // Open Razorpay checkout
                    var options = {
                        key: data.data.key_id,
                        amount: Math.round(advanceAmount * 100),
                        currency: 'INR',
                        name: 'Vehicle Breakdown Assistance',
                        description: 'Booking #' + bookingNumber + ' - ' + serviceType,
                        order_id: data.data.transaction_id,
                        handler: function(response) {
                            handlePaymentSuccess(response, bookingNumber, advanceAmount);
                        },
                        prefill: {
                            name: userName,
                            email: userEmail,
                            contact: userPhone
                        },
                        theme: {
                            color: '#2196F3'
                        },
                        modal: {
                            ondismiss: function() {
                                alert('Payment cancelled');
                            }
                        }
                    };
                    
                    var rzp1 = new Razorpay(options);
                    rzp1.open();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('loadingDiv').style.display = 'none';
                document.getElementById('payButton').disabled = false;
                alert('Payment initiation failed. Please try again.');
            });
        }
        
        function handlePaymentSuccess(response, bookingNumber, advanceAmount) {
            // Verify payment
            document.getElementById('loadingDiv').style.display = 'block';
            
            fetch('api/payment-process.php?action=verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'booking_number=' + bookingNumber +
                      '&transaction_id=' + response.razorpay_order_id +
                      '&razorpay_signature=' + response.razorpay_signature +
                      '&razorpay_payment_id=' + response.razorpay_payment_id
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingDiv').style.display = 'none';
                
                if (data.success) {
                    alert('Payment successful!\n\nYour booking is now under admin review.\nBooking Number: ' + bookingNumber + '\n\nCheck back here for updates.');
                    window.location.href = 'track-service.php?booking_id=' + bookingNumber;
                } else {
                    alert('Payment verification failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('loadingDiv').style.display = 'none';
                alert('Error verifying payment. Please contact support.');
            });
        }
        </script>
    </body>
</html>
