<?php
/**
 * Customer Service Tracking Page
 *
 * File: vehicleassitancems/track-service.php
 * Purpose: Public page for customers to track their breakdown assistance request in real-time
 *
 * Features:
 * - Search by Booking ID
 * - Real-time status updates via AJAX polling
 * - Visual timeline/progress tracker
 * - Driver information display
 * - Rating form (for completed services)
 * - No login required
 */

include('includes/dbconnection.php');
include('includes/tracking-helpers.php');

error_reporting(0);

$booking_found = false;
$booking_data = null;
$error_message = '';
$search_submitted = false;

// Handle search form submission
if (isset($_GET['booking_id']) && !empty($_GET['booking_id'])) {
    $search_submitted = true;
    $booking_id = sanitizeBookingId($_GET['booking_id']);

    if (!$booking_id) {
        $error_message = 'Invalid Booking ID format. Please enter a 9-digit booking number.';
    } else {
        // Fetch booking from database
        try {
            $sql = "SELECT * FROM tblbook WHERE BookingNumber = :booking_id LIMIT 1";
            $query = $dbh->prepare($sql);
            $query->bindParam(':booking_id', $booking_id, PDO::PARAM_STR);
            $query->execute();

            if ($query->rowCount() > 0) {
                $booking_found = true;
                $booking_data = $query->fetch(PDO::FETCH_OBJ);
            } else {
                $error_message = 'Booking ID not found. Please check and try again.';
            }
        } catch (Exception $e) {
            $error_message = 'Error retrieving booking information. Please try again later.';
        }
    }
}

