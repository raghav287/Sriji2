<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
    <title>Dashboard</title>
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
                                <li><a href="#">Overview</a></li>
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

    // Stats
    $total_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE user_id = $user_id")->fetch_row()[0];
    $completed_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE user_id = $user_id AND status = 'completed'")->fetch_row()[0];
    $pending_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE user_id = $user_id AND (status = 'pending' OR status = 'processing')")->fetch_row()[0];
    $canceled_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE user_id = $user_id AND status = 'canceled'")->fetch_row()[0];

    // Recent Orders
    $recent_sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
    $recent_res = $conn->query($recent_sql);
    ?>
    <section class="dashboard mb_100">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 wow fadeInUp">
                    <?php include("includes/dashboard-sidebar.php") ?>
                </div>
                <div class="col-lg-9">
                    <div class="dashboard_content mt_100">
                        <div class="row">
                            <div class="col-xl-4 col-md-6 wow fadeInUp">
                                <div class="dashboard_overview_item">
                                    <div class="icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                        </svg>
                                    </div>
                                    <h3> <?php echo $total_orders; ?> <span>Total Order</span></h3>
                                </div>
                            </div>
                            <div class="col-xl-4 col-md-6 wow fadeInUp">
                                <div class="dashboard_overview_item blue">
                                    <div class="icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                        </svg>
                                    </div>
                                    <h3> <?php echo $completed_orders; ?> <span>Completed Order</span></h3>
                                </div>
                            </div>
                            <div class="col-xl-4 col-md-6 wow fadeInUp">
                                <div class="dashboard_overview_item orange">
                                    <div class="icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>
                                    </div>
                                    <h3> <?php echo $pending_orders; ?> <span>Pending Order</span></h3>
                                </div>
                            </div>
                            <!-- Removed Wishlist and Canceled (redundant or not priority, keeping canceled) -->
                            <div class="col-xl-4 col-md-6 wow fadeInUp">
                                <div class="dashboard_overview_item red">
                                    <div class="icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                    <h3> <?php echo $canceled_orders; ?> <span>Canceled Order</span></h3>
                                </div>
                            </div>
                        </div>
                        <div class="row mt_25">
                            <div class="col-xl-12 wow fadeInLeft">
                                <div class="dashboard_recent_order">
                                    <h3>Your Recent Orders</h3>
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
                                                    <?php while ($row = $recent_res->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?php echo $row['order_number']; ?></td>
                                                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                                            </td>
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
                                                                    class="view_btn">
                                                                    View
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
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
        </div>
    </section>
    <!--============================
        DSHBOARD END
    =============================-->


    <!--=========================
        FOOTER 2 START
    ==========================-->
    <?php include '../includes/footer.php' ?>
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