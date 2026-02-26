<?php
include('includes/dbconnection.php');
session_start();
error_reporting(0);
 if(isset($_POST['submit']))
  {

    $name=$_POST['name'];
    $email=$_POST['email'];
    $pickuploc=$_POST['pickuploc'];
    $destination=$_POST['destination'];
    $phone=$_POST['phone'];
    $pickupdate=$_POST['pickupdate'];
    $pickuptime=$_POST['pickuptime'];
    $vehicletype=$_POST['vehicletype'];
    $bookingnumber = mt_rand(100000000, 999999999);

    // SQL updated to include VehicleType
    $sql="insert into tblbook(BookingNumber,Name,Email,PhoneNumber,PickupLoc,Destination,PickupDate,PickupTime,VehicleType)values(:bookingnumber,:name,:email,:phone,:pickuploc,:destination,:pickupdate,:pickuptime,:vehicletype)";
    $query=$dbh->prepare($sql);
    $query->bindParam(':bookingnumber',$bookingnumber,PDO::PARAM_STR);
    $query->bindParam(':name',$name,PDO::PARAM_STR);
    $query->bindParam(':email',$email,PDO::PARAM_STR);
    $query->bindParam(':phone',$phone,PDO::PARAM_STR);
    $query->bindParam(':pickuploc',$pickuploc,PDO::PARAM_STR);
    $query->bindParam(':destination',$destination,PDO::PARAM_STR);
    $query->bindParam(':pickupdate',$pickupdate,PDO::PARAM_STR);
    $query->bindParam(':pickuptime',$pickuptime,PDO::PARAM_STR);
    $query->bindParam(':vehicletype',$vehicletype,PDO::PARAM_STR);
    $query->execute();

   $LastInsertId=$dbh->lastInsertId();
   if ($LastInsertId>0) {
   echo '<script>alert("Your vehicle breakdown assistance has been book successfully. Booking Number is "+"'.$bookingnumber.'")</script>';
   echo "<script>window.location.href ='booking-request.php'</script>";
  }
  else
    {
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
                                   <input class="form-control" type="text" placeholder="Enter Location" name="pickuploc" required="true">
                                    <label class="form-label">Destination</label>
                                   <input class="form-control" type="text" placeholder="Enter Destination" name="destination" required="true">
                                  <label class="form-label">Pickup Date</label>
                                  <input class="form-control" type="date" required="true" name="pickupdate">
                                   <label class="form-label">Pickup Time</label>
                                   <input class="form-control" type="time" required="true" name="pickuptime">
                                  <label class="form-label">Vehicle Type</label>
                                    <select class="form-control" name="vehicletype" required="true">
                                        <option value="">Select Vehicle Type</option>
                                        <option value="Bike">Bike</option>
                                        <option value="Car">Car</option>
                                        <option value="Bus/Truck">Bus/Truck</option>
                                    </select>
                                   <div class="form-btn">
                                        <div class="shopping-button">
                                            <button class="submit-btn" name="submit">Book Now</button>
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
    </body>
</html>