?>
<!DOCTYPE html>
<html class="no-js" lang="zxx">
<head>
    <title>VBAMS || Track Service</title>

    <link rel="stylesheet" href="css1/normalize.css">
    <link rel="stylesheet" href="css1/animate.css">
    <link rel="stylesheet" href="css1/bootstrap.min.css">
    <link rel="stylesheet" href="css1/meanmenu.min.css">
    <link rel="stylesheet" href="css1/font-awesome.min.css">
    <link rel="stylesheet" href="css1/icofont.css">
    <link rel="stylesheet" href="css1/change-text.css">
    <link rel="stylesheet" href="css1/jquery.mb.YTPlayer.min.css">
    <link rel="stylesheet" href="css1/main.css">
    <link rel="stylesheet" href="css1/owl.carousel.css">
    <link rel="stylesheet" href="css1/owl.theme.css">
    <link rel="stylesheet" href="css1/owl.transitions.css">
    <link rel="stylesheet" href="lib/css/nivo-slider.css">
    <link rel="stylesheet" href="lib/css/preview.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css1/responsive.css">

    <style>
        .track-service-container {
            background: #f8f9fa;
            padding: 40px 0;
        }

        .search-box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .search-box h3 {
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .search-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-form input {
            flex: 1;
            min-width: 250px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .search-form input:focus {
            outline: none;
            border-color: #667eea;
        }

        .search-form button {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .search-form button:hover {
            transform: translateY(-2px);
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #f5c6cb;
        }

        .tracking-results {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .tracking-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .booking-number {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            margin-top: 10px;
        }

        .status-badge.info { background: #d1ecf1; color: #0c5460; }
        .status-badge.primary { background: #cfe2ff; color: #084298; }
        .status-badge.warning { background: #fff3cd; color: #664d03; }
        .status-badge.success { background: #d1e7dd; color: #0f5132; }
        .status-badge.danger { background: #f8d7da; color: #842029; }

        .timeline {
            position: relative;
            margin: 40px 0;
        }

        .timeline-item {
            display: flex;
            margin-bottom: 30px;
        }

        .timeline-marker {
            position: relative;
            width: 50px;
            text-align: center;
            margin-right: 30px;
        }

        .timeline-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            transition: all 0.3s;
            border: 3px solid transparent;
        }

        .timeline-item.completed .timeline-circle {
            background: #28a745;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }

        .timeline-item.active .timeline-circle {
            background: #667eea;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
            border-color: #764ba2;
        }

        .timeline-marker::before {
            content: '';
            position: absolute;
            top: 50px;
            left: 50%;
            width: 2px;
            height: 60px;
            background: #e0e0e0;
            transform: translateX(-50%);
        }

        .timeline-item.completed .timeline-marker::before {
            background: #28a745;
        }

        .timeline-item:last-child .timeline-marker::before {
            display: none;
        }

        .timeline-content {
            padding: 15px 0;
            padding-top: 5px;
        }

        .timeline-content h4 {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .timeline-content p {
            color: #666;
            margin: 0;
            font-size: 13px;
        }

        .timeline-content .time-ago {
            color: #999;
            font-size: 12px;
            margin-top: 5px;
        }

        .driver-info {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin: 30px 0;
            border-left: 4px solid #667eea;
        }

        .driver-info h4 {
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .driver-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .driver-detail-item {
            padding: 15px;
            background: white;
            border-radius: 5px;
        }

        .driver-detail-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .driver-detail-value {
            font-weight: 600;
            color: #333;
            font-size: 15px;
        }

        .driver-call-btn {
            grid-column: 1 / -1;
            padding: 12px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .driver-call-btn:hover {
            opacity: 0.9;
            text-decoration: none;
            color: white;
        }

        .request-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .detail-item {
            padding: 15px;
            background: white;
            border-radius: 5px;
        }

        .detail-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .detail-value {
            font-weight: 600;
            color: #333;
            word-break: break-word;
        }

        .refresh-indicator {
            text-align: center;
            padding: 10px;
            background: #fff3cd;
            border-radius: 5px;
            color: #664d03;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .rating-section {
            background: #e7f5e9;
            padding: 30px;
            border-radius: 8px;
            margin-top: 30px;
            border-left: 4px solid #28a745;
            text-align: center;
        }

        .rating-section h4 {
            color: #155724;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .rating-section p {
            color: #155724;
            margin-bottom: 20px;
        }

        .rating-btn {
            padding: 12px 30px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
        }

        .rating-btn:hover {
            background: #218838;
        }

        /* Rating Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            overflow: auto;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .modal-close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            line-height: 20px;
        }

        .modal-close:hover {
            color: #000;
        }

        .modal h3 {
            color: #333;
            margin-bottom: 25px;
            margin-top: 0;
            clear: both;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .star-rating {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .star {
            font-size: 30px;
            color: #ddd;
            cursor: pointer;
            transition: all 0.2s;
        }

        .star:hover,
        .star.active {
            color: #ffc107;
            transform: scale(1.2);
        }

        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-family: inherit;
            resize: vertical;
            min-height: 100px;
        }

        .submit-rating-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
        }

        .submit-rating-btn:hover {
            opacity: 0.9;
        }

        .submit-rating-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .success-message {
            background: #d1e7dd;
            color: #0f5132;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #d1e7dd;
        }

        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }

            .search-form input,
            .search-form button {
                width: 100%;
            }

            .driver-details,
            .request-details {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                padding: 25px;
            }
        }
    </style>

    <script src="js/vendor/modernizr-2.8.3.min.js"></script>
</head>
<body>
    <?php include_once('includes/header.php'); ?>

    <div class="page-title-area overlay">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-title">
                        <h2>Track Your Service</h2>
                    </div>
                    <div class="page-title-menu">
                        <ul>
                            <li><a href="index.php">Home</a> <span> / </span> </li>
                            <li><a href="track-service.php">Track Service</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="track-service-container">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">

                    <!-- Search Box -->
                    <div class="search-box">
                        <h3><i class="fa fa-search"></i> Track Your Request</h3>
                        <form method="GET" class="search-form">
                            <input
                                type="text"
                                name="booking_id"
                                placeholder="Enter your Booking ID (9 digits)"
                                maxlength="9"
                                pattern="[0-9]{9}"
                                value="<?php echo isset($_GET['booking_id']) ? htmlspecialchars($_GET['booking_id']) : ''; ?>"
                                required>
                            <button type="submit"><i class="fa fa-arrow-right"></i> Track</button>
                        </form>
                    </div>

                    <!-- Error Message -->
                    <?php if ($search_submitted && $error_message): ?>
                        <div class="error-message">
                            <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Tracking Results -->
                    <?php if ($booking_found && $booking_data): ?>
                        <div class="tracking-results">

                            <!-- Refresh Indicator -->
                            <div class="refresh-indicator">
                                <i class="fa fa-sync-alt"></i> Auto-refreshing every 5 seconds...
                            </div>

                            <!-- Header -->
                            <div class="tracking-header" id="trackingHeader">
                                <div class="booking-number">
                                    Booking #<?php echo htmlspecialchars($booking_data->BookingNumber); ?>
                                </div>
                                <div id="currentStatusLabel">
                                    <?php echo getStatusLabel($booking_data->Status); ?>
                                </div>
                                <div class="status-badge" id="currentStatusBadge">
                                    <?php echo getStatusLabel($booking_data->Status); ?>
                                </div>
                            </div>

                            <!-- Status Message -->
                            <div style="text-align: center; color: #666; margin-bottom: 30px; font-size: 14px;">
                                <p id="statusMessage">
                                    <?php echo getStatusMessage($booking_data->Status); ?>
                                </p>
                            </div>

                            <!-- Refund Alert (when booking is rejected) -->
                            <?php 
                            // Check if booking is rejected and show refund message
                            $isRejected = false;
                            $refundInfo = null;
                            
                            if ($booking_data && (
                                $booking_data->Status === 'Rejected' || 
                                $booking_data->BookingStatusFlow === 'Rejected' ||
                                strpos(strtolower($booking_data->Status), 'reject') !== false
                            )) {
                                // Try to fetch refund information
                                try {
                                    $refundSql = "SELECT * FROM tblrefunds WHERE BookingNumber = :booking_id LIMIT 1";
                                    $refundQuery = $dbh->prepare($refundSql);
                                    $refundQuery->bindParam(':booking_id', $booking_data->BookingNumber, PDO::PARAM_STR);
                                    $refundQuery->execute();
                                    
                                    if ($refundQuery->rowCount() > 0) {
                                        $refundInfo = $refundQuery->fetch(PDO::FETCH_OBJ);
                                        $isRejected = true;
                                    } else {
                                        // No refund record found, but booking is rejected
                                        $isRejected = true;
                                    }
                                } catch (Exception $e) {
                                    // Table might not exist, check just by status
                                    $isRejected = true;
                                }
                            }
                            
                            if ($isRejected):
                            ?>
                            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; border-radius: 5px; margin-bottom: 30px;">
                                <div style="color: #856404; font-weight: 600; margin-bottom: 10px;">
                                    <i class="fa fa-exclamation-circle"></i> Booking Cancelled
                                </div>
                                <div style="color: #856404; font-size: 14px; line-height: 1.6;">
                                    <p>Your booking request has been rejected by our admin team.</p>
                                    <?php if ($refundInfo && isset($refundInfo->RefundAmount)): ?>
                                        <p style="font-weight: 600; margin-top: 10px;">
                                            💰 Refund Amount: <span style="color: #28a745;">₹<?php echo number_format($refundInfo->RefundAmount, 2); ?></span>
                                        </p>
                                    <?php endif; ?>
                                    <p style="margin-top: 10px; margin-bottom: 0;">
                                        <i class="fa fa-clock-o"></i> The refund of <strong>₹<?php echo $refundInfo ? number_format($refundInfo->RefundAmount, 2) : '—'; ?></strong> will be processed to your account within <strong>3-5 business days</strong>.
                                    </p>
                                    <?php if ($refundInfo && isset($refundInfo->RejectionReason)): ?>
                                        <p style="margin-top: 10px; font-style: italic;">
                                            <strong>Reason:</strong> <?php echo htmlspecialchars($refundInfo->RejectionReason); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Timeline -->
                            <div class="timeline" id="timelineContainer">
                                <!-- Timeline items will be populated by JavaScript -->
                            </div>

                            <!-- Request Details -->
                            <div class="request-details" id="requestDetails">
                                <!-- Details will be populated by JavaScript -->
                            </div>

                            <!-- Driver Info -->
                            <div id="driverInfoContainer">
                                <!-- Driver info will be populated by JavaScript if assigned -->
                            </div>

                            <!-- Tracking History & Messages -->
                            <div id="trackingHistoryContainer">
                                <!-- Tracking history will be populated by JavaScript -->
                            </div>

                            <!-- Rating Section -->
                            <div id="ratingSection" style="display: none;">
                                <div class="rating-section">
                                    <h4><i class="fa fa-star"></i> Rate Our Service</h4>
                                    <p>Your feedback helps us improve our service quality</p>
                                    <button class="rating-btn" onclick="openRatingModal()">
                                        <i class="fa fa-edit"></i> Submit Rating
                                    </button>
                                </div>
                            </div>

                        </div>
                    <?php elseif ($search_submitted && !$booking_found): ?>
                        <div style="text-align: center; padding: 40px; background: white; border-radius: 8px;">
                            <i class="fa fa-info-circle" style="font-size: 40px; color: #ccc; margin-bottom: 20px;"></i>
                            <p><?php echo htmlspecialchars($error_message); ?></p>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px; background: white; border-radius: 8px; color: #999;">
                            <i class="fa fa-search" style="font-size: 40px; margin-bottom: 20px;"></i>
                            <p>Enter your Booking ID above to track your service progress</p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <!-- Modern Rating Modal -->
    <div id="ratingModal" class="modern-modal">
        <div class="modal-overlay"></div>
        <div class="modern-modal-content">
            <div class="modal-header">
                <h2><i class="fa fa-star"></i> Rate Your Experience</h2>
                <button class="modal-close" onclick="closeRatingModal()">&times;</button>
            </div>

            <div class="modal-body">
                <!-- Success Message -->
                <div id="ratingSuccess" class="success-alert" style="display: none;">
                    <div class="success-icon">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <h3>Thank You!</h3>
                    <p>Your rating has been submitted successfully.</p>
                </div>

                <!-- Rating Form -->
                <form id="ratingForm" onsubmit="submitRating(event)" style="display: block;">
                    <!-- Driver Rating Section -->
                    <div class="rating-section">
                        <div class="section-header">
                            <h4><i class="fa fa-user-circle"></i> How was your driver?</h4>
                            <span class="rating-value" id="driverRatingDisplay">Select rating</span>
                        </div>
                        <div class="star-container" id="driverRatingStars" data-rating="driver">
                            <!-- Stars will be generated by JavaScript -->
                        </div>
                        <input type="hidden" id="driverRatingValue" name="driver_rating" value="0">
                        <p class="rating-hint">Rate the driver's professionalism and courtesy</p>
                    </div>

                    <!-- Service Rating Section -->
                    <div class="rating-section">
                        <div class="section-header">
                            <h4><i class="fa fa-wrench"></i> How was the service?</h4>
                            <span class="rating-value" id="serviceRatingDisplay">Select rating</span>
                        </div>
                        <div class="star-container" id="serviceRatingStars" data-rating="service">
                            <!-- Stars will be generated by JavaScript -->
                        </div>
                        <input type="hidden" id="serviceRatingValue" name="service_rating" value="0">
                        <p class="rating-hint">Rate the quality and effectiveness of service</p>
                    </div>

                    <!-- Feedback Section -->
                    <div class="feedback-section">
                        <label>
                            <i class="fa fa-comment"></i> Share Your Feedback
                            <span class="optional">(Optional)</span>
                        </label>
                        <textarea
                            id="feedback"
                            name="feedback"
                            placeholder="Tell us what you think... (up to 500 characters)"
                            maxlength="500"
                            rows="4"></textarea>
                        <div class="char-counter">
                            <span id="charCount">0</span>/500 characters
                        </div>
                    </div>

                    <!-- Hidden Fields -->
                    <input type="hidden" id="bookingNumberInput" name="booking_number" value="">

                    <!-- Action Buttons -->
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" onclick="closeRatingModal()">
                            <i class="fa fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn-submit">
                            <i class="fa fa-send"></i> Submit Rating
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modern Rating Styles -->
    <style>
        /* Modern Modal Styles */
        .modern-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 9999;
        }

        .modern-modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modern-modal-content {
            position: relative;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideInUp 0.4s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            padding: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }

        .modal-close:hover {
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 40px;
        }

        /* Success Alert */
        .success-alert {
            text-align: center;
            padding: 40px 20px;
        }

        .success-icon {
            font-size: 60px;
            color: #28a745;
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .success-alert h3 {
            color: #333;
            font-size: 22px;
            margin-bottom: 10px;
        }

        .success-alert p {
            color: #666;
            margin: 0;
        }

        /* Rating Sections */
        .rating-section {
            margin-bottom: 35px;
            padding-bottom: 35px;
            border-bottom: 1px solid #eee;
        }

        .rating-section:last-of-type {
            border-bottom: none;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h4 {
            margin: 0;
            color: #333;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rating-value {
            background: #f0f0f0;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            color: #666;
            font-weight: 500;
        }

        /* Star Container */
        .star-container {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            justify-content: flex-start;
        }

        .star {
            font-size: 40px;
            color: #ddd;
            cursor: pointer;
            transition: all 0.2s ease;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .star:hover,
        .star.active {
            color: #ffc107;
            transform: scale(1.2);
            text-shadow: 0 2px 8px rgba(255, 193, 7, 0.4);
        }

        .rating-hint {
            margin: 0;
            color: #999;
            font-size: 13px;
            margin-top: 10px;
        }

        /* Feedback Section */
        .feedback-section {
            margin-top: 30px;
        }

        .feedback-section label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .optional {
            color: #999;
            font-weight: 400;
            font-size: 12px;
        }

        .feedback-section textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            transition: border-color 0.3s;
        }

        .feedback-section textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .char-counter {
            margin-top: 8px;
            text-align: right;
            font-size: 12px;
            color: #999;
        }

        /* Action Buttons */
        .modal-footer {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .btn-cancel,
        .btn-submit {
            flex: 1;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-cancel {
            background: #f0f0f0;
            color: #333;
        }

        .btn-cancel:hover {
            background: #e0e0e0;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Error Messages */
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .modern-modal-content {
                width: 95%;
                border-radius: 10px;
            }

            .modal-header {
                padding: 20px;
            }

            .modal-header h2 {
                font-size: 20px;
            }

            .modal-body {
                padding: 20px;
            }

            .star-container {
                gap: 10px;
            }

            .star {
                font-size: 32px;
            }
        }
    </style>

    <?php include_once('includes/footer.php'); ?>

    <!-- jQuery and Bootstrap Scripts -->
    <script src="js/vendor/jquery-1.12.0.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.meanmenu.js"></script>
    <script src="js/jquery.scrollUp.min.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/change-text.js"></script>
    <script src="js/jquery.mb.YTPlayer.min.js"></script>
    <script src="js/jquery.lettering.js"></script>
    <script src="js/jquery.textillate.js"></script>
    <script src="lib/js/jquery.nivo.slider.js"></script>
    <script src="lib/home.js"></script>
    <script src="js/plugins.js"></script>
    <script src="js/main.js"></script>

    <!-- Tracking and Rating Scripts -->
    <script>
        // Global variables
        const bookingId = '<?php echo $booking_found ? htmlspecialchars($booking_data->BookingNumber) : ''; ?>';
        let pollInterval = null;
        let currentRating = {
            driver: 0,
            service: 0
        };

        // Initialize tracking on page load
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($booking_found): ?>
            // Initialize star ratings
            initializeStarRatings('driverRatingStars', 'driverRatingValue');
            initializeStarRatings('serviceRatingStars', 'serviceRatingValue');
            document.getElementById('bookingNumberInput').value = bookingId;

            // Load initial data
            loadTrackingData();

            // Start polling for updates every 5 seconds
            pollInterval = setInterval(loadTrackingData, 5000);
            <?php endif; ?>
        });

        // Load tracking data from API
        function loadTrackingData() {
            if (!bookingId) return;

            $.ajax({
                url: 'api/track-request.php',
                data: { booking_id: bookingId },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        try {
                            updateTrackingDisplay(response.data);
                        } catch(error) {
                            console.error('Error in updateTrackingDisplay:', error);
                        }
                    } else {
                        console.error('API response error:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }

        // Update tracking display with API data
        function updateTrackingDisplay(data) {
            try {
                // Update status
                const statusBadge = document.getElementById('currentStatusBadge');
                const statusMessage = document.getElementById('statusMessage');
                const statusLabel = data.current_status.status_label;
                const statusColor = data.current_status.status_color;

                if (statusBadge) {
                    statusBadge.textContent = statusLabel;
                    statusBadge.className = 'status-badge ' + statusColor;
                }

                if (statusMessage) {
                    statusMessage.textContent = data.current_status.status_message;
                }

                // Update timeline
                updateTimeline(data.timeline);

                // Update request details
                updateRequestDetails(data.request);

                // Update driver info
                if (data.driver && data.driver.assigned) {
                    updateDriverInfo(data.driver);
                }

                // Update tracking history (messages and remarks)
                if (data.tracking_history && data.tracking_history.length > 0) {
                    updateTrackingHistory(data.tracking_history);
                }

                // Show rating section if completed and not yet rated
                if (data.current_status.is_completed && !data.rating.submitted) {
                    document.getElementById('ratingSection').style.display = 'block';
                }
            } catch(error) {
                console.error('Error in updateTrackingDisplay:', error);
            }
        }

        // Update timeline display
        function updateTimeline(timeline) {
            const container = document.getElementById('timelineContainer');
            if (!container) return;

            let html = '';
            const currentTime = new Date();

            timeline.forEach((item, index) => {
                const isCompleted = item.completed;
                const isActive = isCompleted && (index === timeline.length - 1 || !timeline[index + 1].completed);

                html += `<div class="timeline-item ${isCompleted ? 'completed' : ''} ${isActive ? 'active' : ''}">`;
                html += `<div class="timeline-marker">`;
                html += `<div class="timeline-circle">`;
                html += `<i class="fa ${item.icon}"></i>`;
                html += `</div>`;
                html += `</div>`;
                html += `<div class="timeline-content">`;
                html += `<h4>${item.label}</h4>`;
                html += `<p>${item.description}</p>`;
                if (item.completed_at) {
                    html += `<div class="time-ago">${formatTime(item.completed_at)}</div>`;
                }
                html += `</div>`;
                html += `</div>`;
            });

            container.innerHTML = html;
        }

        // Update request details display
        function updateRequestDetails(request) {
            const container = document.getElementById('requestDetails');
            if (!container) return;

            let html = '';
            html += `<div class="detail-item">`;
            html += `<div class="detail-label">Vehicle Type</div>`;
            html += `<div class="detail-value">${request.vehicle_type}</div>`;
            html += `</div>`;

            html += `<div class="detail-item">`;
            html += `<div class="detail-label">Pickup Location</div>`;
            html += `<div class="detail-value">${request.pickup_location}</div>`;
            html += `</div>`;

            if (request.pickup_coordinates.latitude && request.pickup_coordinates.longitude) {
                html += `<div class="detail-item">`;
                html += `<div class="detail-label">Location Coordinates</div>`;
                html += `<div class="detail-value">${request.pickup_coordinates.latitude}, ${request.pickup_coordinates.longitude}</div>`;
                html += `</div>`;
            }

            html += `<div class="detail-item">`;
            html += `<div class="detail-label">Destination</div>`;
            html += `<div class="detail-value">${request.destination}</div>`;
            html += `</div>`;

            html += `<div class="detail-item">`;
            html += `<div class="detail-label">Pickup Date & Time</div>`;
            html += `<div class="detail-value">${request.pickup_date} at ${request.pickup_time}</div>`;
            html += `</div>`;

            html += `<div class="detail-item">`;
            html += `<div class="detail-label">Request Submitted</div>`;
            html += `<div class="detail-value">${formatTime(request.request_submitted_at)}</div>`;
            html += `</div>`;

            container.innerHTML = html;
        }

        // Update driver info display
        function updateDriverInfo(driver) {
            let container = document.getElementById('driverInfoContainer');
            if (!container) return;

            let html = `<div class="driver-info">`;
            html += `<h4><i class="fa fa-user-circle"></i> Driver Information</h4>`;
            html += `<div class="driver-details">`;
            html += `<div class="driver-detail-item">`;
            html += `<div class="driver-detail-label">Driver Name</div>`;
            html += `<div class="driver-detail-value">${driver.driver_name}</div>`;
            html += `</div>`;

            html += `<div class="driver-detail-item">`;
            html += `<div class="driver-detail-label">Driver ID</div>`;
            html += `<div class="driver-detail-value">${driver.driver_id}</div>`;
            html += `</div>`;

            html += `<a href="tel:${driver.driver_phone}" class="driver-call-btn">`;
            html += `<i class="fa fa-phone"></i> Call Driver: ${driver.driver_phone}`;
            html += `</a>`;
            html += `</div>`;
            html += `</div>`;

            container.innerHTML = html;
        }

        // Update tracking history display (messages and remarks from admin/driver)
        function updateTrackingHistory(history) {
            const container = document.getElementById('trackingHistoryContainer');
            if (!container || !history || history.length === 0) return;

            let html = '<div class="tracking-history-section" style="margin-top: 30px; background: #f9f9f9; padding: 20px; border-radius: 8px;">';
            html += '<h4 style="color: #333; margin-bottom: 15px;"><i class="fa fa-history"></i> Status Updates & Messages</h4>';
            html += '<div style="max-height: 300px; overflow-y: auto;">';

            history.forEach((item, index) => {
                const statusLabel = item.status_label || 'Status Update';
                const remark = item.remark || 'No message';
                const timeAgo = item.time_ago || 'Unknown time';

                html += '<div style="padding: 12px; margin-bottom: 10px; background: white; border-left: 4px solid #667eea; border-radius: 4px;">';
                html += `<div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 5px;">`;
                html += `<span style="font-weight: 600; color: #333;">${statusLabel}</span>`;
                html += `<span style="font-size: 12px; color: #999;">${timeAgo}</span>`;
                html += `</div>`;
                html += `<p style="margin: 0; color: #666; font-size: 13px;">${remark}</p>`;
                html += '</div>';
            });

            html += '</div></div>';
            container.innerHTML = html;
        }

        // Format timestamp to relative time
        function formatTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMins / 60);
            const diffDays = Math.floor(diffHours / 24);

            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return diffMins + ' min' + (diffMins > 1 ? 's' : '') + ' ago';
            if (diffHours < 24) return diffHours + ' hr' + (diffHours > 1 ? 's' : '') + ' ago';
            if (diffDays < 7) return diffDays + ' day' + (diffDays > 1 ? 's' : '') + ' ago';

            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }

        // Initialize star ratings
        function initializeStarRatings(containerId, inputId) {
            const container = document.getElementById(containerId);
            if (!container) return;

            let html = '';
            for (let i = 1; i <= 5; i++) {
                html += `<span class="star" data-value="${i}" data-input="${inputId}"><i class="fa fa-star"></i></span>`;
            }
            container.innerHTML = html;

            // Get the rating type (driver or service)
            const ratingType = container.getAttribute('data-rating');
            const displayElement = document.getElementById(ratingType + 'RatingDisplay');

            // Add click handlers
            const stars = container.querySelectorAll('.star');
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    const inputId = this.getAttribute('data-input');
                    document.getElementById(inputId).value = value;

                    // Update display text
                    const ratings = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
                    if (displayElement) {
                        displayElement.textContent = ratings[value] + ' (' + value + '/5)';
                    }

                    // Update star visual
                    updateModernStarDisplay(container, value);

                    console.log('Rating set: ' + ratingType + ' = ' + value);
                });

                // Hover effects
                star.addEventListener('mouseover', function() {
                    const value = this.getAttribute('data-value');
                    updateModernStarDisplay(container, value, true);
                });
            });

            container.addEventListener('mouseleave', function() {
                const value = document.getElementById(inputId).value;
                updateModernStarDisplay(container, value);
            });
        }

        // Update star display - modern version
        function updateModernStarDisplay(container, value, hover = false) {
            const stars = container.querySelectorAll('.star');
            stars.forEach((star, index) => {
                if ((index + 1) <= value) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }

        // Open rating modal
        function openRatingModal() {
            document.getElementById('ratingModal').classList.add('show');
        }

        // Close rating modal
        function closeRatingModal() {
            document.getElementById('ratingModal').classList.remove('show');
        }

        // Submit rating
        function submitRating(event) {
            event.preventDefault();

            const driverRating = document.getElementById('driverRatingValue').value;
            const serviceRating = document.getElementById('serviceRatingValue').value;
            const feedback = document.getElementById('feedback').value;

            // Improved validation
            if (!driverRating || driverRating === '0') {
                showRatingError('Please rate the driver (1-5 stars)');
                return;
            }

            if (!serviceRating || serviceRating === '0') {
                showRatingError('Please rate the service (1-5 stars)');
                return;
            }

            const submitBtn = document.querySelector('.btn-submit');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Submitting...';

            console.log('Submitting rating:', {
                booking_number: bookingId,
                driver_rating: driverRating,
                service_rating: serviceRating,
                feedback: feedback
            });

            $.ajax({
                url: 'api/submit-rating.php',
                method: 'POST',
                dataType: 'json',
                data: JSON.stringify({
                    booking_number: bookingId,
                    driver_rating: parseInt(driverRating),
                    service_rating: parseInt(serviceRating),
                    feedback: feedback
                }),
                contentType: 'application/json',
                success: function(response) {
                    console.log('Rating response:', response);
                    if (response.success) {
                        // Show success message
                        document.getElementById('ratingForm').style.display = 'none';
                        document.getElementById('ratingSuccess').style.display = 'block';

                        // Close modal after 3 seconds
                        setTimeout(function() {
                            closeRatingModal();
                            // Reset form for next time
                            setTimeout(resetRatingForm, 500);
                        }, 3000);
                    } else {
                        showRatingError(response.message || 'Error submitting rating');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fa fa-send"></i> Submit Rating';
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error, xhr.responseText);
                    showRatingError('Network error. Please check your connection and try again.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa fa-send"></i> Submit Rating';
                }
            });
        }

        // Show rating error
        function showRatingError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = '<i class="fa fa-exclamation-circle"></i> ' + message;
            const modalBody = document.querySelector('.modal-body');
            const existingError = modalBody.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            modalBody.insertBefore(errorDiv, modalBody.firstChild);

            // Auto-remove error after 5 seconds
            setTimeout(function() {
                if (errorDiv.parentNode) {
                    errorDiv.remove();
                }
            }, 5000);
        }

        // Reset rating form
        function resetRatingForm() {
            document.getElementById('ratingForm').style.display = 'block';
            document.getElementById('ratingSuccess').style.display = 'none';
            document.getElementById('driverRatingValue').value = '0';
            document.getElementById('serviceRatingValue').value = '0';
            document.getElementById('feedback').value = '';
            document.getElementById('driverRatingDisplay').textContent = 'Select rating';
            document.getElementById('serviceRatingDisplay').textContent = 'Select rating';
            document.getElementById('charCount').textContent = '0';

            // Reset star displays
            document.querySelectorAll('.star-container .star').forEach(s => s.classList.remove('active'));
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('ratingModal');
            if (event.target === modal || event.target.classList.contains('modal-overlay')) {
                closeRatingModal();
            }
        }

        // Character counter for feedback textarea
        document.addEventListener('DOMContentLoaded', function() {
            const feedbackTextarea = document.getElementById('feedback');
            const charCount = document.getElementById('charCount');

            if (feedbackTextarea && charCount) {
                feedbackTextarea.addEventListener('input', function() {
                    charCount.textContent = this.value.length;
                });
            }
        });

        // Clean up polling on page unload
        window.addEventListener('beforeunload', function() {
            if (pollInterval) {
                clearInterval(pollInterval);
            }
        });
    </script>

</body>
</html>
