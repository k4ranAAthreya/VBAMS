<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/dbconnection.php');
?>
<!DOCTYPE html>
<html class="no-js" lang="zxx">
    <head>
        <!-- Basic page needs
        ============================================ -->
        
        <title>VBAMS || Home Page </title>
        
        <!-- ============== All CSS ================ -->
        <!-- normalize css
        ============================================ -->
        <link rel="stylesheet" href="css1/normalize.css">

        <!-- animate css
        ============================================ -->
        <link rel="stylesheet" href="css1/animate.css">

        <!-- bootstrap css
        ============================================ -->
        <link rel="stylesheet" href="css1/bootstrap.min.css">

        <!-- meanmenu css
        ============================================ -->
        <link rel="stylesheet" href="css1/meanmenu.min.css">

        <!-- font-awesome css
        ============================================ -->
        <link rel="stylesheet" href="css1/font-awesome.min.css">

        <!-- icofont css
        ============================================ -->
        <link rel="stylesheet" href="css1/icofont.css">

        <!-- change-text css
        ============================================ -->
        <link rel="stylesheet" href="css1/change-text.css">

        <!-- YTPlayer css
        ============================================ -->
        <link rel="stylesheet" href="css1/jquery.mb.YTPlayer.min.css">

        <!-- main css
        ============================================ -->
        <link rel="stylesheet" href="css1/main.css">

        <!-- owl.carousel css
        ============================================ -->
        <link rel="stylesheet" href="css1/owl.carousel.css">
        <link rel="stylesheet" href="css1/owl.theme.css">
        <link rel="stylesheet" href="css1/owl.transitions.css">

        <!-- nivo-slider css
        ============================================ -->
        <link rel="stylesheet" href="lib/css/nivo-slider.css">
        <link rel="stylesheet" href="lib/css/preview.css">

        <!-- style css
        ============================================ -->
        <link rel="stylesheet" href="style.css">

        <!-- responsive css
        ============================================ -->
        <link rel="stylesheet" href="css1/responsive.css">

        <!-- modernizr js
        ============================================ -->
        <script src="js/vendor/modernizr-2.8.3.min.js"></script>
    </head>
    <body>
        <!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

       <?php include_once('includes/header.php');?>
        <!-- slider area start -->
        <div class="slider-area">
            <div class="bend niceties preview-1">
                <!-- slider images start -->
                <div id="nivoslider" class="slides">
                    <img src="img1/slider/img5.jpg" alt="slider_1" title="#slider-direction-1"/>
                    <img src="img1/slider/img9.jpg" alt="slider_2" title="#slider-direction-2"/>
                </div>
                <!-- slider images end -->
                <!-- slider 1 direction -->
                <div id="slider-direction-1" class="t-cn slider-direction">
                    <!-- slider progress start -->
                    <div class="slider-progress"></div>
                    <!-- slider progress end -->
                    <!-- slider caption start -->
                    <div class="slider-caption">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-9">
                                    <!-- layer 1 -->
                                    <div class="layer-1-1">
                                        <h2 class="title-1">Best Vehicle Breakdown  </h2>
                                    </div>
                                    <!-- layer 2 -->
                                    <div class="layer-1-2">
                                        <h2 class="title-2"> Assistance Management System </h2>
                                    </div>
                                    <!-- layer 3 -->
                                    <div class="layer-1-3">
                                        <p class="title-3"></p>
                                    </div>
                                    <!-- layer 4 -->
                                    <div class="layer-1-4">
                                        <a href="track-service.php" class="title-4">track your service </a>
                                        <a href="contact.php" class="title-4">contact us </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- slider caption end -->
                </div>
                <!-- slider 2 direction -->
                <div id="slider-direction-2" class="t-cn slider-direction">
                    <!-- slider progress start -->
                    <div class="slider-progress"></div>
                    <!-- slider progress end -->
                    <!-- slider caption start -->
                    <div class="slider-caption">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-9">
                                    <!-- layer 1 -->
                                  <div class="layer-1-1">
                                        <h2 class="title-1">Best Vehicle Breakdown  </h2>
                                    </div>
                                    <!-- layer 2 -->
                                    <div class="layer-1-2">
                                        <h2 class="title-2"> Assistance Management System </h2>
                                    </div>
                                    <!-- layer 3 -->
                                    <div class="layer-2-3">
                                        <p class="title-3"></p>
                                    </div>
                                    <!-- layer 4 -->
                                    <div class="layer-2-4">
                                        <a href="track-service.php" class="title-4">track your service </a>
                                        <a href="contact.php" class="title-4">contact us </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- slider caption end -->
                </div>
            </div>
        
        </div>
        <!-- slider area end -->
        <!-- about us area start -->
        <div class="about-us-area section-padding">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <!-- section title start -->
                        <div class="section-heading">
                            <h2>About <span>Us</span></h2>
                        </div>
                        <!-- section title end -->
                        <!-- about content start -->
                        <div class="about-us-info">
                             <?php
