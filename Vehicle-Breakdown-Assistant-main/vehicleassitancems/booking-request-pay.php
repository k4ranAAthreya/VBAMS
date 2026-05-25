<?php
include('includes/dbconnection.php');
include('includes/payment-config.php');
session_start();
error_reporting(0);

// Handle booking submission
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
    
    // Calculate total charge
    $distance = isset($_POST['distance']) ? floatval($_POST['distance']) : 0;
    $totalAmount = calculateTotalCharge($serviceType, $distance);
    $advancePayment = calculateAdvancePayment($totalAmount);
    
    try {
        // Insert booking record  
        $sql = "INSERT INTO tblbook(BookingNumber, Name, Email, PhoneNumber, PickupLoc, Destination, PickupDate, PickupTime, VehicleType, Latitude, Longitude, ServiceType, AdvancePaymentAmount, TotalAmount, BookingStatusFlow, PaymentStatus)
                VALUES(:bookingnumber, :name, :email, :phone, :pickuploc, :destination, :pickupdate, :pickuptime, :vehicletype, :latitude, :longitude, :service_type, :advance_amount, :total_amount, 'Payment Pending', 'Pending')";
        
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
        $query->bindParam(':service_type', $serviceType, PDO::PARAM_STR);
        $query->bindParam(':advance_amount', $advancePayment, PDO::PARAM_STR);
        $query->bindParam(':total_amount', $totalAmount, PDO::PARAM_STR);
        $query->execute();
        
        $LastInsertId = $dbh->lastInsertId();
        
        if ($LastInsertId > 0) {
            // Create initial tracking history
            $sql_tracking = "INSERT INTO tbltracking(BookingNumber, Status, Remark, UpdationDate)
                            VALUES(:bookingnumber, 'Payment Pending', 'Awaiting advance payment', NOW())";
            $query_tracking = $dbh->prepare($sql_tracking);
            $query_tracking->bindParam(':bookingnumber', $bookingnumber, PDO::PARAM_STR);
            $query_tracking->execute();
            
            // Redirect to payment page
            $_SESSION['booking_number'] = $bookingnumber;
            $_SESSION['amount'] = $advancePayment;
            $_SESSION['total_amount'] = $totalAmount;
            
            echo '<script>alert("Booking created successfully!\n\nBooking Number: ' . $bookingnumber . '\n\nYou will now be redirected to payment page.")
            window.location.href = "payment-confirm.php?booking_id=' . $bookingnumber . '";</script>';
        } else {
            echo '<script>alert("Error: Booking creation failed. Please try again.")</script>';
        }
        
    } catch (Exception $e) {
        echo '<script>alert("Error: ' . htmlspecialchars($e->getMessage()) . '")</script>';
    }
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html class="no-js" lang="zxx">
    <head>
        <title>VBAMS || Request Form with Payment </title>
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

            .charge-summary { 
                background: linear-gradient(135deg, var(--light-color) 0%, #e5e7eb 100%); 
                padding: 25px; 
                border-radius: var(--border-radius); 
                margin: 20px 0; 
                border: 1px solid #e5e7eb;
                box-shadow: var(--shadow-sm);
            }
            
            .charge-item { 
                display: flex; 
                justify-content: space-between; 
                margin: 12px 0; 
                padding: 10px 0;
                border-bottom: 1px solid #f3f4f6;
                transition: all 0.2s ease;
            }

            .charge-item:hover {
                background: rgba(99, 102, 241, 0.05);
                margin: 0 -10px;
                padding: 10px;
            }
            
            .charge-item:last-child {
                border-bottom: none;
            }
            
            .charge-label { 
                font-weight: 600; 
                color: #6b7280;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .charge-value { 
                font-weight: 700; 
                color: var(--primary-color); 
                font-size: 1.1rem;
            }
            
            .payment-info { 
                background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%); 
                border-left: 4px solid var(--primary-color); 
                padding: 20px; 
                margin: 20px 0; 
                border-radius: var(--border-radius);
                box-shadow: var(--shadow-sm);
            }

            .payment-info h4 {
                color: #1e40af;
                margin-bottom: 10px;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .service-select { 
                border: 2px solid #e5e7eb; 
                padding: 12px; 
                border-radius: var(--border-radius);
                transition: all 0.3s ease;
                background: white;
            }

            .service-select:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            }

            .form-control {
                border: 1px solid #e5e7eb;
                border-radius: var(--border-radius);
                padding: 12px 15px;
                transition: all 0.3s ease;
                background: white;
            }

            .form-control:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            }

            .submit-btn {
                background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
                color: white;
                padding: 15px 30px;
                border: none;
                border-radius: var(--border-radius);
                font-weight: 600;
                font-size: 16px;
                cursor: pointer;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }

            .submit-btn::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
                transition: left 0.5s;
            }

            .submit-btn:hover::before {
                left: 100%;
            }

            .submit-btn:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow);
            }

            .progress-indicator {
                display: flex;
                justify-content: space-between;
                margin-bottom: 30px;
                position: relative;
            }

            .progress-line {
                position: absolute;
                top: 20px;
                left: 0;
                right: 0;
                height: 2px;
                background: #e5e7eb;
                z-index: 0;
            }

            .progress-fill {
                position: absolute;
                top: 20px;
                left: 0;
                height: 2px;
                background: var(--success-color);
                z-index: 1;
                transition: width 0.3s ease;
            }

            .step {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: white;
                border: 2px solid #e5e7eb;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                position: relative;
                z-index: 1;
                transition: all 0.3s ease;
            }

            .step.active {
                background: var(--primary-color);
                border-color: var(--primary-color);
                color: white;
            }

            .step.completed {
                background: var(--success-color);
                border-color: var(--success-color);
                color: white;
            }

            .highlight-box {
                background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
                border-left: 4px solid var(--warning-color);
                padding: 20px;
                border-radius: var(--border-radius);
                margin: 20px 0;
            }

            .highlight-box.success {
                background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
                border-left-color: var(--success-color);
            }

            .amount-display {
                font-size: 2rem;
                font-weight: 700;
                color: var(--primary-color);
                text-align: center;
                margin: 20px 0;
                padding: 20px;
                background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
                border-radius: var(--border-radius);
                border: 2px dashed #3b82f6;
            }

            @media (max-width: 768px) {
                .charge-summary {
                    padding: 15px;
                    margin: 15px 0;
                }
                
                .payment-info {
                    padding: 15px;
                }
            }
        </style>
        <script src="js/vendor/modernizr-2.8.3.min.js"></script>
    </head>
    <body>
        <?php include_once('includes/header.php');?>
        
        <div class="page-title-area overlay">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="page-title">
                            <h2>Book Breakdown Assistance</h2>
                        </div>
                        <div class="page-title-menu">
                            <ul>
                                <li><a href="index.php">Home</a> <span> / </span> </li>
                                <li><a href="booking-request-pay.php">Request Form</a></li>
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
                                <h3>Breakdown Assistance Booking Form</h3>
                            </div>
                            
                            <div class="payment-info">
                                <strong><i class="fa fa-info-circle"></i> Payment Required</strong><br>
                                <small>Advance payment confirmation helps us prevent fake bookings and ensure prompt service delivery.</small>
                            </div>
                            
                            <div class="client-address-form">
                                <form action="" method="post" id="bookingForm">
                                    <label>Name *</label>
                                    <input class="form-control" type="text" placeholder="Enter your name" name="name" required="true" pattern="[A-Za-z\s]+" title="Name should only contain letters and spaces">
                                    
                                    <label class="form-label">Email *</label>
                                    <input class="form-control" type="email" placeholder="Enter your email" name="email" required="true">
                                   
                                    <label class="form-label">Phone Number *</label>
                                    <input class="form-control" type="tel" placeholder="Enter your phone number" name="phone" required="true" maxlength="10" pattern="[0-9]{10}">
                                    
                                    <label class="form-label">Service Type *</label>
                                    <select class="form-control service-select" name="service_type" id="serviceType" required="true" onchange="updateCharges()">
                                        <option value="">Select Service Type</option>
                                        <?php
                                        $services = SERVICE_TYPES;
                                        foreach ($services as $key => $service) {
                                            echo '<option value="' . $key . '">' . $service['name'] . ' - ₹' . $service['baseCharge'] . '+ (per ' . ($service['pricePerKM'] > 0 ? 'km' : 'service') . ')</option>';
                                        }
                                        ?>
                                    </select>
                                    
                                    <label class="form-label">Pickup Location *</label>
                                    <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                                        <input class="form-control" type="text" placeholder="Enter Location or click Get Location" name="pickuploc" id="pickuploc" required="true" style="flex: 1;">
                                        <button type="button" class="btn btn-info" id="getLocationBtn" style="white-space: nowrap;">Get Location</button>
                                    </div>
                                    
                                    <label class="form-label">Destination *</label>
                                    <input class="form-control" type="text" placeholder="Enter Destination" name="destination" required="true">
                                  
                                    <label class="form-label">Estimated Distance (KM) *</label>
                                    <input class="form-control" type="number" placeholder="Enter distance in kilometers" name="distance" id="distance" step="0.1" min="0" required="true" onchange="updateCharges()">
                                    
                                    <label class="form-label">Pickup Date *</label>
                                    <input class="form-control" type="date" required="true" name="pickupdate" min="<?php echo date('Y-m-d'); ?>">
                                    
                                    <label class="form-label">Pickup Time *</label>
                                    <input class="form-control" type="time" required="true" name="pickuptime">
                                    
                                    <label class="form-label">Vehicle Type *</label>
                                    <select class="form-control" name="vehicletype" required="true">
                                        <option value="">Select Vehicle Type</option>
                                        <option value="Bike">Bike</option>
                                        <option value="Car">Car</option>
                                        <option value="Bus/Truck">Bus/Truck</option>
                                    </select>

                                    <!-- Hidden fields -->
                                    <input type="hidden" name="latitude" id="latitude" value="">
                                    <input type="hidden" name="longitude" id="longitude" value="">

                                    <!-- Charges Summary -->
                                    <div class="charge-summary">
                                        <h5><strong>Service Charges Summary</strong></h5>
                                        <div class="charge-item">
                                            <span class="charge-label">Base Charge:</span>
                                            <span class="charge-value" id="baseCharge">₹0</span>
                                        </div>
                                        <div class="charge-item">
                                            <span class="charge-label">Distance Charge:</span>
                                            <span class="charge-value" id="distanceCharge">₹0</span>
                                        </div>
                                        <hr style="margin: 10px 0;">
                                        <div class="charge-item">
                                            <span class="charge-label"><strong>Total Amount:</strong></span>
                                            <span class="charge-value" id="totalAmount"><strong>₹0</strong></span>
                                        </div>
                                        <div class="charge-item">
                                            <span class="charge-label"><strong>Advance Payment (Fixed):</strong></span>
                                            <span class="charge-value" id="advanceAmount"><strong>₹500</strong></span>
                                        </div>
                                        <hr style="margin: 10px 0;">
                                        <small style="color: #666;">Fixed ₹500 advance payment required. Remaining balance payable after service completion.</small>
                                    </div>

                                    <div class="form-btn">
                                        <div class="shopping-button">
                                            <button type="submit" class="submit-btn" name="submit">Proceed to Payment</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="client-address">
                            <div class="section-small-title">
                                <h3>Service Information</h3>
                            </div>
                            
                            <div style="background: #f9f9f9; padding: 20px; border-radius: 5px;">
                                <h5>How Our Service Works:</h5>
                                <ol style="margin: 15px 0;">
                                    <li><strong>Submit Request:</strong> Fill the form with your vehicle details</li>
                                    <li><strong>Fixed Advance (₹500):</strong> Pay ₹500 advance to confirm booking</li>
                                    <li><strong>Admin Approval:</strong> Our team reviews and approves your booking</li>
                                    <li><strong>Service Dispatch:</strong> A service provider is assigned</li>
                                    <li><strong>Service Delivery:</strong> Get towing assistance at your location</li>
                                    <li><strong>Final Settlement:</strong> Pay remaining amount after service</li>
                                </ol>
                                
                                <hr>
                                
                                <h5 style="margin-top: 20px;">Service Types Available:</h5>
                                <ul style="margin: 15px 0;">
                                    <li><strong>Bike Towing:</strong> Motorcycle and scooter towing (₹1000)</li>
                                    <li><strong>Car Towing:</strong> Four-wheeler car towing (₹3000)</li>
                                    <li><strong>Truck Towing:</strong> Heavy vehicle and truck towing (₹6000)</li>
                                </ul>
                                
                                <hr>
                                
                                <h5 style="margin-top: 20px;">Why Advance Payment?</h5>
                                <ul style="margin: 15px 0;  color: #555;">
                                    <li>✓ Prevents fake or unserious bookings</li>
                                    <li>✓ Ensures priority service</li>
                                    <li>✓ Guarantees service provider commitment</li>
                                    <li>✓ Faster response time</li>
                                    <li>✓ Easy refund if booking is rejected</li>
                                </ul>
                                
                                <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-top: 20px;">
                                    <strong><i class="fa fa-shield"></i> Safe & Secure</strong><br>
                                    <small>Your payment is secured by Razorpay. We never store your card details.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include_once('includes/footer.php');?>

        <script>
        // Get Location
        document.getElementById('getLocationBtn').addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;
                    
                    // Reverse geocode to get address (using OpenStreetMap)
                    fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng)
                        .then(response => response.json())
                        .then(data => {
                            if (data.address) {
                                var address = data.address.city || data.address.town || data.address.county || '';
                                document.getElementById('pickuploc').value = address;
                            }
                        });
                    
                    alert('Location captured: ' + lat + ', ' + lng);
                });
            } else {
                alert('Geolocation is not supported by this browser');
            }
        });
        
        // Calculate and update charges
        function updateCharges() {
            var serviceType = document.getElementById('serviceType').value;
            var distance = parseFloat(document.getElementById('distance').value) || 0;
            
            if (!serviceType) {
                resetCharges();
                return;
            }
            
            // Get service charges via AJAX
            fetch('api/payment-process.php?action=get-charge&service_type=' + encodeURIComponent(serviceType))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        var charges = data.data;
                        var baseCharge = parseFloat(charges.baseCharge);
                        var distanceCharge = distance * parseFloat(charges.pricePerKM);
                        var total = baseCharge + distanceCharge;
                        
                        // Apply min/max limits
                        if (total < parseFloat(charges.minimumCharge)) {
                            total = parseFloat(charges.minimumCharge);
                        }
                        if (charges.maximumCharge > 0 && total > parseFloat(charges.maximumCharge)) {
                            total = parseFloat(charges.maximumCharge);
                        }
                        
                        // Use fixed ₹500 advance payment for towing services
                        var advanceAmount = 500;
                        
                        document.getElementById('baseCharge').textContent = '₹' + baseCharge.toFixed(2);
                        document.getElementById('distanceCharge').textContent = '₹' + distanceCharge.toFixed(2);
                        document.getElementById('totalAmount').textContent = '₹' + total.toFixed(2);
                        document.getElementById('advanceAmount').textContent = '₹' + advanceAmount.toFixed(2);
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        function resetCharges() {
            document.getElementById('baseCharge').textContent = '₹0.00';
            document.getElementById('distanceCharge').textContent = '₹0.00';
            document.getElementById('totalAmount').textContent = '₹0.00';
            document.getElementById('advanceAmount').textContent = '₹500.00';
        }
        </script>
    </body>
</html>
