<?php
/**
 * API Endpoint: Submit Service Rating
 *
 * File: vehicleassitancems/api/submit-rating.php
 * Purpose: Handle customer rating submission and store in database
 *
 * Method: POST
 * Parameters: booking_number, driver_rating, service_rating, feedback (optional)
 * Response: JSON success/error message
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

// Include database connection and helpers
require_once('../includes/dbconnection.php');
require_once('../includes/tracking-helpers.php');

// Initialize response
$response = array(
    'success' => false,
    'message' => '',
    'rating_id' => null
);

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method. Only POST is allowed.');
    }

    // Auto-create service_ratings table if it doesn't exist
    try {
        $sql_check = "SHOW TABLES LIKE 'service_ratings'";
        $query_check = $dbh->prepare($sql_check);
        $query_check->execute();

        if ($query_check->rowCount() === 0) {
            // Table doesn't exist, create it
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
    } catch (PDOException $e) {
        // Log but continue - table creation error
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        // Fallback to POST variables
        $input = $_POST;
    }

    // Validate required parameters
    if (!isset($input['booking_number']) || empty($input['booking_number'])) {
        throw new Exception('Booking number is required');
    }

    if (!isset($input['driver_rating']) || empty($input['driver_rating'])) {
        throw new Exception('Driver rating is required');
    }

    if (!isset($input['service_rating']) || empty($input['service_rating'])) {
        throw new Exception('Service rating is required');
    }

    // Sanitize booking number
    $booking_number = sanitizeBookingId($input['booking_number']);
    if (!$booking_number) {
        throw new Exception('Invalid booking number format. Must be 9 digits.');
    }

    // Validate rating values (1-5)
    $driver_rating = (int)$input['driver_rating'];
    $service_rating = (int)$input['service_rating'];

    if ($driver_rating < 1 || $driver_rating > 5) {
        throw new Exception('Driver rating must be between 1 and 5');
    }

    if ($service_rating < 1 || $service_rating > 5) {
        throw new Exception('Service rating must be between 1 and 5');
    }

    // Get feedback (optional)
    $feedback = isset($input['feedback']) ? trim($input['feedback']) : '';
    if (strlen($feedback) > 500) {
        $feedback = substr($feedback, 0, 500);
    }

    // Query 1: Verify booking exists
    $sql_check = "SELECT ID FROM tblbook WHERE BookingNumber = :booking_number LIMIT 1";
    $query_check = $dbh->prepare($sql_check);
    $query_check->bindParam(':booking_number', $booking_number, PDO::PARAM_STR);
    $query_check->execute();

    if ($query_check->rowCount() === 0) {
        throw new Exception('Booking not found. Invalid booking number.');
    }

    $booking = $query_check->fetch(PDO::FETCH_OBJ);
    $booking_id = $booking->ID;

    // Query 2: Check if rating already exists
    try {
        $sql_exists = "SELECT rating_id FROM service_ratings WHERE booking_number = :booking_number LIMIT 1";
        $query_exists = $dbh->prepare($sql_exists);
        $booking_number_int = (int)$booking_number;
        $query_exists->bindParam(':booking_number', $booking_number_int, PDO::PARAM_INT);
        $query_exists->execute();

        if ($query_exists->rowCount() > 0) {
            throw new Exception('You have already submitted a rating for this booking. Thank you!');
        }
    } catch (PDOException $e) {
        // Table might not exist yet, continue
        if (strpos($e->getMessage(), "doesn't exist") === false) {
            throw $e;
        }
    }

    // Query 3: Verify service is completed
    $sql_status = "SELECT Status FROM tblbook WHERE BookingNumber = :booking_number LIMIT 1";
    $query_status = $dbh->prepare($sql_status);
    $query_status->bindParam(':booking_number', $booking_number, PDO::PARAM_STR);
    $query_status->execute();

    $booking_status = $query_status->fetch(PDO::FETCH_OBJ);

    if (!isServiceCompleted($booking_status->Status)) {
        throw new Exception('Service must be completed before submitting a rating.');
    }

    // Query 4: Insert rating into database
    $sql_insert = "INSERT INTO service_ratings (booking_id, booking_number, driver_rating, service_rating, feedback, submitted_at)
                   VALUES (:booking_id, :booking_number, :driver_rating, :service_rating, :feedback, NOW())";

    $query_insert = $dbh->prepare($sql_insert);
    $query_insert->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $query_insert->bindParam(':booking_number', $booking_number_int, PDO::PARAM_INT);
    $query_insert->bindParam(':driver_rating', $driver_rating, PDO::PARAM_INT);
    $query_insert->bindParam(':service_rating', $service_rating, PDO::PARAM_INT);
    $query_insert->bindParam(':feedback', $feedback, PDO::PARAM_STR);

    if (!$query_insert->execute()) {
        throw new Exception('Failed to submit rating. Please try again.');
    }

    $rating_id = $dbh->lastInsertId();

    $response['success'] = true;
    $response['message'] = 'Thank you! Your rating has been submitted successfully.';
    $response['rating_id'] = (int)$rating_id;

} catch (PDOException $e) {
    $response['success'] = false;
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

?>
