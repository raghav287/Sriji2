<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
    <title>Orders - Sriji Vastra Shingar Sewa</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <link rel="stylesheet" href="../assets/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/css/mobile_menu.css">
    <link rel="stylesheet" href="../assets/css/nice-select.css">
    <link rel="stylesheet" href="../assets/css/scroll_button.css">
    <link rel="stylesheet" href="../assets/css/slick.css">
    <link rel="stylesheet" href="../assets/css/venobox.min.css">
    <link rel="stylesheet" href="../assets/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/jquery.pwstabs.css">
    <link rel="stylesheet" href="../assets/css/range_slider.css">
    <link rel="stylesheet" href="../assets/css/multiple-image-video.css">
    <link rel="stylesheet" href="../assets/css/animated_barfiller.css">
    <link rel="stylesheet" href="../assets/css/custom_spacing.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>

<body class="default_home">

    <!--=========================
        HEADER START
    ==========================-->
    <?php include("../includes/header.php") ?>
    <!--=========================
        HEADER END
    ==========================-->


    <!--=========================
        PAGE BANNER START
    ==========================-->
    <section class="page_banner" style="background: url(../assets/images/background/breadcrumb-bg.jpg);">
        <div class="page_banner_overlay">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="page_banner_text wow fadeInUp">
                            <h1>My Account</h1>
                            <ul>
                                <li><a href="#"><i class="fal fa-home-lg"></i> Home</a></li>
                                <li><a href="#">Order</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--=========================
        PAGE BANNER START
    ==========================-->


    <!--============================
        DSHBOARD START
    =============================-->
    <!--============================
        DSHBOARD START
    =============================-->
    <?php
    require_once '../admin/db.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../sign-in.php");
        exit;
    }
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
    $res = $conn->query($sql);
    ?>
    <section class="dashboard mb_100">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 wow fadeInUp">
                    <?php include("includes/dashboard-sidebar.php") ?>
                </div>
                <div class="col-lg-9 wow fadeInRight">
                    <div class="dashboard_content mt_100">
                        <h3 class="dashboard_title">Order History</h3>
                        <div class="dashboard_order_table">
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($res->num_rows > 0): ?>
                                            <?php while ($row = $res->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $row['order_number']; ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                                    <td><span class="<?php
                                                    if ($row['status'] == 'completed')
                                                        echo 'complete';
                                                    elseif ($row['status'] == 'pending' || $row['status'] == 'processing')
                                                        echo 'active';
                                                    else
                                                        echo 'cancel';
                                                    ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                                    <td>₹<?php echo number_format($row['total_amount'], 2); ?></td>
                                                    <td>
                                                        <a href="order-details.php?id=<?php echo $row['id']; ?>"
                                                            class="view_btn">View</a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No orders found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--============================
        DSHBOARD END
    =============================-->


    <!--=========================
        FOOTER 2 START
    ==========================-->
    <?php include("../includes/footer.php") ?>
    <!--=========================
        FOOTER 2 END
    ==========================-->


    <!--==========================
        SCROLL BUTTON START
    ===========================-->
    <div class="progress-wrap">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </div>
    <!--==========================
        SCROLL BUTTON END
    ===========================-->


    <!--jquery library js-->
    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <!--bootstrap js-->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <!--font-awesome js-->
    <script src="../assets/js/Font-Awesome.js"></script>
    <!--counter js-->
    <script src="../assets/js/jquery.waypoints.min.js"></script>
    <script src="../assets/js/jquery.countup.min.js"></script>
    <!--nice select js-->
    <script src="../assets/js/jquery.nice-select.min.js"></script>
    <!--select 2 js-->
    <script src="../assets/js/select2.min.js"></script>
    <!--simply countdown js-->
    <script src="../assets/js/simplyCountdown.js"></script>
    <!--slick slider js-->
    <script src="../assets/js/slick.min.js"></script>
    <!--venobox js-->
    <script src="../assets/js/venobox.min.js"></script>
    <!--wow js-->
    <script src="../assets/js/wow.min.js"></script>
    <!--marquee js-->
    <script src="../assets/js/jquery.marquee.min.js"></script>
    <!--pws tabs js-->
    <script src="../assets/js/jquery.pwstabs.min.js"></script>
    <!--scroll button js-->
    <script src="../assets/js/scroll_button.js"></script>
    <!--youtube background js-->
    <script src="../assets/js/jquery.youtube-background.min.js"></script>
    <!--range slider js-->
    <script src="../assets/js/range_slider.js"></script>
    <!--sticky sidebar js-->
    <script src="../assets/js/sticky_sidebar.js"></script>
    <!--multiple image upload js-->
    <script src="../assets/js/multiple-image-video.js"></script>
    <!--animated barfiller js-->
    <script src="../assets/js/animated_barfiller.js"></script>
    <!--main/custom js-->
    <script src="../assets/js/custom.js"></script>

</body>

</html>