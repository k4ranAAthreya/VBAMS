<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['vamsaid']==0)) {
  header('location:logout.php');
  } else{

// Handle booking approval with driver assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_action'])) {
    $booking_id = isset($_POST['booking_id']) ? trim(htmlspecialchars($_POST['booking_id'])) : '';
    $driver_name = isset($_POST['driver_name']) ? trim(htmlspecialchars($_POST['driver_name'])) : '';
    
    // Debug: log received values
    error_log("Approval attempt - Booking: " . $booking_id . ", Driver: " . $driver_name);
    
    // Driver is MANDATORY - strict validation
    if (empty($driver_name)) {
        $error_msg = "ERROR: Driver assignment is MANDATORY. You must select a driver to approve this booking.";
    } elseif (empty($booking_id)) {
        $error_msg = "ERROR: Booking ID not received. Please refresh the page and try again.";
    } else {
        try {
            // Verify driver exists in database (without Status constraint)
            $verifyDriver = "SELECT DriverID FROM tbldriver WHERE Name = :driver_name LIMIT 1";
            $verifyQuery = $dbh->prepare($verifyDriver);
            $verifyQuery->bindParam(':driver_name', $driver_name, PDO::PARAM_STR);
            $verifyQuery->execute();
            
            if ($verifyQuery->rowCount() === 0) {
                throw new Exception("Selected driver '" . $driver_name . "' not found in database.");
            }
            
            // Update booking with driver assignment
            $sql = "UPDATE tblbook SET AssignTo = :driver, Status = 'Approved', BookingStatusFlow = 'Confirmed', UpdationDate = NOW() WHERE BookingNumber = :booking_id";
            $query = $dbh->prepare($sql);
            $query->bindParam(':driver', $driver_name, PDO::PARAM_STR);
            $query->bindParam(':booking_id', $booking_id, PDO::PARAM_STR);
            $result = $query->execute();
            
            $rowCount = $query->rowCount();
            error_log("Update result - Rows affected: " . $rowCount);
            
            if ($result && $rowCount > 0) {
                $success_msg = "✓ Booking #" . htmlspecialchars($booking_id) . " approved successfully! Driver assigned: <strong>" . htmlspecialchars($driver_name) . "</strong>";
            } else {
                throw new Exception("Booking update failed (0 rows affected). Booking may not exist.");
            }
        } catch (Exception $e) {
            $error_msg = "Error: " . $e->getMessage();
            error_log("Approval error: " . $e->getMessage());
        }
    }
}

// Handle booking rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_action'])) {
    $booking_id = isset($_POST['booking_id']) ? trim(htmlspecialchars($_POST['booking_id'])) : '';
    $rejection_reason = isset($_POST['rejection_reason']) ? trim(htmlspecialchars($_POST['rejection_reason'])) : 'Admin rejected booking';
    
    if (empty($booking_id)) {
        $error_msg = "ERROR: Booking ID not received.";
    } else {
        try {
            // Update booking status to Rejected
            $sql = "UPDATE tblbook SET Status = 'Rejected', BookingStatusFlow = 'Rejected', UpdationDate = NOW() WHERE BookingNumber = :booking_id";
            $query = $dbh->prepare($sql);
            $query->bindParam(':booking_id', $booking_id, PDO::PARAM_STR);
            $result = $query->execute();
            
            if ($result && $query->rowCount() > 0) {
                // Create tracking history for rejection
                $trackingSql = "INSERT INTO tbltracking(BookingNumber, Status, Remark, UpdationDate) VALUES(:booking_id, 'Rejected', :reason, NOW())";
                $trackingQuery = $dbh->prepare($trackingSql);
                $trackingQuery->bindParam(':booking_id', $booking_id, PDO::PARAM_STR);
                $trackingQuery->bindParam(':reason', $rejection_reason, PDO::PARAM_STR);
                $trackingQuery->execute();
                
                $success_msg = "✓ Booking #" . htmlspecialchars($booking_id) . " has been rejected.";
            } else {
                throw new Exception("Booking rejection failed.");
            }
        } catch (Exception $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    }
}

  ?>
