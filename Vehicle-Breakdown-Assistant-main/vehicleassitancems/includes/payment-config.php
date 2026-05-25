<?php
/**
 * PAYMENT SYSTEM CONFIGURATION
 * Advanced Payment Confirmation System for Vehicle Breakdown Assistance
 * 
 * This configuration file contains all payment-related settings and constants
 */

// =========================================================================
// PAYMENT GATEWAY CONFIGURATION
// =========================================================================

// Razorpay API Keys (Replace with your actual keys)
define('RAZORPAY_KEY_ID', 'rzp_test_xxxxxx');
define('RAZORPAY_KEY_SECRET', 'xxxxxx_secret');
define('RAZORPAY_WEBHOOK_SECRET', 'xxxxxx_webhook');

// Payment Gateway Settings
define('PAYMENT_GATEWAY', 'razorpay'); // 'razorpay', 'paypal', 'stripe'
define('PAYMENT_ENVIRONMENT', 'sandbox'); // 'sandbox' or 'production'
define('CURRENCY', 'INR');

// =========================================================================
// ADVANCE PAYMENT SETTINGS
// =========================================================================

// Default advance payment percentage (can be overridden per service)
define('DEFAULT_ADVANCE_PAYMENT_PERCENTAGE', 50); // 50% of total amount

// Minimum advance payment
define('MINIMUM_ADVANCE_PAYMENT', 100); // INR

// Maximum advance payment per booking
define('MAXIMUM_ADVANCE_PAYMENT', 50000); // INR

// =========================================================================
// BOOKING STATUS FLOW STATES
// =========================================================================

define('BOOKING_STATUS_PAYMENT_PENDING', 'Payment Pending');
define('BOOKING_STATUS_PAYMENT_CONFIRMED', 'Payment Confirmed');
define('BOOKING_STATUS_PENDING_APPROVAL', 'Pending Admin Approval');
define('BOOKING_STATUS_CONFIRMED', 'Confirmed');
define('BOOKING_STATUS_ON_THE_WAY', 'On The Way');
define('BOOKING_STATUS_COMPLETED', 'Completed');
define('BOOKING_STATUS_REJECTED', 'Rejected');
define('BOOKING_STATUS_REFUNDED', 'Refunded');

// Valid status transitions
$BOOKING_STATUS_TRANSITIONS = array(
    'Payment Pending' => array('Payment Confirmed', 'Cancelled'),
    'Payment Confirmed' => array('Pending Admin Approval', 'Refunded'),
    'Pending Admin Approval' => array('Confirmed', 'Rejected'),
    'Confirmed' => array('On The Way', 'Rejected'),
    'On The Way' => array('Completed', 'Rejected'),
    'Completed' => array(),
    'Rejected' => array('Refunded'),
    'Refunded' => array()
);
define('BOOKING_STATUS_TRANSITIONS', $BOOKING_STATUS_TRANSITIONS);

// =========================================================================
// PAYMENT STATUS CONSTANTS
// =========================================================================

define('PAYMENT_STATUS_PENDING', 'Pending');
define('PAYMENT_STATUS_COMPLETED', 'Completed');
define('PAYMENT_STATUS_FAILED', 'Failed');
define('PAYMENT_STATUS_CANCELLED', 'Cancelled');

// =========================================================================
// REFUND SETTINGS
// =========================================================================

// Automatic refund settings
define('AUTO_REFUND_ON_REJECTION', true); // Automatically refund if booking rejected
define('MAX_REFUND_PROCESSING_TIME', 5); // Days to process refund
define('REFUND_PERCENTAGE_ON_REJECTION', 100); // 100% refund

// Cancellation policy
define('CANCELLATION_ALLOWED_BEFORE_HOURS', 2); // User can cancel 2 hours before booking
define('CANCELLATION_REFUND_PERCENTAGE', 80); // 80% refund if cancelled within allowed time
define('CANCELLATION_REFUND_PERCENTAGE_LATE', 50); // 50% refund if cancelled late

// =========================================================================
// SERVICE TYPES AND CHARGES
// =========================================================================

$SERVICE_TYPES = array(
    'Bike Towing' => array(
        'name' => 'Bike Towing Service',
        'description' => 'Motorcycle and two-wheeler towing to nearest workshop',
        'baseCharge' => 1000,
        'advancePaymentPercentage' => 50,
        'advancePaymentFixed' => 500,
        'pricePerKM' => 0,
        'minimumCharge' => 1000,
        'maximumCharge' => 1000
    ),
    'Car Towing' => array(
        'name' => 'Car Towing Service',
        'description' => 'Four-wheeler car towing to nearest workshop',
        'baseCharge' => 3000,
        'advancePaymentPercentage' => 50,
        'advancePaymentFixed' => 500,
        'pricePerKM' => 0,
        'minimumCharge' => 3000,
        'maximumCharge' => 3000
    ),
    'Truck Towing' => array(
        'name' => 'Truck Towing Service',
        'description' => 'Heavy vehicle and truck towing to nearest workshop',
        'baseCharge' => 6000,
        'advancePaymentPercentage' => 50,
        'advancePaymentFixed' => 500,
        'pricePerKM' => 0,
        'minimumCharge' => 6000,
        'maximumCharge' => 6000
    )
);
define('SERVICE_TYPES', $SERVICE_TYPES);