$sql="SELECT * from tblpage where PageType='aboutus'";
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);

$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $row)
{               ?>
                            <p><?php  echo htmlentities($row->PageDescription);?></p>
                            
                            <?php $cnt=$cnt+1;}} ?>
                        </div>
                        <!-- about content end -->
                    </div>
                    <div class="col-md-6 hidden-xs">
                        <!-- about us img start -->
                        <div class="about-us-img">
                            <img src="img1/about/tow-truck-federal-way-2.jpg" alt="">
                        </div>
                        <!-- about us img end -->
                    </div>
                </div>
            </div>
        </div>
        <!-- about us area end -->

        <!-- Customer Testimonials Area Start -->
        <div class="testimonials-area section-padding" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <!-- Section Title -->
                        <div class="section-heading" style="text-align: center; margin-bottom: 50px;">
                            <h2 style="color: white; font-size: 32px; margin-bottom: 15px;">
                                <i class="fa fa-star"></i> Customer Reviews
                            </h2>
                            <p style="color: rgba(255,255,255,0.9); font-size: 16px;">
                                See what our customers say about our service
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Testimonials Grid -->
                <div class="row">
                    <?php
                        // Fetch customer ratings from database
                        try {
                            $sql = "SELECT sr.driver_rating, sr.service_rating, sr.feedback, sr.submitted_at,
                                           tb.Name as customer_name
                                    FROM service_ratings sr
                                    JOIN tblbook tb ON sr.booking_id = tb.ID
                                    ORDER BY sr.submitted_at DESC
                                    LIMIT 6";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $testimonials = $query->fetchAll(PDO::FETCH_OBJ);

                            if (count($testimonials) > 0) {
                                foreach ($testimonials as $testimonial) {
                                    $avg_rating = round(($testimonial->driver_rating + $testimonial->service_rating) / 2);
                                    $date = date('M d, Y', strtotime($testimonial->submitted_at));
                                    $feedback = substr($testimonial->feedback, 0, 100);
                                    if (strlen($testimonial->feedback) > 100) {
                                        $feedback .= '...';
                                    }
                        ?>
                                    <div class="col-md-4 col-sm-6 testimonial-item" style="margin-bottom: 30px;">
                                        <div class="testimonial-card" style="
                                            background: white;
                                            padding: 25px;
                                            border-radius: 10px;
                                            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                                            transition: all 0.3s ease;
                                            min-height: 280px;
                                            display: flex;
                                            flex-direction: column;
                                            cursor: pointer;
                                        " onmouseover="this.style.boxShadow='0 10px 40px rgba(0,0,0,0.2)'; this.style.transform='translateY(-5px)';"
                                           onmouseout="this.style.boxShadow='0 5px 20px rgba(0,0,0,0.1)'; this.style.transform='translateY(0)';">

                                            <!-- Star Rating -->
                                            <div style="margin-bottom: 15px;">
                                                <div style="font-size: 18px; margin-bottom: 8px;">
                                                    <?php
                                                        for ($i = 0; $i < $avg_rating; $i++) {
                                                            echo '<i class="fa fa-star" style="color: #ffc107; margin-right: 3px;"></i>';
                                                        }
                                                        for ($i = $avg_rating; $i < 5; $i++) {
                                                            echo '<i class="fa fa-star-o" style="color: #ddd; margin-right: 3px;"></i>';
                                                        }
                                                    ?>
                                                </div>
                                                <small style="color: #999;">
                                                    Driver: <?php echo $testimonial->driver_rating; ?>/5 |
                                                    Service: <?php echo $testimonial->service_rating; ?>/5
                                                </small>
                                            </div>

                                            <!-- Feedback Text -->
                                            <p style="
                                                color: #333;
                                                margin-bottom: 15px;
                                                flex-grow: 1;
                                                line-height: 1.6;
                                                font-style: italic;
                                                font-size: 14px;
                                            ">
                                                "<?php echo htmlspecialchars($feedback); ?>"
                                            </p>

                                            <!-- Customer Info -->
                                            <div style="border-top: 1px solid #eee; padding-top: 15px;">
                                                <div style="font-weight: 600; color: #333; margin-bottom: 5px;">
                                                    <?php echo htmlspecialchars($testimonial->customer_name); ?>
                                                </div>
                                                <small style="color: #999;">
                                                    <i class="fa fa-calendar"></i> <?php echo $date; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                        <?php
                                }
                            } else {
                                // Show demo testimonials if no real ones exist
                        ?>
                                    <!-- Demo Testimonial 1 -->
                                    <div class="col-md-4 col-sm-6 testimonial-item" style="margin-bottom: 30px;">
                                        <div class="testimonial-card" style="
                                            background: white;
                                            padding: 25px;
                                            border-radius: 10px;
                                            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                                            transition: all 0.3s ease;
                                            min-height: 280px;
                                            display: flex;
                                            flex-direction: column;
                                            cursor: pointer;
                                            opacity: 0.8;
                                        " onmouseover="this.style.boxShadow='0 10px 40px rgba(0,0,0,0.2)'; this.style.transform='translateY(-5px)';"
                                           onmouseout="this.style.boxShadow='0 5px 20px rgba(0,0,0,0.1)'; this.style.transform='translateY(0)';">

                                            <div style="margin-bottom: 15px;">
                                                <div style="font-size: 18px; margin-bottom: 8px;">
                                                    <i class="fa fa-star" style="color: #ffc107; margin-right: 3px;"></i>
                                                    <i class="fa fa-star" style="color: #ffc107; margin-right: 3px;"></i>
                                                    <i class="fa fa-star" style="color: #ffc107; margin-right: 3px;"></i>
                                                    <i class="fa fa-star" style="color: #ffc107; margin-right: 3px;"></i>
                                                    <i class="fa fa-star" style="color: #ffc107; margin-right: 3px;"></i>
                                                </div>
                                                <small style="color: #999;">Driver: 5/5 | Service: 5/5</small>
                                            </div>

                                            <p style="
                                                color: #333;
                                                margin-bottom: 15px;
                                                flex-grow: 1;
                                                line-height: 1.6;
                                                font-style: italic;
                                                font-size: 14px;
                                            ">
                                                "Excellent service! The driver was very professional and arrived on time. Highly recommended!"
                                            </p>

                                            <div style="border-top: 1px solid #eee; padding-top: 15px;">
                                                <div style="font-weight: 600; color: #333; margin-bottom: 5px;">
                                                    Rajesh Kumar
                                                </div>
                                                <small style="color: #999;">
                                                    <i class="fa fa-calendar"></i> Demo Review
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Demo Testimonial 2 -->
                                    <div class="col-md-4 col-sm-6 testimonial-item" style="margin-bottom: 30px;">
                                        <div class="testimonial-card" style="
                                            background: white;
                                            padding: 25px;
                                            border-radius: 10px;
                                            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                                            transition: all 0.3s ease;
                                            min-height: 280px;
                                            display: flex;
                                            flex-direction: column;
                                            cursor: pointer;
                                            opacity: 0.8;
                                        " onmouseover="this.style.boxShadow='0 10px 40px rgba(0,0,0,0.2)'; this.style.transform='translateY(-5px)';"
                                           onmouseout="this.style.boxShadow='0 5px 20px rgba(0,0,0,0.1)'; this.style.transform='translateY(0)';">

                                            <div style="margin-bottom: 15px;">
                                                <div style="font-size: 18px; margin-bottom: 8px;">
                                                    <i class="fa fa-star" style="color: #ffc107; margin-right: 3px;"></i>
                                                    <i class="fa fa-star" style="color: #ffc107; margin-right: 3px;"></i>
                                                    <i class="fa fa-star" style="color: #ffc107; margin-right: 3px;"></i>
                                                    <i class="fa fa-star" style="color: #ffc107; margin-right: 3px;"></i>
                                                    <i class="fa fa-star-o" style="color: #ddd; margin-right: 3px;"></i>
                                                </div>
                                                <small style="color: #999;">Driver: 4/5 | Service: 4/5</small>
                                            </div>

                                            <p style="
                                                color: #333;
                                                margin-bottom: 15px;
                                                flex-grow: 1;
                                                line-height: 1.6;
                                                font-style: italic;
                                                font-size: 14px;
                                            ">
                                                "Great breakdown assistance! Quick response time and very helpful. Would definitely use again."
                                            </p>

                                            <div style="border-top: 1px solid #eee; padding-top: 15px;">
                                                <div style="font-weight: 600; color: #333; margin-bottom: 5px;">
                                                    Priya Singh
                                                </div>
                                                <small style="color: #999;">
                                                    <i class="fa fa-calendar"></i> Demo Review
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Demo Testimonial 3 -->
                                    <div class="col-md-4 col-sm-6 testimonial-item" style="margin-bottom: 30px;">
                                        <div class="testimonial-card" style="
                                            background: white;
                                            padding: 25px;
                                            border-radius: 10px;
                                            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                                            transition: all 0.3s ease;
                                            min-height: 280px;
                                            display: flex;
                                            flex-direction: column;
                                            cursor: pointer;
                                            opacity: 0.8;
                                        " onmouseover="this.style.boxShadow='0 10px 40px rgba(0,0,0,0.2)'; this.style.transform='translateY(-5px)';"
                                           onmouseout="this.style.boxShadow='0 5px 20px rgba(0,0,0,0.1)'; this.style.transform='translateY(0)';">

                                            <div style="margin-bottom: 15px;">
                                                <div style="font-size: 18px; margin-bottom: 8px;">
                                                    <i class="fa fa-star" style="color: #ffc107; margin-right: 3px;"></i>
                                                    <i class="fa fa-star" style="color: #ffc107; margin-right: 3px;"></i>
                                                    <i class="fa fa-star" style="color: #ffc107; margin-right: 3px;"></i>
                                                    <i class="fa fa-star" style="color: #ffc107; margin-right: 3px;"></i>
                                                    <i class="fa fa-star" style="color: #ffc107; margin-right: 3px;"></i>
                                                </div>
                                                <small style="color: #999;">Driver: 5/5 | Service: 5/5</small>
                                            </div>

                                            <p style="
                                                color: #333;
                                                margin-bottom: 15px;
                                                flex-grow: 1;
                                                line-height: 1.6;
                                                font-style: italic;
                                                font-size: 14px;
                                            ">
                                                "Best service in town! Professional team, affordable prices, and excellent customer support. 5 stars!"
                                            </p>

                                            <div style="border-top: 1px solid #eee; padding-top: 15px;">
                                                <div style="font-weight: 600; color: #333; margin-bottom: 5px;">
                                                    Amit Patel
                                                </div>
                                                <small style="color: #999;">
                                                    <i class="fa fa-calendar"></i> Demo Review
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                        <?php
                            }
                        } catch (Exception $e) {
                            // Table doesn't exist yet - show demo
                        ?>
                                    <div class="col-md-12" style="text-align: center; padding: 50px 0;">
                                        <div style="
                                            background: rgba(255,255,255,0.1);
                                            padding: 30px;
                                            border-radius: 10px;
                                            backdrop-filter: blur(10px);
                                        ">
                                            <i class="fa fa-star" style="font-size: 40px; margin-bottom: 15px; display: block;"></i>
                                            <p style="font-size: 16px; margin: 0;">Customer reviews coming soon!</p>
                                        </div>
                                    </div>
                        <?php
                        }
                        ?>
                </div>

                <!-- View More Button -->
                <div style="text-align: center; margin-top: 40px;">
                    <a href="track-service.php" style="
                        display: inline-block;
                        padding: 12px 35px;
                        background: white;
                        color: #667eea;
                        border-radius: 5px;
                        text-decoration: none;
                        font-weight: 600;
                        transition: all 0.3s;
                        font-size: 14px;
                    " onmouseover="this.style.boxShadow='0 5px 20px rgba(0,0,0,0.2)'; this.style.transform='translateY(-2px)';"
                       onmouseout="this.style.boxShadow='none'; this.style.transform='translateY(0)';">
                        <i class="fa fa-star"></i> Submit Your Rating
                    </a>
                </div>
            </div>
        </div>
        <!-- Customer Testimonials Area End -->


        <!-- blog area end -->
        <!-- quick book area start -->

        <!-- quick book area end -->
        <?php include_once('includes/footer.php');?>

        <!-- ============== All JS ================ -->
        <!-- jquery js
        =========================================== -->
        <script src="js/vendor/jquery-1.12.0.min.js"></script>

        <!-- bootstrap js
        =========================================== -->
        <script src="js/bootstrap.min.js"></script>

        <!-- meanmenu js
        =========================================== -->
        <script src="js/jquery.meanmenu.js"></script>

        <!-- scrollUp js
        =========================================== -->
        <script src="js/jquery.scrollUp.min.js"></script>

        <!-- wow js
        =========================================== -->
        <script src="js/wow.min.js"></script>

        <!-- owl.carousel js
        =========================================== -->
        <script src="js/owl.carousel.min.js"></script>

        <!-- change-text js
        =========================================== -->
        <script src="js/change-text.js"></script>

        <!-- YTPlayer js
        =========================================== -->
        <script src="js/jquery.mb.YTPlayer.min.js"></script>

        <!-- textillate js
        =========================================== -->
        <script src="js/jquery.lettering.js"></script>
        <script src="js/jquery.textillate.js"></script>

        <!-- nivo.slider js
        =========================================== -->
        <script src="lib/js/jquery.nivo.slider.js"></script>
        <script src="lib/home.js"></script>

        <!-- plugins js
        =========================================== -->
        <script src="js/plugins.js"></script>

        <!-- main js
        =========================================== -->
        <script src="js/main.js"></script>
    </body>
</html>
