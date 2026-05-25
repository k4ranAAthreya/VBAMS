<?php
/**
 * PAYMENT PROCESSING API
 * Advanced Payment Confirmation System
 * 
 * This file handles all payment-related operations:
 * - Initiating payments
 * - Verifying payments
 * - Processing refunds
 * - Managing payment notifications
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../includes/dbconnection.php');
include('../includes/payment-config.php');

// Get request method
$request_method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : null;

/**
 * API ENDPOINT: /api/payment-process.php?action=initiate
 * POST: Initiate payment for a booking
 */
if ($action === 'initiate' && $request_method === 'POST') {
    $response = initiatePayment();
    echo json_encode($response);
    exit;
}

/**
 * API ENDPOINT: /api/payment-process.php?action=verify
 * POST: Verify payment from gateway
 */
if ($action === 'verify' && $request_method === 'POST') {
    $response = verifyPayment();
    echo json_encode($response);
    exit;
}

/**
 * API ENDPOINT: /api/payment-process.php?action=webhook
 * POST: Handle payment gateway webhook
 */
if ($action === 'webhook' && $request_method === 'POST') {
    $response = handlePaymentWebhook();
    echo json_encode($response);
    exit;
}

/**
 * API ENDPOINT: /api/payment-process.php?action=refund
 * POST: Process refund request
 */
if ($action === 'refund' && $request_method === 'POST') {
    $response = processRefund();
    echo json_encode($response);
    exit;
}

/**
 * API ENDPOINT: /api/payment-process.php?action=get-charge
 * GET: Get service charge details
 */
if ($action === 'get-charge' && $request_method === 'GET') {
    $response = getChargeDetails();
    echo json_encode($response);
    exit;
}

/**
 * FUNCTION: Initiate Payment
 * Creates a payment request and returns payment gateway details
 */
