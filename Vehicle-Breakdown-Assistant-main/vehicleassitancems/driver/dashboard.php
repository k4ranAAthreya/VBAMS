<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['vamsid']==0)) {
  header('location:logout.php');
  } else{



  ?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Vehicle Break Down Assistance Management System: Dashboard</title>

<link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
<link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
<link rel="stylesheet" href="../assets/css/main.css" type="text/css">
</head>
<body class="theme-indigo">

<?php include_once('includes/header.php');?>

<div class="main_content" id="main-content">

    <?php include_once('includes/sidebar.php');?>

    

    <div class="page">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="dashboard.php">Dashboard</a>
        </nav>
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="card widget_2 big_icon traffic">
                        <div class="body">
                            <?php 
                         $did=$_SESSION['vamsdid'];
// Get driver name from session using driver ID
$driverSql = "SELECT Name FROM tbldriver WHERE DriverID = :did LIMIT 1";
$driverQuery = $dbh->prepare($driverSql);
$driverQuery->bindParam(':did', $did, PDO::PARAM_STR);
$driverQuery->execute();
$driverData = $driverQuery->fetch(PDO::FETCH_OBJ);
$driverName = $driverData ? $driverData->Name : '';

$sql ="SELECT * from tblbook where Status='Approved' && AssignTo=:driverName ";
$query = $dbh -> prepare($sql);
$query-> bindParam(':driverName', $driverName, PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$totnewrequest=$query->rowCount();
?>
                            <h6 style="color: red;">Total New Request</h6>
                            <h2><?php echo htmlentities($totnewrequest);?></h2>
                            <a href="new-request.php"><small> View Detail</small></a>
                          
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="card widget_2 big_icon sales">
                        <div class="body">
                             <?php 
                         $did=$_SESSION['vamsdid'];
// Get driver name from session using driver ID
$driverSql = "SELECT Name FROM tbldriver WHERE DriverID = :did LIMIT 1";
$driverQuery = $dbh->prepare($driverSql);
$driverQuery->bindParam(':did', $did, PDO::PARAM_STR);
$driverQuery->execute();
$driverData = $driverQuery->fetch(PDO::FETCH_OBJ);
$driverName = $driverData ? $driverData->Name : '';

$sql ="SELECT * from  tblbook where Status='On The Way' && AssignTo=:driverName ";
$query = $dbh -> prepare($sql);
$query-> bindParam(':driverName', $driverName, PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$tototwrequest=$query->rowCount();
?>
                            <h6 style="color: orange;">Inprogress Request</h6>
                           <h2><?php echo htmlentities($tototwrequest);?></h2>
                            <a href="ontheway.php"><small> View Detail</small></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="card widget_2 big_icon email">
                        <div class="body">
                            <?php 
                         $did=$_SESSION['vamsdid'];
// Get driver name from session using driver ID
$driverSql = "SELECT Name FROM tbldriver WHERE DriverID = :did LIMIT 1";
$driverQuery = $dbh->prepare($driverSql);
$driverQuery->bindParam(':did', $did, PDO::PARAM_STR);
$driverQuery->execute();
$driverData = $driverQuery->fetch(PDO::FETCH_OBJ);
$driverName = $driverData ? $driverData->Name : '';

$sql ="SELECT * from  tblbook where Status='Completed' && AssignTo=:driverName ";
$query = $dbh -> prepare($sql);
$query-> bindParam(':driverName', $driverName, PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$totcomprequest=$query->rowCount();
?>
                            <h6 style="color: green;">Completed Request</h6>
                           
                            <h2><?php echo htmlentities($totcomprequest);?></h2>
                            <a href="completed.php"><small> View Detail</small></a>
                        </div>
                    </div>
                </div>
             
            </div>
        </div>
    </div>    
</div>

<!-- Core -->
<script src="../assets/bundles/libscripts.bundle.js"></script>
<script src="../assets/bundles/vendorscripts.bundle.js"></script>
<script src="../assets/js/theme.js"></script>
</body>
</html><?php } ?>