<!doctype html>
<html lang="en">

<head>
  
    <title>Vehicle Break Down Assistance Management System: New Request</title>

    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">

    <link rel="stylesheet" href="../assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css">

    <link  rel="stylesheet" href="../assets/css/main.css">
    <style>
        .modal-approve {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-approve.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-approve-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .modal-approve-content h3 {
            color: #333;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .modal-approve-content .form-group {
            margin-bottom: 15px;
        }
        .modal-approve-content label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .modal-approve-content select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .modal-approve-buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }
        .modal-approve-buttons button {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }
        .btn-approve-confirm {
            background: #4caf50;
            color: white;
        }
        .btn-approve-confirm:hover {
            background: #45a049;
        }
        .btn-approve-cancel {
            background: #f5f5f5;
            color: #333;
        }
        .btn-approve-cancel:hover {
            background: #e0e0e0;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #f5c6cb;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #c3e6cb;
        }
        .error-message {
            color: #f44336;
            font-weight: bold;
            margin-bottom: 10px;
            display: none;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .badge-paid {
            background: #4CAF50;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-pending {
            background: #FF9800;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-confirmed {
            background: #2196F3;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-rejected {
            background: #f44336;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-info {
            background: #17a2b8;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .modal-reject {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-reject.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-reject-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .modal-reject-content h3 {
            color: #f44336;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .modal-reject-content .form-group {
            margin-bottom: 15px;
        }
        .modal-reject-content label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .modal-reject-content textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: Arial, sans-serif;
            resize: vertical;
            min-height: 100px;
            box-sizing: border-box;
        }
        .modal-reject-buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }
        .modal-reject-buttons button {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }
        .btn-reject-confirm {
            background: #f44336;
            color: white;
        }
        .btn-reject-confirm:hover {
            background: #da190b;
        }
        .btn-reject-cancel {
            background: #f5f5f5;
            color: #333;
        }
        .btn-reject-cancel:hover {
            background: #e0e0e0;
        }
    </style>
</head>
<body class="theme-indigo">
    <!-- Page Loader -->
    
<?php include_once('includes/header.php');?>

    <div class="main_content" id="main-content">
       <?php include_once('includes/sidebar.php');?>

      

        <div class="page">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="javascript:void(0);">New Booking Request</a>
            </nav>
            <div class="container-fluid">
                <?php if (isset($success_msg)): ?>
                    <div class="alert-success"><?php echo $success_msg; ?></div>
                    <script>setTimeout(function() { location.reload(); }, 2000);</script>
                <?php endif; ?>
                <?php if (isset($error_msg)): ?>
                    <div class="alert-error"><?php echo $error_msg; ?></div>
                <?php endif; ?>
                <div class="row clearfix">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="header">
                                <h2><strong>New Booking</strong> Request </h2>
                            </div>
                            <div class="body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                        <thead>
                                            <tr>
                                               <th>S.No</th>
                                        <th>Booking #</th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>Email</th>
                                        <th>Service Type</th>
                                        <th>💳 Payment Status</th>
                                        <th>₹ Advance</th>
                                        <th>Booking Status</th>
                                        <th>Driver</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                            </tr>
                                        </thead>
                                       
                                        <tbody>
                                            <tr>
                                               <?php
$sql="SELECT b.*, p.status as payment_db_status, p.amount as paid_amount FROM tblbook b 
      LEFT JOIN tblpayments p ON b.BookingNumber = p.booking_number
      WHERE b.Status is null ORDER BY b.DateofRequest DESC LIMIT 100";
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);