function initiatePayment() {
    global $dbh;
    
    try {
        // Validate input
        $bookingNumber = isset($_POST['booking_number']) ? intval($_POST['booking_number']) : null;
        $serviceType = isset($_POST['service_type']) ? sanitizeInput($_POST['service_type']) : null;
        $totalAmount = isset($_POST['total_amount']) ? floatval($_POST['total_amount']) : null;
        $userEmail = isset($_POST['user_email']) ? sanitizeInput($_POST['user_email']) : null;
        $userName = isset($_POST['user_name']) ? sanitizeInput($_POST['user_name']) : null;
        $userPhone = isset($_POST['user_phone']) ? sanitizeInput($_POST['user_phone']) : null;
        $paymentMethod = isset($_POST['payment_method']) ? sanitizeInput($_POST['payment_method']) : 'Credit Card';
        
        // Validation
        if (!$bookingNumber || !$serviceType || !$totalAmount || !$userEmail) {
            return array(
                'success' => false,
                'message' => 'Missing required parameters',
                'code' => 400
            );
        }
        
        // Check if booking exists
        $sql = "SELECT * FROM tblbook WHERE BookingNumber = :booking_number";
        $query = $dbh->prepare($sql);
        $query->bindParam(':booking_number', $bookingNumber, PDO::PARAM_INT);
        $query->execute();
        $booking = $query->fetch(PDO::FETCH_OBJ);
        
        if (!$booking) {
            return array(
                'success' => false,
                'message' => 'Booking not found',
                'code' => 404
            );
        }
        
        // Check if payment already exists
        $sql_check = "SELECT * FROM tblpayments WHERE BookingNumber = :booking_number AND PaymentStatus = 'Completed'";
        $query_check = $dbh->prepare($sql_check);
        $query_check->bindParam(':booking_number', $bookingNumber, PDO::PARAM_INT);
        $query_check->execute();
        
        if ($query_check->rowCount() > 0) {
            return array(
                'success' => false,
                'message' => 'Payment already completed for this booking',
                'code' => 400
            );
        }
        
        // Calculate advance payment
        $advancePayment = calculateAdvancePayment($totalAmount);
        
        // Get gateway specific details
        $gatewayResponse = null;
        $transactionID = null;
        $paymentLink = null;
        
        if (PAYMENT_GATEWAY === 'razorpay') {
            $gatewayResponse = createRazorpayOrder($bookingNumber, $advancePayment, $userEmail, $userPhone, $userName);
            
            if (!$gatewayResponse || !isset($gatewayResponse['id'])) {
                logAuditAction(null, $bookingNumber, null, 'Payment Initiation Failed', 'Razorpay order creation failed');
                return array(
                    'success' => false,
                    'message' => 'Failed to create payment order',
                    'code' => 500
                );
            }
            
            $transactionID = $gatewayResponse['id'];
            $paymentLink = $gatewayResponse['short_url'] ?? null;
        }
        
        // Store payment record
        $sql_insert = "INSERT INTO tblpayments (BookingNumber, UserID, PaymentMethod, TransactionID, Amount, PaymentStatus, PaymentGateway, GatewayResponse)
                       VALUES (:booking_number, NULL, :payment_method, :transaction_id, :amount, 'Pending', :gateway, :gateway_response)";
        
        $query_insert = $dbh->prepare($sql_insert);
        $query_insert->bindParam(':booking_number', $bookingNumber, PDO::PARAM_INT);
        $query_insert->bindParam(':payment_method', $paymentMethod, PDO::PARAM_STR);
        $query_insert->bindParam(':transaction_id', $transactionID, PDO::PARAM_STR);
        $query_insert->bindParam(':amount', $advancePayment, PDO::PARAM_STR);
        $query_insert->bindParam(':gateway', PAYMENT_GATEWAY, PDO::PARAM_STR);
        $query_insert->bindParam(':gateway_response', json_encode($gatewayResponse), PDO::PARAM_STR);
        $query_insert->execute();
        
        // Update booking status
        $sql_update = "UPDATE tblbook SET BookingStatusFlow = :status, AdvancePaymentAmount = :advance_amount, TotalAmount = :total_amount 
                       WHERE BookingNumber = :booking_number";
        
        $query_update = $dbh->prepare($sql_update);
        $status = 'Payment Pending';
        $query_update->bindParam(':status', $status, PDO::PARAM_STR);
        $query_update->bindParam(':advance_amount', $advancePayment, PDO::PARAM_STR);
        $query_update->bindParam(':total_amount', $totalAmount, PDO::PARAM_STR);
        $query_update->bindParam(':booking_number', $bookingNumber, PDO::PARAM_INT);
        $query_update->execute();
        
        // Create notification
        createPaymentNotification($bookingNumber, $userEmail, $userPhone, 'Payment Initiated', 
            'Payment Initiated', "Your payment of ₹{$advancePayment} has been initiated. Please complete the payment.");
        
        logAuditAction(null, $bookingNumber, null, 'Payment Initiated', "Advance payment of ₹{$advancePayment} initiated");
        
        // Return success response
        return array(
            'success' => true,
            'message' => 'Payment initiated successfully',
            'code' => 200,
            'data' => array(
                'booking_number' => $bookingNumber,
                'transaction_id' => $transactionID,
                'advance_amount' => $advancePayment,
                'total_amount' => $totalAmount,
                'payment_gateway' => PAYMENT_GATEWAY,
                'payment_link' => $paymentLink,
                'key_id' => RAZORPAY_KEY_ID
            )
        );
        
    } catch (Exception $e) {
        logError('Payment Initiation Error: ' . $e->getMessage());
        return array(
            'success' => false,
            'message' => 'Payment initiation failed',
            'error' => $e->getMessage(),
            'code' => 500
        );
    }
}

/**
 * FUNCTION: Verify Payment
 * Verifies payment status from gateway
 */