// =========================================================================
// EXTRA SERVICES (OPTIONAL ADD-ONS)
// =========================================================================

$EXTRA_SERVICES = array(
    'Premium Assistance' => 200,
    'Priority Queue' => 150,
    'Night Service Charge' => 100,
    'Holiday Service Charge' => 250,
    'Multi-Service Package' => 500
);
define('EXTRA_SERVICES', $EXTRA_SERVICES);

// =========================================================================
// EMAIL CONFIGURATION FOR NOTIFICATIONS
// =========================================================================

define('NOTIFICATION_EMAIL_FROM', 'noreply@vehiclebreakdownassistance.com');
define('NOTIFICATION_EMAIL_FROM_NAME', 'Vehicle Breakdown Assistance');
define('ADMIN_NOTIFICATION_EMAIL', 'admin@vehiclebreakdownassistance.com');

// Email templates folder
define('EMAIL_TEMPLATES_PATH', __DIR__ . '/email-templates/');

// =========================================================================
// SMS CONFIGURATION (Optional)
// =========================================================================

// Twilio Configuration (if using SMS notifications)
define('TWILIO_ACCOUNT_SID', 'your_account_sid');
define('TWILIO_AUTH_TOKEN', 'your_auth_token');
define('TWILIO_PHONE_NUMBER', '+1234567890');
define('ENABLE_SMS_NOTIFICATIONS', false);

// =========================================================================
// SECURITY SETTINGS
// =========================================================================

// Payment validation timeout (seconds)
define('PAYMENT_VALIDATION_TIMEOUT', 3600); // 1 hour

// Maximum attempts for payment retry
define('MAX_PAYMENT_RETRY_ATTEMPTS', 3);

// Session timeout for payment process
define('PAYMENT_SESSION_TIMEOUT', 1800); // 30 minutes

// =========================================================================
// AUDIT LOG SETTINGS
// =========================================================================

define('ENABLE_AUDIT_LOG', true);
define('LOG_PAYMENT_TRANSACTIONS', true);
define('LOG_ADMIN_ACTIONS', true);
define('LOG_REFUND_OPERATIONS', true);

// =========================================================================
// PAGINATION AND LIMITS
// =========================================================================

define('PAYMENTS_PER_PAGE', 20);
define('BOOKINGS_PER_PAGE', 25);
define('AUDIT_LOG_PER_PAGE', 50);

// =========================================================================
// PAYMENT RECONCILIATION SETTINGS
// =========================================================================

define('AUTO_RECONCILIATION_ENABLED', true);
define('RECONCILIATION_FREQUENCY', 'daily'); // 'hourly', 'daily', 'weekly'
define('RECONCILIATION_TIME', '02:00'); // 2 AM daily

// =========================================================================
// HELPER FUNCTION: GET SERVICE CHARGE
// Returns: Array with charge details for a service type
// =========================================================================

function getServiceCharge($serviceType) {
    $services = SERVICE_TYPES;
    
    if (!isset($services[$serviceType])) {
        return null;
    }
    
    return $services[$serviceType];
}

// =========================================================================
// HELPER FUNCTION: CALCULATE ADVANCE PAYMENT
// =========================================================================

function calculateAdvancePayment($totalAmount, $advancePercentage = null) {
    // Fixed advance payment of ₹500 for towing services
    $advancePayment = 500;
    
    // Ensure it's within min/max bounds
    $advancePayment = max($advancePayment, MINIMUM_ADVANCE_PAYMENT);
    $advancePayment = min($advancePayment, MAXIMUM_ADVANCE_PAYMENT);
    
    return round($advancePayment, 2);
}

// =========================================================================
// HELPER FUNCTION: CALCULATE TOTAL CHARGE
// =========================================================================

function calculateTotalCharge($serviceType, $distance = 0, $extraServices = array()) {
    $service = getServiceCharge($serviceType);
    
    if (!$service) {
        return 0;
    }
    
    $total = $service['baseCharge'];
    
    // Add distance-based charge
    if ($distance > 0 && $service['pricePerKM'] > 0) {
        $total += ($distance * $service['pricePerKM']);
    }
    
    // Apply minimum and maximum charges
    $total = max($total, $service['minimumCharge']);
    if ($service['maximumCharge'] > 0) {
        $total = min($total, $service['maximumCharge']);
    }
    
    // Add extra services
    if (!empty($extraServices)) {
        $services = EXTRA_SERVICES;
        foreach ($extraServices as $serviceName) {
            if (isset($services[$serviceName])) {
                $total += $services[$serviceName];
            }
        }
    }
    
    return round($total, 2);
}

// =========================================================================
// HELPER FUNCTION: GET BOOKING STATUS TRANSITIONS
// =========================================================================

function getValidStatusTransitions($currentStatus) {
    $transitions = BOOKING_STATUS_TRANSITIONS;
    return isset($transitions[$currentStatus]) ? $transitions[$currentStatus] : array();
}

// =========================================================================
// HELPER FUNCTION: VALIDATE STATUS TRANSITION
// =========================================================================

function isValidStatusTransition($fromStatus, $toStatus) {
    $validTransitions = getValidStatusTransitions($fromStatus);
    return in_array($toStatus, $validTransitions);
}

?>