$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $row)
{               ?>
                                              <td><?php echo htmlentities($cnt);?></td>
                                        <td><?php  echo htmlentities($row->BookingNumber);?></td>
                                        <td><?php  echo htmlentities($row->Name);?></td>
                                        <td><?php  echo htmlentities($row->PhoneNumber);?></td>
                                        <td><?php  echo htmlentities($row->Email);?></td>
                                        <td><?php  echo htmlentities($row->ServiceType ?? 'N/A');?></td>
                                        <td>
                                            <?php 
                                            $payStatus = ($row->PaymentStatus === 'Paid' || $row->payment_db_status === 'captured') ? 'Paid' : 'Pending';
                                            $badgeClass = ($payStatus === 'Paid') ? 'badge-paid' : 'badge-pending';
                                            ?>
                                            <span class="<?php echo $badgeClass; ?>">✓ <?php echo $payStatus; ?></span>
                                        </td>
                                        <td><strong>₹<?php echo htmlentities(number_format($row->AdvancePaymentAmount ?? 500, 2)); ?></strong></td>
                                        <td>
                                            <?php 
                                            if($row->Status=="" || $row->Status === null) {
                                                echo '<span class="badge-pending">⏳ Pending</span>';
                                            } else if($row->Status=="Approved") {
                                                echo '<span class="badge-confirmed">✓ Confirmed</span>';
                                            } else if($row->Status=="Rejected") {
                                                echo '<span class="badge-rejected">✗ Rejected</span>';
                                            } else {
                                                echo '<span class="badge-info">' . htmlentities($row->Status) . '</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if(!empty($row->AssignTo)) {
                                                echo '<strong>' . htmlentities($row->AssignTo) . '</strong>';
                                            } else {
                                                echo '<span style="color: #999;">Not Assigned</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($row->DateofRequest)); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="view-booking-detail.php?editid=<?php echo htmlentities ($row->ID);?>&&bookid=<?php echo htmlentities ($row->BookingNumber);?>" class="btn btn-sm btn-info" title="View Details"><i class="fa fa-eye" aria-hidden="true"></i> View</a>
                                                <?php if($row->Status=="" || $row->Status === null){ ?>
                                                    <button class="btn btn-sm" onclick="openApproveModal('<?php echo htmlentities($row->BookingNumber); ?>')" style="background: #4caf50; border: none; color: white; padding: 6px 12px; border-radius: 4px; cursor: pointer;" title="Approve and Assign Driver"><i class="fa fa-check" aria-hidden="true"></i> Approve</button>
                                                    <button class="btn btn-sm" onclick="openRejectModal('<?php echo htmlentities($row->BookingNumber); ?>')" style="background: #f44336; border: none; color: white; padding: 6px 12px; border-radius: 4px; cursor: pointer;" title="Reject Booking"><i class="fa fa-times" aria-hidden="true"></i> Reject</button>
                                                <?php } ?>
                                            </div>
                                        </td>
                                            </tr>
                                         <?php $cnt=$cnt+1;}} else { echo "<tr><td colspan='12' style='text-align:center;'>No new booking requests</td></tr>";} ?> 
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
        </div>
    </div>


<!-- Jquery Core Js --> 
<script src="../assets/bundles/libscripts.bundle.js"></script> <!-- Lib Scripts Plugin Js --> 
<script src="../assets/bundles/vendorscripts.bundle.js"></script> <!-- Lib Scripts Plugin Js --> 

<!-- Jquery DataTable Plugin Js --> 
<script src="../assets/bundles/datatablescripts.bundle.js"></script>
<script src="../assets/vendor/jquery-datatable/buttons/dataTables.buttons.min.js"></script>
<script src="../assets/vendor/jquery-datatable/buttons/buttons.bootstrap4.min.js"></script>
<script src="../assets/vendor/jquery-datatable/buttons/buttons.colVis.min.js"></script>
<script src="../assets/vendor/jquery-datatable/buttons/buttons.flash.min.js"></script>
<script src="../assets/vendor/jquery-datatable/buttons/buttons.html5.min.js"></script>
<script src="../assets/vendor/jquery-datatable/buttons/buttons.print.min.js"></script>

<script src="../assets/js/theme.js"></script><!-- Custom Js --> 
<script src="../assets/js/pages/tables/jquery-datatable.js"></script>