function verifyPayment() {
    global $dbh;
    
    try {
        $bookingNumber = isset($_POST['booking_number']) ? intval($_POST['booking_number']) : null;
        $transactionID = isset($_POST['transaction_id']) ? sanitizeInput($_POST['transaction_id']) : null;
        $paymentSignature = isset($_POST['razorpay_signature']) ? sanitizeInput($_POST['razorpay_signature']) : null;
        
        if (!$bookingNumber || !$transactionID) {
            return array('success' => false, 'message' => 'Invalid parameters', 'code' => 400);
        }
        
        // Get payment record
        $sql = "SELECT * FROM tblpayments WHERE BookingNumber = :booking_number AND TransactionID = :transaction_id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':booking_number', $bookingNumber, PDO::PARAM_INT);
        $query->bindParam(':transaction_id', $transactionID, PDO::PARAM_STR);
        $query->execute();
        
        if ($query->rowCount() === 0) {
            return array('success' => false, 'message' => 'Payment record not found', 'code' => 404);
        }
        
        $payment = $query->fetch(PDO::FETCH_OBJ);
        
        // Verify with Razorpay
        if (PAYMENT_GATEWAY === 'razorpay' && $paymentSignature) {
            $isValid = verifyRazorpaySignature($transactionID, $paymentSignature, $_POST['razorpay_payment_id'] ?? null);
            
            if ($isValid) {
                // Update payment status
                $sql_update = "UPDATE tblpayments SET PaymentStatus = 'Completed', PaymentDate = NOW() 
                               WHERE BookingNumber = :booking_number AND TransactionID = :transaction_id";
                
                $query_update = $dbh->prepare($sql_update);
                $query_update->bindParam(':booking_number', $bookingNumber, PDO::PARAM_INT);
                $query_update->bindParam(':transaction_id', $transactionID, PDO::PARAM_STR);
                $query_update->execute();
                
                // Update booking status
                $sql_book_update = "UPDATE tblbook SET BookingStatusFlow = :status, PaymentStatus = 'Confirmed' 
                                    WHERE BookingNumber = :booking_number";
                
                $query_book = $dbh->prepare($sql_book_update);
                $status = 'Payment Confirmed';
                $query_book->bindParam(':status', $status, PDO::PARAM_STR);
                $query_book->bindParam(':booking_number', $bookingNumber, PDO::PARAM_INT);
                $query_book->execute();
                
                // Get booking details for notification
                $sql_book = "SELECT * FROM tblbook WHERE BookingNumber = :booking_number";
                $query_book = $dbh->prepare($sql_book);
                $query_book->bindParam(':booking_number', $bookingNumber, PDO::PARAM_INT);
                $query_book->execute();
                $booking = $query_book->fetch(PDO::FETCH_OBJ);
                
                // Create notification
                createPaymentNotification($bookingNumber, $booking->Email, $booking->PhoneNumber, 'Payment Success',
                    'Payment Successful', "Your payment of ₹{$payment->Amount} has been received. Your booking is under admin review.");
                
                logAuditAction(null, $bookingNumber, $payment->PaymentID, 'Payment Verified', 'Payment successfully verified and completed');
                
                return array(
                    'success' => true,
                    'message' => 'Payment verified successfully',
                    'code' => 200,
                    'data' => array(
                        'booking_number' => $bookingNumber,
                        'status' => 'completed'
                    )
                );
            }
        }
        
        return array('success' => false, 'message' => 'Payment verification failed', 'code' => 400);
        
    } catch (Exception $e) {
        logError('Payment Verification Error: ' . $e->getMessage());
        return array('success' => false, 'message' => 'Verification error', 'error' => $e->getMessage(), 'code' => 500);
    }
}

/**
 * FUNCTION: Create Razorpay Order
 */
