<?php
/**
 * Quick Booking Tracker Widget
 * Single-file component for embedding on any page
 * Include with: <?php include('quick-track-widget.php'); ?>
 *
 * Features:
 * - Search bookings by ID
 * - Display quick status, vehicle, location, driver info
 * - Link to full tracking page
 * - Mobile responsive
 * - No external dependencies
 */

require_once('includes/dbconnection.php');
require_once('includes/tracking-helpers.php');

// Initialize variables
$search_result = null;
$search_submitted = false;
$error_message = '';

// Handle search form submission (GET parameter)
if (isset($_GET['track_id']) && !empty($_GET['track_id'])) {
    $search_submitted = true;
    $booking_id = sanitizeBookingId($_GET['track_id']);

    if (!$booking_id) {
        $error_message = 'Invalid Booking ID format. Please enter 9 digits.';
    } else {
        // Query database for booking
        try {
            $sql = "SELECT * FROM tblbook WHERE BookingNumber = :booking_id LIMIT 1";
            $query = $dbh->prepare($sql);
            $query->bindParam(':booking_id', $booking_id, PDO::PARAM_STR);
            $query->execute();

            if ($query->rowCount() > 0) {
                $search_result = $query->fetch(PDO::FETCH_OBJ);
            } else {
                $error_message = 'Booking ID not found. Please check and try again.';
            }
        } catch (Exception $e) {
            $error_message = 'Error retrieving booking. Please try again later.';
        }
    }
}
?>

<!-- Scoped Inline CSS -->
<style>
.quick-track-widget {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px;
    border-radius: 8px;
    color: white;
    margin: 40px 0;
}

.quick-track-widget h3 {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 24px;
    font-weight: 600;
}

.quick-track-widget .search-form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.quick-track-widget input {
    flex: 1;
    min-width: 200px;
    padding: 12px 15px;
    border: none;
    border-radius: 5px;
    font-size: 14px;
}

.quick-track-widget button {
    padding: 12px 30px;
    background: white;
    color: #667eea;
    border: none;
    border-radius: 5px;
    font-weight: 600;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s ease;
}

.quick-track-widget button:hover {
    opacity: 0.9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.quick-track-widget .error-alert {
    background: rgba(255,255,255,0.2);
    padding: 12px 15px;
    border-radius: 5px;
    margin: 15px 0;
    border-left: 3px solid #ff6b6b;
    font-size: 14px;
}

.quick-track-widget .quick-result {
    background: rgba(255,255,255,0.1);
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
}

.quick-track-widget .result-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.quick-track-widget .result-row:last-child {
    border-bottom: none;
}

.quick-track-widget .label {
    font-weight: 600;
    font-size: 13px;
    opacity: 0.9;
}

.quick-track-widget .value {
    font-weight: 500;
    font-size: 14px;
}

.quick-track-widget .status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 12px;
    white-space: nowrap;
}

.quick-track-widget .status-badge.info { background: rgba(13,110,253,0.3); }
.quick-track-widget .status-badge.primary { background: rgba(13,110,253,0.3); }
.quick-track-widget .status-badge.warning { background: rgba(255,193,7,0.3); }
.quick-track-widget .status-badge.success { background: rgba(40,167,69,0.3); }
.quick-track-widget .status-badge.danger { background: rgba(220,53,69,0.3); }

.quick-track-widget .call-btn {
    background: white;
    color: #667eea;
    padding: 6px 12px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 600;
    font-size: 12px;
    display: inline-block;
    margin-left: 10px;
    transition: all 0.3s ease;
}

.quick-track-widget .call-btn:hover {
    opacity: 0.9;
    color: #667eea;
    text-decoration: none;
    transform: translateY(-1px);
}

.quick-track-widget .full-track-btn {
    display: inline-block;
    background: white;
    color: #667eea;
    padding: 12px 25px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 600;
    margin-top: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.quick-track-widget .full-track-btn:hover {
    opacity: 0.9;
    color: #667eea;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

@media (max-width: 768px) {
    .quick-track-widget {
        padding: 25px;
        margin: 20px 0;
    }

    .quick-track-widget h3 {
        font-size: 20px;
        margin-bottom: 15px;
    }

    .quick-track-widget .search-form {
        flex-direction: column;
        gap: 8px;
    }

    .quick-track-widget input,
    .quick-track-widget button {
        width: 100%;
    }

    .quick-track-widget .result-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .quick-track-widget .call-btn {
        margin-left: 0;
        margin-top: 8px;
    }

    .quick-track-widget input::placeholder {
        font-size: 13px;
    }
}
</style>

<!-- Widget HTML -->
<div class="quick-track-widget">
    <h3><i class="fa fa-search"></i> Quick Track Your Request</h3>

    <!-- Search Form -->
    <form method="GET" class="search-form">
        <input
            type="text"
            name="track_id"
            placeholder="Enter your Booking ID (9 digits)"
            maxlength="9"
            pattern="[0-9]{9}"
            value="<?php echo isset($_GET['track_id']) ? htmlspecialchars($_GET['track_id']) : ''; ?>"
        />
        <button type="submit"><i class="fa fa-arrow-right"></i> Track Status</button>
    </form>

    <!-- Error Message -->
    <?php if ($search_submitted && $error_message): ?>
        <div class="error-alert">
            <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Quick Result Display -->
    <?php if ($search_result): ?>
        <div class="quick-result">
            <!-- Status Row -->
            <div class="result-row">
                <span class="label">Status</span>
                <span class="status-badge <?php echo getStatusBadgeColor($search_result->Status); ?>">
                    <?php echo getStatusLabel($search_result->Status); ?>
                </span>
            </div>

            <!-- Vehicle Type Row -->
            <div class="result-row">
                <span class="label">Vehicle Type</span>
                <span class="value"><?php echo htmlspecialchars($search_result->VehicleType); ?></span>
            </div>

            <!-- Pickup Location Row -->
            <div class="result-row">
                <span class="label">Pickup Location</span>
                <span class="value"><?php echo htmlspecialchars($search_result->PickupLoc); ?></span>
            </div>

            <!-- Driver Info Row (if assigned) -->
            <?php
            if (!empty($search_result->AssignTo)) {
                try {
                    $sql_driver = "SELECT * FROM tbldriver WHERE DriverID = :driver_id LIMIT 1";
                    $q_driver = $dbh->prepare($sql_driver);
                    $q_driver->bindParam(':driver_id', $search_result->AssignTo, PDO::PARAM_STR);
                    $q_driver->execute();

                    if ($q_driver->rowCount() > 0) {
                        $driver = $q_driver->fetch(PDO::FETCH_OBJ);
            ?>
                        <div class="result-row">
                            <span class="label">Assigned Driver</span>
                            <span class="value">
                                <?php echo htmlspecialchars($driver->Name); ?>
                                <a href="tel:<?php echo htmlspecialchars($driver->MobileNumber); ?>" class="call-btn">
                                    <i class="fa fa-phone"></i> Call
                                </a>
                            </span>
                        </div>
            <?php
                    }
                } catch (Exception $e) {
                    // Silently fail if driver lookup fails
                }
            }
            ?>

            <!-- Link to Full Tracking Page -->
            <a href="track-service.php?booking_id=<?php echo htmlspecialchars($search_result->BookingNumber); ?>" class="full-track-btn">
                <i class="fa fa-arrow-right"></i> View Full Tracking Details
            </a>
        </div>
    <?php endif; ?>
</div>
