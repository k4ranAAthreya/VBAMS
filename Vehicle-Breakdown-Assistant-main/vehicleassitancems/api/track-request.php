<?php
/**
 * API Endpoint: Track Service Request
 *
 * File: vehicleassitancems/api/track-request.php
 * Purpose: Return JSON data for customer tracking page with real-time updates
 *
 * Request: GET /api/track-request.php?booking_id=XXXXXXXXX
 * Response: JSON with booking details, current status, driver info, timeline, rating status
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Include database connection and helpers
require_once('../includes/dbconnection.php');
require_once('../includes/tracking-helpers.php');

// Initialize response
$response = array(
    'success' => false,
    'message' => '',
    'exists' => false,
    'data' => null
);

try {
    // Validate booking ID parameter
    if (!isset($_GET['booking_id']) || empty($_GET['booking_id'])) {
        throw new Exception('Booking ID is required');
    }

    $booking_id = sanitizeBookingId($_GET['booking_id']);
    if (!$booking_id) {
        throw new Exception('Invalid booking ID format. Must be 9 digits.');
    }

    // Query 1: Fetch booking details
    $sql = "SELECT * FROM tblbook WHERE BookingNumber = :booking_id LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':booking_id', $booking_id, PDO::PARAM_STR);
    $query->execute();

    if ($query->rowCount() === 0) {
        throw new Exception('Booking not found. Please check your Booking ID.');
    }

    $booking = $query->fetch(PDO::FETCH_OBJ);
    $response['exists'] = true;

    // Query 2: Get tracking history (all status updates)
    $sql_tracking = "SELECT * FROM tbltracking
                     WHERE BookingNumber = :booking_id
                     ORDER BY ID ASC";
    $query_tracking = $dbh->prepare($sql_tracking);
    $query_tracking->bindParam(':booking_id', $booking_id, PDO::PARAM_STR);
    $query_tracking->execute();

    $tracking_history = $query_tracking->fetchAll(PDO::FETCH_OBJ);

    // Query 3: Get driver details (if assigned)
    $driver_info = null;
    if (!empty($booking->AssignTo)) {
        $sql_driver = "SELECT * FROM tbldriver WHERE DriverID = :driver_id LIMIT 1";
        $query_driver = $dbh->prepare($sql_driver);
        $query_driver->bindParam(':driver_id', $booking->AssignTo, PDO::PARAM_STR);
        $query_driver->execute();

        if ($query_driver->rowCount() > 0) {
            $driver = $query_driver->fetch(PDO::FETCH_OBJ);
            $driver_info = array(
                'assigned' => true,
                'driver_id' => $driver->DriverID,
                'driver_name' => $driver->Name,
                'driver_phone' => formatPhoneNumber($driver->MobileNumber),
                'driver_email' => $driver->Email,
                'assigned_time' => $booking->UpdationDate
            );
        }
    }

    // Query 4: Check if rating already submitted
    $rating_submitted = false;
    try {
        // First, ensure the service_ratings table exists
        $sql_check = "SHOW TABLES LIKE 'service_ratings'";
        $query_check = $dbh->prepare($sql_check);
        $query_check->execute();

        if ($query_check->rowCount() === 0) {
            // Auto-create the table
            $sql_create = "CREATE TABLE IF NOT EXISTS service_ratings (
                rating_id INT AUTO_INCREMENT PRIMARY KEY,
                booking_id INT NOT NULL,
                booking_number INT NOT NULL,
                driver_rating INT CHECK(driver_rating >= 1 AND driver_rating <= 5),
                service_rating INT CHECK(service_rating >= 1 AND service_rating <= 5),
                feedback TEXT,
                submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_booking (booking_number),
                FOREIGN KEY (booking_id) REFERENCES tblbook(ID) ON DELETE CASCADE,
                INDEX idx_booking_number (booking_number),
                INDEX idx_submitted_at (submitted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $query_create = $dbh->prepare($sql_create);
            $query_create->execute();
        }

        // Now check for existing ratings
        $sql_rating = "SELECT rating_id FROM service_ratings WHERE booking_number = :booking_number LIMIT 1";
        $query_rating = $dbh->prepare($sql_rating);
        $booking_number_int = (int)$booking_id;
        $query_rating->bindParam(':booking_number', $booking_number_int, PDO::PARAM_INT);
        $query_rating->execute();

        if ($query_rating->rowCount() > 0) {
            $rating_submitted = true;
        }
    } catch (Exception $e) {
        // service_ratings table operations failed - that's okay
        $rating_submitted = false;
    }

    // Build timeline array
    $timeline = array();
    $timeline_stages = getServiceTimeline();

    // Determine current progress
    $current_timeline_status = getTimelineStatus($booking->Status, $booking->AssignTo);

    foreach ($timeline_stages as $stage) {
        // Mark stages as completed if we've reached them or passed them
        $completed = false;

        // Stage 1: submitted (always completed when booking exists)
        if ($stage['id'] === 1) {
            $completed = true;
        }
        // Stage 2: accepted (when Status is Approved or beyond)
        elseif ($stage['id'] === 2 && in_array($booking->Status, array('Approved', 'On The Way', 'Completed'))) {
            $completed = true;
        }
        // Stage 3: driver_assigned (when driver is assigned)
        elseif ($stage['id'] === 3 && isDriverAssigned($booking->Status, $booking->AssignTo)) {
            $completed = true;
        }
        // Stage 4: on_the_way (when Status is On The Way or Completed)
        elseif ($stage['id'] === 4 && in_array($booking->Status, array('On The Way', 'Completed'))) {
            $completed = true;
        }
        // Stage 5: completed (when Status is Completed)
        elseif ($stage['id'] === 5 && isServiceCompleted($booking->Status)) {
            $completed = true;
        }

        // Find timestamp for this stage from tracking history
        $stage_time = '';
        if ($completed) {
            foreach ($tracking_history as $history) {
                // Match tracking status with this stage
                $history_timeline_status = getTimelineStatus($history->Status, $booking->AssignTo);
                if ($history_timeline_status === $stage['status']) {
                    $stage_time = $history->UpdationDate;
                    break;
                }
            }
            // Fallback to booking date for submitted stage
            if (!$stage_time && $stage['id'] === 1) {
                $stage_time = $booking->DateofRequest;
            }
        }

        $timeline[] = array(
            'id' => $stage['id'],
            'status' => $stage['status'],
            'label' => $stage['label'],
            'description' => $stage['description'],
            'icon' => $stage['icon'],
            'completed' => $completed,
            'completed_at' => $stage_time
        );
    }

    // Build response data
    $response['data'] = array(
        'booking_id' => (int)$booking->ID,
        'booking_number' => (int)$booking->BookingNumber,
        'customer' => array(
            'name' => htmlspecialchars($booking->Name),
            'email' => htmlspecialchars($booking->Email),
            'phone' => formatPhoneNumber($booking->PhoneNumber)
        ),
        'request' => array(
            'booking_number' => (int)$booking->BookingNumber,
            'vehicle_type' => isset($booking->VehicleType) ? htmlspecialchars($booking->VehicleType) : 'Not Specified',
            'pickup_location' => htmlspecialchars($booking->PickupLoc),
            'pickup_coordinates' => array(
                'latitude' => !empty($booking->Latitude) ? $booking->Latitude : null,
                'longitude' => !empty($booking->Longitude) ? $booking->Longitude : null
            ),
            'destination' => htmlspecialchars($booking->Destination),
            'pickup_date' => !empty($booking->PickupDate) ? $booking->PickupDate : 'Not Set',
            'pickup_time' => !empty($booking->PickupTime) ? $booking->PickupTime : 'Not Set',
            'request_submitted_at' => !empty($booking->DateofRequest) ? $booking->DateofRequest : date('Y-m-d H:i:s')
        ),
        'current_status' => array(
            'status' => $booking->Status,
            'status_label' => getStatusLabel($booking->Status),
            'status_message' => getStatusMessage($booking->Status),
            'status_color' => getStatusBadgeColor($booking->Status),
            'status_icon' => getStatusIcon($booking->Status),
            'is_completed' => isServiceCompleted($booking->Status),
            'is_rejected' => isServiceRejected($booking->Status),
            'last_update' => $booking->UpdationDate
        ),
        'driver' => $driver_info,
        'timeline' => $timeline,
        'tracking_history' => array_map(function($t) {
            return array(
                'remark' => htmlspecialchars($t->Remark),
                'status' => $t->Status,
                'status_label' => getStatusLabel($t->Status),
                'updated_at' => $t->UpdationDate,
                'time_ago' => formatTime($t->UpdationDate)
            );
        }, $tracking_history),
        'rating' => array(
            'submitted' => $rating_submitted,
            'required' => isServiceCompleted($booking->Status)
        )
    );

    $response['success'] = true;
    $response['message'] = 'Tracking data retrieved successfully';

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

?>