function createRazorpayOrder($bookingNumber, $amount, $email, $phone, $name) {
    try {
        $auth = base64_encode(RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
        $orderId = 'BK-' . $bookingNumber . '-' . time();
        
        $data = json_encode(array(
            'amount' => $amount * 100, // Convert to paise
            'currency' => CURRENCY,
            'receipt' => $orderId,
            'customer_notify' => 1,
            'email_notify' => 1,
            'sms_notify' => 1,
            'description' => 'Vehicle Breakdown Assistance - Booking #' . $bookingNumber,
            'customer_id' => 'CUST-' . $bookingNumber
        ));
        
        $ch = curl_init('https://api.razorpay.com/v1/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpCode === 200 && isset($result['id'])) {
            return $result;
        }
        
        logError('Razorpay API Error: ' . $response);
        return null;
        
    } catch (Exception $e) {
        logError('Razorpay Order Creation Error: ' . $e->getMessage());
        return null;
    }
}

/**
 * FUNCTION: Verify Razorpay Signature
 */
function verifyRazorpaySignature($orderId, $signature, $paymentId) {
    $secret = RAZORPAY_KEY_SECRET;
    $hash = hash_hmac('sha256', $orderId . '|' . $paymentId, $secret);
    return ($hash === $signature);
}

/**
 * FUNCTION: Process Refund
 */
function processRefund() {
    global $dbh;
    
    try {
        // Check admin authentication
        requireAdmin();
        
        $bookingNumber = isset($_POST['booking_number']) ? intval($_POST['booking_number']) : null;
        $refundReason = isset($_POST['refund_reason']) ? sanitizeInput($_POST['refund_reason']) : null;
        $adminID = $_SESSION['vamsaid'] ?? null;
        
        if (!$bookingNumber) {
            return array('success' => false, 'message' => 'Booking number required', 'code' => 400);
        }
        
        // Get payment record
        $sql = "SELECT p.*, b.Email, b.PhoneNumber FROM tblpayments p 
                JOIN tblbook b ON p.BookingNumber = b.BookingNumber
                WHERE p.BookingNumber = :booking_number AND p.PaymentStatus = 'Completed'";
        
        $query = $dbh->prepare($sql);
        $query->bindParam(':booking_number', $bookingNumber, PDO::PARAM_INT);
        $query->execute();
        
        if ($query->rowCount() === 0) {
            return array('success' => false, 'message' => 'No completed payment found', 'code' => 404);
        }
        
        $payment = $query->fetch(PDO::FETCH_OBJ);
        
        // Process refund based on gateway
        $refundTransactionID = null;
        $isSuccessful = false;
        
        if (PAYMENT_GATEWAY === 'razorpay' && $payment->TransactionID) {
            $refundResponse = initiateRazorpayRefund($payment->TransactionID, $payment->Amount);
            
            if ($refundResponse && isset($refundResponse['id'])) {
                $refundTransactionID = $refundResponse['id'];
                $isSuccessful = true;
            }
        }
        
        // Create refund record
        $refundStatus = $isSuccessful ? 'Processing' : 'Failed';
        
        $sql_insert = "INSERT INTO tblrefunds (PaymentID, BookingNumber, RefundAmount, RefundReason, RefundStatus, RefundTransactionID, ProcessedBy)
                       VALUES (:payment_id, :booking_number, :amount, :reason, :status, :transaction_id, :processed_by)";
        
        $query_insert = $dbh->prepare($sql_insert);
        $query_insert->bindParam(':payment_id', $payment->PaymentID, PDO::PARAM_INT);
        $query_insert->bindParam(':booking_number', $bookingNumber, PDO::PARAM_INT);
        $query_insert->bindParam(':amount', $payment->Amount, PDO::PARAM_STR);
        $query_insert->bindParam(':reason', $refundReason, PDO::PARAM_STR);
        $query_insert->bindParam(':status', $refundStatus, PDO::PARAM_STR);
        $query_insert->bindParam(':transaction_id', $refundTransactionID, PDO::PARAM_STR);
        $query_insert->bindParam(':processed_by', $adminID, PDO::PARAM_STR);
        $query_insert->execute();
        
        $refundID = $dbh->lastInsertId();
        
        // Update booking status
        $sql_update = "UPDATE tblbook SET BookingStatusFlow = 'Refunded' WHERE BookingNumber = :booking_number";
        $query_update = $dbh->prepare($sql_update);
        $query_update->bindParam(':booking_number', $bookingNumber, PDO::PARAM_INT);
        $query_update->execute();
        
        // Create notification
        createPaymentNotification($bookingNumber, $payment->Email, $payment->PhoneNumber, 'Refund Initiated',
            'Refund Initiated', "Your refund of ₹{$payment->Amount} has been initiated. It will be processed within " . MAX_REFUND_PROCESSING_TIME . " business days.");
        
        logAuditAction($adminID, $bookingNumber, $payment->PaymentID, 'Refund Initiated', 
            "Refund of ₹{$payment->Amount} initiated. Reason: {$refundReason}");
        
        return array(
            'success' => $isSuccessful,
            'message' => $isSuccessful ? 'Refund initiated successfully' : 'Refund initiation failed',
            'code' => $isSuccessful ? 200 : 400,
            'data' => array(
                'refund_id' => $refundID,
                'amount' => $payment->Amount,
                'status' => $refundStatus
            )
        );
        
    } catch (Exception $e) {
        logError('Refund Processing Error: ' . $e->getMessage());
        return array('success' => false, 'message' => 'Refund processing failed', 'error' => $e->getMessage(), 'code' => 500);
    }
}

/**
 * FUNCTION: Initiate Razorpay Refund
 */
function initiateRazorpayRefund($paymentId, $amount) {
    try {
        $auth = base64_encode(RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
        
        $data = json_encode(array(
            'amount' => $amount * 100
        ));
        
        $ch = curl_init("https://api.razorpay.com/v1/payments/{$paymentId}/refund");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpCode === 200 && isset($result['id'])) {
            return $result;
        }
        
        logError('Razorpay Refund Error: ' . $response);
        return null;
        
    } catch (Exception $e) {
        logError('Razorpay Refund Error: ' . $e->getMessage());
        return null;
    }
}

/**
 * FUNCTION: Get Charge Details
 */
function getChargeDetails() {
    try {
        $serviceType = isset($_GET['service_type']) ? sanitizeInput($_GET['service_type']) : null;
        
        if (!$serviceType) {
            return array('success' => false, 'message' => 'Service type required', 'code' => 400);
        }
        
        $service = getServiceCharge($serviceType);
        
        if (!$service) {
            return array('success' => false, 'message' => 'Service not found', 'code' => 404);
        }
        
        return array(
            'success' => true,
            'code' => 200,
            'data' => $service
        );
        
    } catch (Exception $e) {
        return array('success' => false, 'message' => 'Error retrieving charges', 'error' => $e->getMessage(), 'code' => 500);
    }
}

/**
 * FUNCTION: Handle Payment Webhook
 */
function handlePaymentWebhook() {
    global $dbh;
    
    try {
        $webhookBody = file_get_contents('php://input');
        $webhookData = json_decode($webhookBody, true);
        
        // Verify webhook signature
        if (PAYMENT_GATEWAY === 'razorpay') {
            $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? null;
            $isValid = hash_equals(
                hash_hmac('sha256', $webhookBody, RAZORPAY_WEBHOOK_SECRET),
                $signature ?? ''
            );
            
            if (!$isValid) {
                return array('success' => false, 'message' => 'Invalid signature', 'code' => 401);
            }
        }
        
        // Handle different event types
        $eventType = $webhookData['event'] ?? null;
        
        if ($eventType === 'payment.authorized' || $eventType === 'payment.captured') {
            $paymentId = $webhookData['payload']['payment']['entity']['id'] ?? null;
            
            if ($paymentId) {
                // Update payment status in database
                // Implementation depends on your gateway and data structure
            }
        }
        
        logAuditAction(null, null, null, 'Webhook Received', 'Payment gateway webhook: ' . $eventType);
        
        return array('success' => true, 'code' => 200);
        
    } catch (Exception $e) {
        logError('Webhook Processing Error: ' . $e->getMessage());
        return array('success' => false, 'message' => 'Webhook processing failed', 'code' => 500);
    }
}

/**
 * HELPER: Create Payment Notification
 */
function createPaymentNotification($bookingNumber, $email, $phone, $type, $subject, $message) {
    global $dbh;
    
    try {
        $sql = "INSERT INTO tblpaymentnotifications (BookingNumber, UserEmail, UserPhone, NotificationType, Subject, Message, Status)
                VALUES (:booking_number, :email, :phone, :type, :subject, :message, 'Pending')";
        
        $query = $dbh->prepare($sql);
        $query->bindParam(':booking_number', $bookingNumber, PDO::PARAM_INT);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':phone', $phone, PDO::PARAM_STR);
        $query->bindParam(':type', $type, PDO::PARAM_STR);
        $query->bindParam(':subject', $subject, PDO::PARAM_STR);
        $query->bindParam(':message', $message, PDO::PARAM_STR);
        
        return $query->execute();
        
    } catch (Exception $e) {
        logError('Notification Creation Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * HELPER: Sanitize Input
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * HELPER: Log Audit Action
 */
function logAuditAction($adminID, $bookingNumber, $paymentID, $action, $description, $oldValue = null, $newValue = null) {
    global $dbh;
    
    if (!ENABLE_AUDIT_LOG) return;
    
    try {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        
        $sql = "INSERT INTO tblauditlog (AdminID, BookingNumber, PaymentID, Action, Description, OldValue, NewValue, IPAddress)
                VALUES (:admin_id, :booking_number, :payment_id, :action, :description, :old_value, :new_value, :ip_address)";
        
        $query = $dbh->prepare($sql);
        $query->bindParam(':admin_id', $adminID, PDO::PARAM_INT);
        $query->bindParam(':booking_number', $bookingNumber, PDO::PARAM_INT);
        $query->bindParam(':payment_id', $paymentID, PDO::PARAM_INT);
        $query->bindParam(':action', $action, PDO::PARAM_STR);
        $query->bindParam(':description', $description, PDO::PARAM_STR);
        $query->bindParam(':old_value', $oldValue, PDO::PARAM_STR);
        $query->bindParam(':new_value', $newValue, PDO::PARAM_STR);
        $query->bindParam(':ip_address', $ipAddress, PDO::PARAM_STR);
        
        $query->execute();
        
    } catch (Exception $e) {
        logError('Audit Log Error: ' . $e->getMessage());
    }
}

/**
 * HELPER: Log Errors
 */
function logError($message) {
    $logFile = __DIR__ . '/../../logs/payment-error.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

/**
 * HELPER: Require Admin
 */
function requireAdmin() {
    if (!isset($_SESSION['vamsaid']) || empty($_SESSION['vamsaid'])) {
        http_response_code(401);
        die(json_encode(array('success' => false, 'message' => 'Admin authentication required')));
    }
}

?>
