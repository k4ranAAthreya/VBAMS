<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['vamsaid']==0)) {
  header('location:logout.php');
} else {
  if(isset($_POST['submit'])) {

    $driid=$_POST['driid'];
    $name=$_POST['name'];
    $mobnum=$_POST['mobnum'];
    $email=$_POST['email'];
    $address=$_POST['address'];
    $password=md5($_POST['password']);

    $ret="select Email from tbldriver where Email=:email || MobileNumber=:mobnum || DriverID=:driid";
    $query= $dbh -> prepare($ret);
    $query->bindParam(':driid',$driid,PDO::PARAM_STR);
    $query->bindParam(':mobnum',$mobnum,PDO::PARAM_STR);
    $query->bindParam(':email',$email,PDO::PARAM_STR);
    $query-> execute();
    $results = $query -> fetchAll(PDO::FETCH_OBJ);

    if($query -> rowCount() == 0) {
      $sql="insert into tbldriver(DriverID,Name,MobileNumber,Email,Address,Password)values(:driid,:name,:mobnum,:email,:address,:password)";
      $query=$dbh->prepare($sql);
      $query->bindParam(':driid',$driid,PDO::PARAM_STR);
      $query->bindParam(':name',$name,PDO::PARAM_STR);
      $query->bindParam(':mobnum',$mobnum,PDO::PARAM_STR);
      $query->bindParam(':email',$email,PDO::PARAM_STR);
      $query->bindParam(':address',$address,PDO::PARAM_STR);
      $query->bindParam(':password',$password,PDO::PARAM_STR);
      $query->execute();

      $LastInsertId=$dbh->lastInsertId();
      if ($LastInsertId>0) {
        echo '<script>alert("Driver detail has been added.")</script>';
        echo "<script>window.location.href ='add-driver.php'</script>";
      } else {
        echo '<script>alert("Something Went Wrong. Please try again")</script>';
      }
    } else {
      echo "<script>alert('Email-id, Employee Id or Mobile Number already exist. Please try again');</script>";
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
<title>Vehicle Break Down Assistance Management System: Add Driver</title>

<link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
<link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
<link rel="stylesheet" href="../assets/vendor/bootstrap-multiselect/bootstrap-multiselect.css">
<link rel="stylesheet" href="../assets/vendor/parsleyjs/css/parsley.css">
<link rel="stylesheet" href="../assets/css/main.css" type="text/css">
</head>
<body class="theme-indigo">
    <?php include_once('includes/header.php');?>

    <div class="main_content" id="main-content">
       <?php include_once('includes/sidebar.php');?>

        <div class="page">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="javascript:void(0);">Add Driver</a>
            </nav>
            <div class="container-fluid">
                <div class="row clearfix">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="header">
                                <h2>Add Driver</h2>
                            </div>
                            <div class="body">
                                <form id="basic-form" method="post" data-parsley-validate>
                                    <div class="form-group">
                                        <label>Driver ID</label>
                                        <input type="text" class="form-control" name="driid" required maxlength="10">
                                    </div>
                                    <div class="form-group">
                                        <label>Name</label>
                                        <!-- Only alphabets allowed -->
                                        <input type="text" class="form-control" name="name" required 
                                               pattern="^[A-Za-z\s]+$" 
                                               data-parsley-pattern="^[A-Za-z\s]+$" 
                                               data-parsley-pattern-message="Name must contain only letters">
                                    </div>
                                    <div class="form-group">
                                        <label>Mobile Number</label>
                                        <!-- Exactly 10 digits -->
                                        <input type="text" class="form-control" name="mobnum" required 
                                               pattern="^\d{10}$" 
                                               data-parsley-pattern="^\d{10}$" 
                                               data-parsley-pattern-message="Mobile number must be exactly 10 digits">
                                    </div>
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Address</label>
                                        <textarea class="form-control" name="address" required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" class="form-control" name="password" required minlength="6">
                                    </div>
                                    <br>
                                    <button type="submit" class="btn btn-primary" name="submit">Add</button>
                                </form>
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
<script src="../assets/vendor/bootstrap-multiselect/bootstrap-multiselect.js"></script>
<script src="../assets/vendor/parsleyjs/js/parsley.min.js"></script>

<!-- Theme JS -->
<script src="../assets/js/theme.js"></script>
<script>
    $(function() {
        // initialize parsley validation
        $('#basic-form').parsley();
    });
</script>
</body>
</html>