<!-- Approval Modal -->
<div id="approveModal" class="modal-approve">
    <div class="modal-approve-content">
        <h3><i class="fa fa-check-circle"></i> Approve Booking & Assign Driver</h3>
        <form id="approveForm" method="POST">
            <input type="hidden" id="bookingIdInput" name="booking_id">
            <input type="hidden" name="approve_action" value="1">
            
            <div class="form-group">
                <label for="driverSelect" style="color: #f44336;"><strong>MANDATORY: Select Driver</strong></label>
                <p style="font-size: 12px; color: #f44336; margin-top: 5px; margin-bottom: 10px;">Driver assignment is REQUIRED - you cannot approve without selecting a driver</p>
                <select id="driverSelect" name="driver_name" required onchange="validateDriver();" style="border: 2px solid #ddd; transition: all 0.3s;">
                    <option value="" disabled selected style="color: #999;">-- SELECT DRIVER (REQUIRED) --</option>
                    <?php 
                    try {
                        // Fetch all drivers from database
                        $driverSql = "SELECT DriverID, Name, MobileNumber FROM tbldriver ORDER BY Name";
                        $driverQuery = $dbh->prepare($driverSql);
                        $driverQuery->execute();
                        $drivers = $driverQuery->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($drivers) > 0) {
                            foreach ($drivers as $driver) {
                                echo '<option value="' . htmlspecialchars($driver['Name']) . '">' . htmlspecialchars($driver['Name']) . ' (📱 ' . htmlspecialchars($driver['MobileNumber']) . ')</option>';
                            }
                        } else {
                            echo '<option value="">No drivers found in database</option>';
                        }
                    } catch (Exception $e) {
                        echo '<option value="">Error loading drivers: ' . htmlspecialchars($e->getMessage()) . '</option>';
                    }
                    ?>
                </select>
                <div class="error-message" id="driverError" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-top: 10px; display: none; border-left: 4px solid #f5c6cb;">
                    <strong>REQUIRED:</strong> You must select a driver to proceed
                </div>
            </div>
            
            <div class="modal-approve-buttons">
                <button type="submit" id="submitApproveBtn" class="btn-approve-confirm" style="opacity: 0.5; cursor: not-allowed;" disabled>Confirm Approval</button>
                <button type="button" class="btn-approve-cancel" onclick="closeApproveModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openApproveModal(bookingId) {
        document.getElementById('bookingIdInput').value = bookingId;
        document.getElementById('driverSelect').value = '';
        document.getElementById('driverError').style.display = 'none';
        document.getElementById('submitApproveBtn').disabled = true;
        document.getElementById('submitApproveBtn').style.opacity = '0.5';
        document.getElementById('submitApproveBtn').style.cursor = 'not-allowed';
        document.getElementById('approveModal').classList.add('show');
    }

    function closeApproveModal() {
        document.getElementById('approveModal').classList.remove('show');
    }

    // Validate driver selection in real-time
    function validateDriver() {
        const driverSelect = document.getElementById('driverSelect');
        const driverError = document.getElementById('driverError');
        const submitBtn = document.getElementById('submitApproveBtn');
        const selectedValue = driverSelect.value.trim();

        if (!selectedValue || selectedValue === '') {
            driverError.style.display = 'block';
            driverSelect.style.borderColor = '#f44336';
            driverSelect.style.backgroundColor = '#fff5f5';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            submitBtn.style.cursor = 'not-allowed';
            return false;
        } else {
            driverError.style.display = 'none';
            driverSelect.style.borderColor = '#4caf50';
            driverSelect.style.backgroundColor = '#ffffff';
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
            return true;
        }
    }

    // Validate form before submission
    document.getElementById('approveForm').addEventListener('submit', function(e) {
        const driverSelect = document.getElementById('driverSelect').value.trim();
        const driverError = document.getElementById('driverError');
        
        if (!driverSelect || driverSelect === '') {
            e.preventDefault();
            driverError.style.display = 'block';
            driverSelect.style.borderColor = '#f44336';
            alert('ERROR: You MUST select a driver to approve this booking!');
            return false;
        }
        driverError.style.display = 'none';
        return true;
    });

    // Close modal when clicking outside
    document.getElementById('approveModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeApproveModal();
        }
    });
</script>

</body>
</html><?php }  ?>