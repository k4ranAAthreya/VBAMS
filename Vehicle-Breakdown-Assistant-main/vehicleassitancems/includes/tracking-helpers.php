<?php
/**
 * Tracking Helper Functions
 *
 * File: vehicleassitancems/includes/tracking-helpers.php
 * Purpose: Helper functions for status translation, timeline generation, and UI elements
 */

/**
 * Translate database status to customer-friendly label
 *
 * @param string $status - Database status value (NULL, 'Approved', 'Rejected', 'On The Way', 'Completed')
 * @return string - Human-readable status label
 */
function getStatusLabel($status) {
    $labels = array(
        NULL => 'Request Submitted',
        '' => 'Request Submitted',
        'Approved' => 'Request Accepted',
        'Rejected' => 'Service Declined',
        'On The Way' => 'Driver On The Way',
        'Completed' => 'Service Completed',
        'pending' => 'Request Submitted',
        'accepted' => 'Request Accepted',
        'driver_assigned' => 'Driver Assigned',
        'on_the_way' => 'Driver On The Way',
        'in_progress' => 'Service In Progress',
        'completed' => 'Service Completed',
        'cancelled' => 'Service Cancelled'
    );

    return isset($labels[$status]) ? $labels[$status] : 'Unknown Status';
}

/**
 * Get CSS color/badge color for status
 *
 * @param string $status - Database status value
 * @return string - Bootstrap color class name (primary, success, danger, warning, info)
 */
function getStatusBadgeColor($status) {
    $colors = array(
        NULL => 'info',           // Blue - pending
        '' => 'info',
        'Approved' => 'primary',  // Dark blue - accepted
        'Rejected' => 'danger',   // Red - declined
        'On The Way' => 'warning', // Yellow/Orange - in progress
        'Completed' => 'success',  // Green - completed
        'pending' => 'info',
        'accepted' => 'primary',
        'driver_assigned' => 'primary',
        'on_the_way' => 'warning',
        'in_progress' => 'warning',
        'completed' => 'success',
        'cancelled' => 'danger'
    );

    return isset($colors[$status]) ? $colors[$status] : 'secondary';
}

/**
 * Get icon for each status
 *
 * @param string $status - Database status value
 * @return string - Font Awesome icon class
 */
function getStatusIcon($status) {
    $icons = array(
        NULL => 'fa-clock-o',           // Clock - pending
        '' => 'fa-clock-o',
        'Approved' => 'fa-check-circle', // Check circle - accepted
        'Rejected' => 'fa-times-circle', // X circle - declined
        'On The Way' => 'fa-car',        // Car - in transit
        'Completed' => 'fa-check-circle', // Check - completed
        'pending' => 'fa-clock-o',
        'accepted' => 'fa-check',
        'driver_assigned' => 'fa-user-circle',
        'on_the_way' => 'fa-car',
        'in_progress' => 'fa-spinner',
        'completed' => 'fa-check-circle',
        'cancelled' => 'fa-ban'
    );

    return isset($icons[$status]) ? $icons[$status] : 'fa-question-circle';
}

/**
 * Get friendly description message for status
 *
 * @param string $status - Database status value
 * @return string - Description message
 */
function getStatusMessage($status) {
    $messages = array(
        NULL => 'Your breakdown assistance request has been submitted. We will review it shortly.',
        '' => 'Your breakdown assistance request has been submitted. We will review it shortly.',
        'Approved' => 'Your request has been accepted by BreakdownBuddy. A driver will be assigned soon.',
        'Rejected' => 'Unfortunately, your request was declined. Please contact us for more information.',
        'On The Way' => 'Your assigned driver is on the way to your location. Please stay available.',
        'Completed' => 'Thank you for using BreakdownBuddy! Please take a moment to rate our service.',
        'pending' => 'Your request is pending approval.',
        'accepted' => 'Your request has been accepted.',
        'driver_assigned' => 'A driver has been assigned to your request.',
        'on_the_way' => 'Your driver is on the way.',
        'in_progress' => 'Service is in progress.',
        'completed' => 'Service completed successfully!',
        'cancelled' => 'Your request has been cancelled.'
    );

    return isset($messages[$status]) ? $messages[$status] : 'Status unknown. Please contact support.';
}

/**
 * Get service timeline array
 *
 * Shows all possible status progression steps
 *
 * @return array - Timeline steps with labels and descriptions
 */
