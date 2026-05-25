<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
include('../includes/payment-config.php');

if (strlen($_SESSION['vamsaid']==0)) {
    header('location:logout.php');
} else {
?>
<!doctype html>
<html lang="en">
<head>
    <title>Payment Management - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .payment-badge { padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-success { background: #4CAF50; color: white; }
        .badge-pending { background: #FF9800; color: white; }
        .badge-failed { background: #f44336; color: white; }
        .badge-refunded { background: #2196F3; color: white; }
        .payment-info { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .info-row { display: flex; justify-content: space-between; margin: 10px 0; padding: 8px 0; border-bottom: 1px solid #ddd; }
        .info-label { font-weight: 500; color: #333; }
        .info-value { color: #666; }
        .refund-btn { background: #2196F3; color: white; padding: 8px 15px; border: none; border-radius: 3px; cursor: pointer; font-size: 12px; }
        .refund-btn:hover { background: #1976D2; }
        .refund-btn:disabled { background: #ccc; cursor: not-allowed; }
        .action-btn { padding: 8px 15px; margin: 5px; border: none; border-radius: 3px; cursor: pointer; font-size: 12px; }
        .btn-approve { background: #4CAF50; color: white; }
        .btn-reject { background: #f44336; color: white; }
        .btn-approve:hover { background: #45a049; }
        .btn-reject:hover { background: #da190b; }
    </style>
</head>
<body class="theme-indigo">
    <?php include_once('includes/header.php');?>
    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php');?>
        <div class="page">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="javascript:void(0);">Payment Management Dashboard</a>
            </nav>
            
            <div class="container-fluid">
                <!-- Payment Summary Cards -->
                <div class="row clearfix">
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="body">
                                <h6 class="text-muted">Total Payments</h6>
                                <h2 class="font-weight-bold">
                                    <?php
                                    $sql = "SELECT COUNT(*) as count FROM tblpayments WHERE PaymentStatus = 'Completed'";
                                    $query = $dbh->prepare($sql);
                                    $query->execute();
                                    $result = $query->fetch(PDO::FETCH_OBJ);
                                    echo $result->count;
                                    ?>
                                </h2>
                                <small class="text-muted">Completed payments</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="body">
                                <h6 class="text-muted">Total revenue</h6>
                                <h2 class="font-weight-bold">
                                    ₹<?php
                                    $sql =  "SELECT SUM(Amount) as total FROM tblpayments WHERE PaymentStatus = 'Completed'";
                                    $query = $dbh->prepare($sql);
                                    $query->execute();
                                    $result = $query->fetch(PDO::FETCH_OBJ);
                                    echo number_format($result->total, 2);
                                    ?>
                                </h2>
                                <small class="text-muted">Advance payments collected</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="body">
                                <h6 class="text-muted">Pending Refunds</h6>
                                <h2 class="font-weight-bold">
                                    <?php
                                    $sql = "SELECT COUNT(*) as count FROM tblrefunds WHERE RefundStatus != 'Completed'";
                                    $query = $dbh->prepare($sql);
                                    $query->execute();
                                    $result = $query->fetch(PDO::FETCH_OBJ);
                                    echo $result->count;
                                    ?>
                                </h2>
                                <small class="text-muted">In processing</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="body">
                                <h6 class="text-muted">Failed Payments</h6>
                                <h2 class="font-weight-bold">
                                    <?php
                                    $sql = "SELECT COUNT(*) as count FROM tblpayments WHERE PaymentStatus = 'Failed'";
                                    $query = $dbh->prepare($sql);
                                    $query->execute();
                                    $result = $query->fetch(PDO::FETCH_OBJ);
                                    echo $result->count;
                                    ?>
                                </h2>
                                <small class="text-muted">Failed transactions</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Transactions Table -->
                <div class="row clearfix">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="header">
                                <h2><strong>Payment</strong> Transactions</h2>
                            </div>
                            <div class="body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Booking #</th>
                                                <th>User Name</th>
                                                <th>Amount</th>
                                                <th>Payment Status</th>
                                                <th>Payment Date</th>
                                                <th>Gateway</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT p.*, b.Name, b.Email, b.PhoneNumber, b.BookingStatusFlow
                                                    FROM tblpayments p
                                                    JOIN tblbook b ON p.BookingNumber = b.BookingNumber
                                                    ORDER BY p.CreatedAt DESC
                                                    LIMIT 100";
                                            
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            $payments = $query->fetchAll(PDO::FETCH_OBJ);
                                            
                                            $cnt = 1;
                                            foreach ($payments as $payment) {
                                                $statusClass = '';
                                                if ($payment->PaymentStatus === 'Completed') $statusClass = 'badge-success';
                                                elseif ($payment->PaymentStatus === 'Pending') $statusClass = 'badge-pending';
                                                elseif ($payment->PaymentStatus === 'Failed') $statusClass = 'badge-failed';
                                                ?>
                                                <tr>
                                                    <td><?php echo $cnt++; ?></td>
                                                    <td><strong><?php echo htmlspecialchars($payment->BookingNumber); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($payment->Name); ?></td>
                                                    <td><strong>₹<?php echo number_format($payment->Amount, 2); ?></strong></td>
                                                    <td><span class="payment-badge <?php echo $statusClass; ?>"><?php echo $payment->PaymentStatus; ?></span></td>
                                                    <td><?php echo $payment->PaymentDate ? date('d-m-Y H:i', strtotime($payment->PaymentDate)) : 'N/A'; ?></td>
                                                    <td><?php echo htmlspecialchars($payment->PaymentGateway); ?></td>
                                                    <td>
                                                        <button onclick="viewPaymentDetails(<?php echo $payment->PaymentID; ?>)" class="action-btn" style="background:#2196F3; color:white;">View</button>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Refund Management Table -->
                <div class="row clearfix">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="header">
                                <h2><strong>Refund</strong> Management</h2>
                            </div>
                            <div class="body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Booking #</th>
                                                <th>User Name</th>
                                                <th>Refund Amount</th>
                                                <th>Status</th>
                                                <th>Initiated Date</th>
                                                <th>Reason</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT r.*, b.Name, b.Email, b.PhoneNumber
                                                    FROM tblrefunds r
                                                    JOIN tblbook b ON r.BookingNumber = b.BookingNumber
                                                    ORDER BY r.InitiatedAt DESC
                                                    LIMIT 50";
                                            
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            $refunds = $query->fetchAll(PDO::FETCH_OBJ);
                                            
                                            $cnt = 1;
                                            foreach ($refunds as $refund) {
                                                $statusClass = '';
                                                if ($refund->RefundStatus === 'Completed') $statusClass = 'badge-success';
                                                elseif ($refund->RefundStatus === 'Processing') $statusClass = 'badge-pending';
                                                elseif ($refund->RefundStatus === 'Failed') $statusClass = 'badge-failed';
                                                ?>
                                                <tr>
                                                    <td><?php echo $cnt++; ?></td>
                                                    <td><strong><?php echo htmlspecialchars($refund->BookingNumber); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($refund->Name); ?></td>
                                                    <td><strong>₹<?php echo number_format($refund->RefundAmount, 2); ?></strong></td>
                                                    <td><span class="payment-badge <?php echo $statusClass; ?>"><?php echo $refund->RefundStatus; ?></span></td>
                                                    <td><?php echo date('d-m-Y H:i', strtotime($refund->InitiatedAt)); ?></td>
                                                    <td><?php echo htmlspecialchars($refund->RefundReason); ?></td>
                                                    <td>
                                                        <button onclick="viewRefundDetails(<?php echo $refund->RefundID; ?>)" class="action-btn" style="background:#2196F3; color:white;">View</button>
                                                    </td>
                                                </tr>
                                            <?php } ?>
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
    
    <script src="../assets/bundles/libscripts.bundle.js"></script>
    <script src="../assets/bundles/vendorscripts.bundle.js"></script>
    <script src="../assets/bundles/datatablescripts.bundle.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/dataTables.buttons.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.bootstrap4.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.colVis.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.flash.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.html5.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.print.min.js"></script>
    <script src="../assets/bundles/mainscripts.bundle.js"></script>
    
    <script>
    function viewPaymentDetails(paymentId) {
        // Implement modal or new page to show payment details
        alert('Opening payment details for ID: ' + paymentId);
    }
    
    function viewRefundDetails(refundId) {
        // Implement modal or new page to show refund details
        alert('Opening refund details for ID: ' + refundId);
    }
    </script>
</body>
</html>
<?php } ?>
