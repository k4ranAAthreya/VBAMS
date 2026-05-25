<?php
include('includes/dbconnection.php');
include('includes/payment-config.php');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

function sanitizeInput($input) {
    return htmlspecialchars(stripslashes(trim($input)), ENT_QUOTES, 'UTF-8');
}

if(isset($_POST['submit'])) {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $pickuploc = sanitizeInput($_POST['pickuploc']);
    $destination = sanitizeInput($_POST['destination']);
    $phone = sanitizeInput($_POST['phone']);
    $pickupdate = sanitizeInput($_POST['pickupdate']);
    $pickuptime = sanitizeInput($_POST['pickuptime']);
    $vehicletype = sanitizeInput($_POST['vehicletype']);
    $latitude = sanitizeInput($_POST['latitude']);
    $longitude = sanitizeInput($_POST['longitude']);
    $serviceType = sanitizeInput($_POST['service_type']);
    
    // Validate service type
    $service = getServiceCharge($serviceType);
    if (!$service) {
        echo '<script>alert("Invalid service type selected")</script>';
        exit;
    }
    
    $bookingnumber = mt_rand(100000000, 999999999);
    
    // Calculate total charge based on base charge from database
    $totalAmount = floatval($service['BaseCharge']);
    
    // Use fixed ₹500 advance payment for towing services
    $advanceAmount = 500;
    
    // Insert booking with payment fields
    $sql = "INSERT INTO tblbook(
        BookingNumber, Name, Email, PhoneNumber, PickupLoc, Destination,
        PickupDate, PickupTime, VehicleType, Latitude, Longitude,
        ServiceType, TotalAmount, AdvancePaymentAmount, PaymentStatus, BookingStatusFlow
    ) VALUES (
        :bookingnumber, :name, :email, :phone, :pickuploc, :destination,
        :pickupdate, :pickuptime, :vehicletype, :latitude, :longitude,
        :servicetype, :totalamount, :advanceamount, 'Pending', 'Payment Pending'
    )";
    
    $query = $dbh->prepare($sql);
    $query->bindParam(':bookingnumber', $bookingnumber, PDO::PARAM_STR);
    $query->bindParam(':name', $name, PDO::PARAM_STR);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':phone', $phone, PDO::PARAM_STR);
    $query->bindParam(':pickuploc', $pickuploc, PDO::PARAM_STR);
    $query->bindParam(':destination', $destination, PDO::PARAM_STR);
    $query->bindParam(':pickupdate', $pickupdate, PDO::PARAM_STR);
    $query->bindParam(':pickuptime', $pickuptime, PDO::PARAM_STR);
    $query->bindParam(':vehicletype', $vehicletype, PDO::PARAM_STR);
    $query->bindParam(':latitude', $latitude, PDO::PARAM_STR);
    $query->bindParam(':longitude', $longitude, PDO::PARAM_STR);
    $query->bindParam(':servicetype', $serviceType, PDO::PARAM_STR);
    $query->bindParam(':totalamount', $totalAmount, PDO::PARAM_STR);
    $query->bindParam(':advanceamount', $advanceAmount, PDO::PARAM_STR);
    $query->execute();
    
    $LastInsertId = $dbh->lastInsertId();
    if ($LastInsertId > 0) {
        // Redirect to demo payment page with booking number
        echo "<script>window.location.href='payment-demo.php?booking_id=".$bookingnumber."'</script>";
    } else {
        echo '<script>alert("Something Went Wrong. Please try again")</script>';
    }
}
?><!DOCTYPE html>
<html class="no-js" lang="zxx">
    <head>
        <title>VBAMS || Request Form </title>

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
        <script src="js/vendor/modernizr-2.8.3.min.js"></script>
    </head>
    <body>
     <?php include_once('includes/header.php');?>
        <div class="page-title-area overlay">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="page-title">
                            <h2>Send Request</h2>
                        </div>
                        <div class="page-title-menu">
                            <ul>
                                <li><a href="index.php">Home</a> <span> / </span> </li>
                                <li><a href="booking-request.php">Request Form</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="checkout-area section-padding">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="client-address">
                            <div class="section-small-title">
                                <h3>Booking Form for Breakdown Assistance</h3>
                            </div>
                            <div class="client-address-form">
                                <form action="" method="post">
                                    <label>Name</label>
                                    <input class="form-control" type="text" placeholder="Enter your name" name="name" required="true" pattern="[A-Za-z\s]+" title="Name should only contain letters and spaces. Numbers are not allowed.">
                                    
                                    <label class="form-label">Email</label>
                                    <input class="form-control" type="email" placeholder="Enter your email" name="email" required="true">
                                   <label class="form-label">Phone</label>
                                   <input class="form-control" type="tel" placeholder="Enter your phone number" name="phone" required="true" maxlength="10" pattern="[0-9]+">
                                    <label class="form-label">Pickup Location</label>
                                    <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                                        <input class="form-control" type="text" placeholder="Enter Location or click Get Location button" name="pickuploc" id="pickuploc" required="true" style="flex: 1;">
                                        <button type="button" class="btn btn-info" id="getLocationBtn" style="white-space: nowrap; padding: 10px 20px;">Get Location</button>
                                    </div>
                                    <!-- Location status message -->
                                    <div id="locationStatus" style="margin-bottom: 15px; color: #666; font-size: 13px; display: none;">
                                        <span id="locationMessage"></span>
                                    </div>
                                    <label class="form-label">Destination</label>
                                   <input class="form-control" type="text" placeholder="Enter Destination" name="destination" required="true">
                                  <label class="form-label">Pickup Date</label>
                                  <input class="form-control" type="date" required="true" name="pickupdate">
                                   <label class="form-label">Pickup Time</label>
                                   <input class="form-control" type="time" required="true" name="pickuptime">
                                  <label class="form-label" style="color: #d32f2f; font-weight: bold;">Vehicle Type (Towing Service) *</label>
                                    <select class="form-control" id="service_type" name="service_type" required="true" onchange="updatePaymentInfo()">
                                        <option value="">-- Select Vehicle Type --</option>
                                        <option value="Bike Towing">🏍️ Bike Towing - ₹1000 (Demo Price)</option>
                                        <option value="Car Towing">🚗 Car Towing - ₹3000 (Demo Price)</option>
                                        <option value="Truck Towing">🚚 Truck Towing - ₹6000 (Demo Price)</option>
                                    </select>
                                    
                                    <div style="background: #e8f4f8; padding: 12px; border-radius: 5px; margin-top: 15px; border-left: 4px solid #0066cc;">
                                        <strong style="color: #0066cc;">ℹ️ Towing Services Only</strong>
                                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #333;">We provide towing services only. No repair or other roadside assistance services are available.</p>
                                    </div>

                                    <!-- Payment Information Display -->
                                    <div id="paymentInfo" style="display:none; background: #f5f5f5; padding: 15px; border-radius: 5px; margin-top: 20px; border-left: 4px solid #d32f2f;">
                                        <h4 style="color: #d32f2f; margin-top: 0;">💳 Payment Information</h4>
                                        
                                        <div style="display: flex; justify-content: space-between; margin: 10px 0; padding: 8px 0; border-bottom: 1px solid #ddd;">
                                            <span><strong>Service Charge:</strong></span>
                                            <span id="baseCharge">₹0</span>
                                        </div>
                                        
                                        <div style="display: flex; justify-content: space-between; margin: 10px 0; padding: 8px 0; border-bottom: 1px solid #ddd;">
                                            <span><strong>Advance Payment Required (Fixed):</strong></span>
                                            <span id="advanceAmount" style="color: #d32f2f; font-weight: bold; font-size: 18px;">₹500</span>
                                        </div>
                                        
                                        <div style="display: flex; justify-content: space-between; margin: 10px 0; padding: 8px 0;">
                                            <span><strong>Remaining After Approval:</strong></span>
                                            <span id="remainingAmount" style="color: #2196F3;">₹0</span>
                                        </div>

                                        <p style="font-size: 12px; color: #666; margin-top: 10px;">
                                            ℹ️ <strong>Note:</strong> Fixed ₹500 advance payment required now. 
                                            The remaining balance will be payable after service completion.
                                        </p>
                                    </div>

                                    <!-- Hidden fields for location coordinates (latitude and longitude for database) -->
                                    <input type="hidden" name="latitude" id="latitude" value="">
                                    <input type="hidden" name="longitude" id="longitude" value="">

                                   <div class="form-btn" style="margin-top: 25px;">
                                        <div class="shopping-button">
                                            <button class="submit-btn" name="submit" id="submitBtn" disabled style="opacity: 0.6; cursor: not-allowed;">Proceed to Payment</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      <?php include_once('includes/footer.php');?>
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

        <!-- Geolocation Script -->
        <script>
            // Service charge data - Towing Services Only
            const serviceCharges = {
                'Bike Towing': {baseCharge: 1000, advance: 500},
                'Car Towing': {baseCharge: 3000, advance: 500},
                'Truck Towing': {baseCharge: 6000, advance: 500}
            };

            function updatePaymentInfo() {
                const serviceType = document.getElementById('service_type').value;
                const paymentInfo = document.getElementById('paymentInfo');
                const submitBtn = document.getElementById('submitBtn');
                
                if (serviceType && serviceCharges[serviceType]) {
                    const charges = serviceCharges[serviceType];
                    const baseCharge = charges.baseCharge;
                    const advanceAmount = charges.advance; // Fixed ₹500 advance payment
                    const remainingAmount = baseCharge - advanceAmount;
                    
                    document.getElementById('baseCharge').textContent = '₹' + baseCharge;
                    document.getElementById('advanceAmount').textContent = '₹' + advanceAmount;
                    document.getElementById('remainingAmount').textContent = '₹' + remainingAmount;
                    
                    paymentInfo.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    submitBtn.style.cursor = 'pointer';
                } else {
                    paymentInfo.style.display = 'none';
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.6';
                    submitBtn.style.cursor = 'not-allowed';
                }
            }

            // Get location button click handler
            document.getElementById('getLocationBtn').addEventListener('click', function(e) {
                e.preventDefault();
                getLocation();
            });

            // Function to get user's location on button click
            function getLocation() {
                if (navigator.geolocation) {
                    // Show status message
                    document.getElementById('locationStatus').style.display = 'block';
                    document.getElementById('locationMessage').textContent = 'Getting your location...';
                    document.getElementById('locationMessage').style.color = '#666';

                    // Request location with timeout and accuracy options
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            // Success callback
                            var latitude = position.coords.latitude;
                            var longitude = position.coords.longitude;

                            // Format coordinates for display
                            var locationText = latitude.toFixed(6) + ', ' + longitude.toFixed(6);

                            // Populate Pickup Location field with coordinates
                            document.getElementById('pickuploc').value = locationText;

                            // Also populate hidden fields for database storage
                            document.getElementById('latitude').value = latitude;
                            document.getElementById('longitude').value = longitude;

                            // Update status message
                            document.getElementById('locationMessage').textContent =
                                '✓ Location captured successfully: ' + locationText;
                            document.getElementById('locationMessage').style.color = '#28a745';
                        },
                        function(error) {
                            // Error callback
                            var errorMessage = 'Location not available';

                            // Specific error messages
                            if (error.code === error.PERMISSION_DENIED) {
                                errorMessage = 'Location permission denied. Please allow location access in your browser settings.';
                            } else if (error.code === error.POSITION_UNAVAILABLE) {
                                errorMessage = 'Location information not available. Please enable location services.';
                            } else if (error.code === error.TIMEOUT) {
                                errorMessage = 'Location request timeout. Please try again.';
                            }

                            document.getElementById('locationMessage').textContent = errorMessage;
                            document.getElementById('locationMessage').style.color = '#dc3545';
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,  // 10 second timeout
                            maximumAge: 0    // Don't use cached position
                        }
                    );
                } else {
                    // Geolocation not supported
                    document.getElementById('locationStatus').style.display = 'block';
                    document.getElementById('locationMessage').textContent =
                        'Geolocation not supported by your browser. Please enter location manually.';
                    document.getElementById('locationMessage').style.color = '#ffc107';
                }
            }
        </script>
    </body>
</html>