function getServiceTimeline() {
    return array(
        array(
            'id' => 1,
            'status' => 'submitted',
            'label' => 'Request Submitted',
            'description' => 'Your request has been received',
            'icon' => 'fa-clock-o'
        ),
        array(
            'id' => 2,
            'status' => 'accepted',
            'label' => 'Request Accepted',
            'description' => 'BreakdownBuddy has accepted your request',
            'icon' => 'fa-check'
        ),
        array(
            'id' => 3,
            'status' => 'driver_assigned',
            'label' => 'Driver Assigned',
            'description' => 'A driver has been assigned',
            'icon' => 'fa-user-circle'
        ),
        array(
            'id' => 4,
            'status' => 'on_the_way',
            'label' => 'Driver On The Way',
            'description' => 'Your driver is heading to your location',
            'icon' => 'fa-car'
        ),
        array(
            'id' => 5,
            'status' => 'completed',
            'label' => 'Service Completed',
            'description' => 'Service has been completed successfully',
            'icon' => 'fa-check-circle'
        )
    );
}

/**
 * Convert database status to timeline status for progress display
 *
 * @param string $dbStatus - Database status value
 * @param string $assignTo - Driver ID (if assigned)
 * @return string - Timeline status identifier
 */
function getTimelineStatus($dbStatus, $assignTo = null) {
    switch($dbStatus) {
        case NULL:
        case '':
            return 'submitted';
        case 'Approved':
            return ($assignTo && !empty($assignTo)) ? 'driver_assigned' : 'accepted';
        case 'On The Way':
            return 'on_the_way';
        case 'Completed':
            return 'completed';
        case 'Rejected':
            return 'rejected';
        default:
            return 'submitted';
    }
}

/**
 * Check if service is completed
 *
 * @param string $status - Database status value
 * @return bool - True if service is completed
 */
function isServiceCompleted($status) {
    return (strtolower($status) === 'completed' || $status === 'Completed');
}

/**
 * Check if service is rejected/cancelled
 *
 * @param string $status - Database status value
 * @return bool - True if service is rejected
 */
function isServiceRejected($status) {
    return (strtolower($status) === 'rejected' || $status === 'Rejected');
}

/**
 * Check if driver is assigned
 *
 * @param string $dbStatus - Database status value
 * @param string $assignTo - Driver ID
 * @return bool - True if driver is assigned
 */
function isDriverAssigned($dbStatus, $assignTo) {
    return (($dbStatus === 'Approved' || $dbStatus === 'On The Way' || $dbStatus === 'Completed')
            && !empty($assignTo));
}

/**
 * Format timestamp for display
 *
 * @param string $timestamp - Database timestamp
 * @return string - Formatted time (e.g., "2 hours ago" or "Jan 15, 2024 2:30 PM")
 */
function formatTime($timestamp) {
    if (empty($timestamp)) {
        return 'Not yet';
    }

    $time = strtotime($timestamp);
    $now = time();
    $diff = $now - $time;

    // Less than 1 minute
    if ($diff < 60) {
        return 'Just now';
    }

    // Less than 1 hour
    if ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    }

    // Less than 24 hours
    if ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    }

    // More than 24 hours - show date
    return date('M d, Y g:i A', $time);
}

/**
 * Format phone number for display
 *
 * @param string $phone - Phone number
 * @return string - Formatted phone (e.g., +91-98765-43210)
 */
function formatPhoneNumber($phone) {
    if (empty($phone)) {
        return 'N/A';
    }

    $phone = preg_replace('/[^0-9]/', '', $phone);

    if (strlen($phone) === 10) {
        return '+91-' . substr($phone, 0, 5) . '-' . substr($phone, 5);
    }

    return $phone;
}

/**
 * Get rating stars HTML
 *
 * @param int $rating - Rating value (1-5)
 * @param bool $interactive - If true, return clickable stars; if false, return display stars
 * @return string - HTML markup for stars
 */
function getRatingStars($rating = 0, $interactive = false) {
    $html = '<div class="star-rating">';

    for ($i = 1; $i <= 5; $i++) {
        if ($interactive) {
            $html .= '<label class="star" data-value="' . $i . '">';
            $html .= '<i class="fa fa-star' . ($i <= $rating ? '' : '-o') . '"></i>';
            $html .= '</label>';
        } else {
            $html .= '<i class="fa fa-star' . ($i <= $rating ? '' : '-o') . '" style="color: #ffc107;"></i>';
        }
    }

    $html .= '</div>';
    return $html;
}

/**
 * Validate booking ID format
 *
 * @param mixed $bookingId - Booking ID to validate
 * @return bool - True if valid 9-digit number
 */
function isValidBookingId($bookingId) {
    return is_numeric($bookingId) && strlen((string)$bookingId) === 9;
}

/**
 * Sanitize booking ID input
 *
 * @param mixed $bookingId - Raw booking ID input
 * @return string|false - Sanitized booking ID or false if invalid
 */
function sanitizeBookingId($bookingId) {
    $sanitized = preg_replace('/[^0-9]/', '', (string)$bookingId);

    if (strlen($sanitized) === 9) {
        return $sanitized;
    }

    return false;
}

?>
