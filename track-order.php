<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
    <title>Track Order - Sriji Vastra Shingar Sewa</title>
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/mobile_menu.css">
    <link rel="stylesheet" href="assets/css/nice-select.css">
    <link rel="stylesheet" href="assets/css/scroll_button.css">
    <link rel="stylesheet" href="assets/css/slick.css">
    <link rel="stylesheet" href="assets/css/venobox.min.css">
    <link rel="stylesheet" href="assets/css/select2.min.css">
    <link rel="stylesheet" href="assets/css/jquery.pwstabs.css">
    <link rel="stylesheet" href="assets/css/range_slider.css">
    <link rel="stylesheet" href="assets/css/multiple-image-video.css">
    <link rel="stylesheet" href="assets/css/animated_barfiller.css">
    <link rel="stylesheet" href="assets/css/custom_spacing.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/responsive.css">

    <style>
    .page_banner {
        background-size: cover !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
        aspect-ratio: 38 / 5;
        min-height: 160px;
    }

    @media (max-width: 768px) {
        .page_banner {
            aspect-ratio: unset;
            min-height: 200px;
        }
    }

    /* Ensure banner background keeps its aspect ratio across breakpoints */
    .page_banner {
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    @media (max-width: 768px) {
        .page_banner {
            background-size: cover;
            /* keep ratio while filling smaller screens */
        }
    }
    </style>
</head>

<body class="default_home">

    <!--=========================
        HEADER START
    ==========================-->
    <?php include("includes/header.php") ?>
    <!--=========================
        HEADER END
    ==========================-->


    <!--=========================
        PAGE BANNER START
    ==========================-->
    <section class="page_banner" style="background: url(assets/images/background/ban2.png);">
        <div class="page_banner_overlay">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="page_banner_text wow fadeInUp">
                            <h1>Track Order</h1>
                            <ul>
                                <li><a href="#"><i class="fal fa-home-lg"></i> Home</a></li>
                                <li><a href="#">Track Order</a></li>
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
        TRACK ORDER START
    =============================-->
    <?php
    require_once 'admin/db.php';

    $order = null;
    $error = "";
    $order_id_input = "";
    $email_input = "";

    if (isset($_GET['order_id']) && isset($_GET['email'])) {
        $order_id_input = $_GET['order_id'];
        $email_input = $_GET['email'];

        $sql = "SELECT * FROM orders WHERE order_number = '$order_id_input' AND customer_email = '$email_input'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();
        } else {
            $error = "Order not found with provided details.";
        }
    }
    ?>
    <!--============================
        TRACK ORDER START
    =============================-->
    <section class="track_order mt_100 mb_100">
        <div class="container">
            <div class="row wow fadeInUp">
                <div class="col-xxl-5 col-xl-6 col-md-10 col-lg-8 m-auto">
                    <form class="tack_order_form" method="GET">
                        <h4>order tracking</h4>
                        <p>tracking your order status</p>
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <div class="single_input">
                            <label>order id*</label>
                            <input type="text" name="order_id" placeholder="#Order ID"
                                value="<?php echo htmlspecialchars($order_id_input); ?>" required>
                        </div>
                        <div class="single_input">
                            <label>email address*</label>
                            <input type="email" name="email" placeholder="Email Address"
                                value="<?php echo htmlspecialchars($email_input); ?>" required>
                        </div>
                        <button type="submit" class="common_btn">track order <i
                                class="fas fa-long-arrow-right"></i></button>
                    </form>
                </div>
            </div>

            <?php if ($order): ?>
            <?php
                $status = $order['status'];
                // Status Logic
                $s1 = $s2 = $s3 = $s4 = "";
                $is_cancelled = false;

                if ($status == 'cancelled') {
                    $is_cancelled = true;
                } else {
                    if ($status == 'pending') {
                        $s1 = 'active';
                    } elseif ($status == 'processing') {
                        $s1 = 'active';
                        $s2 = 'active';
                    } elseif ($status == 'shipped') {
                        $s1 = 'active';
                        $s2 = 'active';
                        $s3 = 'active';
                    } elseif ($status == 'delivered') {
                        $s1 = 'active';
                        $s2 = 'active';
                        $s3 = 'active';
                        $s4 = 'active';
                    }
                }
                ?>
            <div class="row justify-content-center mt-5">
                <div class="col-xxl-10 wow fadeInUp">
                    <?php if ($is_cancelled): ?>
                    <div class="alert alert-danger text-center">
                        <h4>This order has been cancelled.</h4>
                    </div>
                    <?php else: ?>
                    <ul class="track_order_map">
                        <li class="<?php echo $s1; ?>">
                            <div class="icon">
                                <img src="assets/images/track_icon_1.png" alt="Track" class="img-fluid">
                            </div>
                            <h4>Order Confirmed</h4>
                        </li>
                        <li class="<?php echo $s2; ?>">
                            <div class="icon">
                                <img src="assets/images/track_icon_2.png" alt="Track" class="img-fluid">
                            </div>
                            <h4>Order Processing</h4>
                        </li>
                        <li class="<?php echo $s3; ?>">
                            <div class="icon">
                                <img src="assets/images/track_icon_3.png" alt="Track" class="img-fluid">
                            </div>
                            <h4>On the way</h4>
                        </li>
                        <li class="<?php echo $s4; ?>">
                            <div class="icon">
                                <img src="assets/images/track_icon_4.png" alt="Track" class="img-fluid">
                            </div>
                            <h4>Delivered</h4>
                        </li>
                    </ul>
                    <?php endif; ?>
                </div>
                <div class="col-xxl-10">
                    <div class="tracking_product_area">
                        <div class="row">
                            <div class="col-lg-6 wow fadeInLeft">
                                <div class="tracking_product_info">
                                    <div class="icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                        </svg>
                                    </div>
                                    <h3>Order Status: <?php echo ucfirst($status); ?></h3>
                                    <p>Detailed info about your order.</p>
                                    <ul>
                                        <li>
                                            <h6>Date:</h6>
                                            <h5><?php echo date('M d, Y', strtotime($order['created_at'])); ?></h5>
                                        </li>
                                        <li>
                                            <h6>Customer:</h6>
                                            <h5><?php echo htmlspecialchars($order['customer_name']); ?></h5>
                                        </li>
                                        <li>
                                            <h6>Status:</h6>
                                            <h5><?php echo ucfirst($status); ?></h5>
                                        </li>
                                        <li>
                                            <h6>Order ID:</h6>
                                            <h5>#<?php echo htmlspecialchars($order['order_number']); ?></h5>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-6 wow fadeInRight">
                                <div class="tracking_product_list">
                                    <h3>Order Details</h3>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>product</th>
                                                    <th>total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    $oid = $order['id'];
                                                    $items_sql = "SELECT oi.*, p.name as product_name, s.size as variant_size, pv.color as variant_color
                                                              FROM order_items oi 
                                                              LEFT JOIN products p ON oi.product_id = p.id 
                                                              LEFT JOIN product_variants pv ON oi.variant_id = pv.id
                                                              LEFT JOIN product_sizes s ON pv.product_size_id = s.id 
                                                              WHERE oi.order_id = $oid";
                                                    $items_res = $conn->query($items_sql);
                                                    if ($items_res && $items_res->num_rows > 0) {
                                                        while ($item = $items_res->fetch_assoc()) {
                                                            $variant_str = "";
                                                            if (!empty($item['variant_size'])) {
                                                                $variant_str .= $item['variant_size'];
                                                            }
                                                            if (!empty($item['variant_color'])) {
                                                                $variant_str .= ($variant_str ? ", " : "") . $item['variant_color'];
                                                            }
                                                            ?>
                                                <tr>
                                                    <td>
                                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                                        <?php if ($variant_str): ?>
                                                        (<?php echo htmlspecialchars($variant_str); ?>)
                                                        <?php endif; ?>
                                                        <span>× <?php echo $item['quantity']; ?></span>
                                                    </td>
                                                    <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                                    </td>
                                                </tr>
                                                <?php
                                                        }
                                                    }
                                                    ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th>Total</th>
                                                    <th>₹<?php echo number_format($order['total_amount'], 2); ?></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </section>
    <!--============================
        TRACK ORDER END
    =============================-->


    <!--=========================
        FOOTER 2 START
    ==========================-->
    <?php include("includes/footer.php") ?>
    <!--=========================
        FOOTER 2 END
    ==========================-->
    <a href="https://wa.me/919115936593" target="_blank"
        class="position-fixed d-flex align-items-center justify-content-center" style="
      bottom:25px;
      right:25px;
      width:65px;
      padding:10px;
      height:65px;
      background-color:#25D366;
      border-radius:50%;
      box-shadow:0 8px 20px rgba(0,0,0,0.2);
      z-index:999;
      transition:all 0.3s ease;
   " onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" width="30" alt="WhatsApp">
    </a>

    <!--==========================
        SCROLL BUTTON START
    ===========================-->
    <!-- <div class="progress-wrap">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </div> -->
    <!--==========================
        SCROLL BUTTON END
    ===========================-->


    <!--jquery library js-->
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <!--bootstrap js-->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <!--font-awesome js-->
    <script src="assets/js/Font-Awesome.js"></script>
    <!--counter js-->
    <script src="assets/js/jquery.waypoints.min.js"></script>
    <script src="assets/js/jquery.countup.min.js"></script>
    <!--nice select js-->
    <script src="assets/js/jquery.nice-select.min.js"></script>
    <!--select 2 js-->
    <script src="assets/js/select2.min.js"></script>
    <!--simply countdown js-->
    <script src="assets/js/simplyCountdown.js"></script>
    <!--slick slider js-->
    <script src="assets/js/slick.min.js"></script>
    <!--venobox js-->
    <script src="assets/js/venobox.min.js"></script>
    <!--wow js-->
    <script src="assets/js/wow.min.js"></script>
    <!--marquee js-->
    <script src="assets/js/jquery.marquee.min.js"></script>
    <!--pws tabs js-->
    <script src="assets/js/jquery.pwstabs.min.js"></script>
    <!--scroll button js-->
    <script src="assets/js/scroll_button.js"></script>
    <!--youtube background js-->
    <script src="assets/js/jquery.youtube-background.min.js"></script>
    <!--range slider js-->
    <script src="assets/js/range_slider.js"></script>
    <!--sticky sidebar js-->
    <script src="assets/js/sticky_sidebar.js"></script>
    <!--multiple image upload js-->
    <script src="assets/js/multiple-image-video.js"></script>
    <!--animated barfiller js-->
    <script src="assets/js/animated_barfiller.js"></script>
    <!--main/custom js-->
    <script src="assets/js/custom.js"></script>

</body>

</html>