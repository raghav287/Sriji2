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

if (!isset($_GET['id'])) {
    header("Location: orders.php"); // Redirect to orders list
    exit;
}

$order_id = intval($_GET['id']);

// Fetch Order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order_res = $stmt->get_result();

if ($order_res->num_rows == 0) {
    echo "Order not found or access denied.";
    exit;
}

$order = $order_res->fetch_assoc();

// Fetch Items
$item_sql = "SELECT oi.*, p.name as product_name, 
            (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image_path,
            s.size, v.color
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            LEFT JOIN product_variants v ON oi.variant_id = v.id
            LEFT JOIN product_sizes s ON v.product_size_id = s.id
            WHERE oi.order_id = ?";
$item_stmt = $conn->prepare($item_sql);
$item_stmt->bind_param("i", $order_id);
$item_stmt->execute();
$items_res = $item_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
    <title>Order Details - Sriji Vastra Shingar Sewa</title>
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
    <?php include '../includes/header.php' ?>
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
                            <h1>Order Details</h1>
                            <ul>
                                <li><a href="../index.php"><i class="fal fa-home-lg"></i> Home</a></li>
                                <li><a href="orders.php">Orders</a></li>
                                <li><a href="#">Details</a></li>
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
    <section class="dashboard mb_100">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 wow fadeInUp">
                    <?php include "includes/dashboard-sidebar.php"; ?>
                </div>
                <div class="col-lg-9 wow fadeInRight">
                    <div class="dashboard_content mt_100">
                        <h3 class="dashboard_title">Order Details <a class="back_btn common_btn" href="orders.php">Go
                                Back</a></h3>
                        <div class="dashboard_order_invoice_area">
                            <div class="dashboard_order_invoice">
                                <div class="dashboard_invoice_logo_area">
                                    <div class="invoice_logo">
                                        <img src="../assets/images/logo/logo.png" alt="logo" class="img-fluid w-100">
                                    </div>
                                    <div class="text">
                                        <h2>invoice</h2>
                                        <p>invoice no: <?php echo htmlspecialchars($order['order_number']); ?></p>
                                        <p>date: <?php echo date('d-m-Y', strtotime($order['created_at'])); ?></p>
                                        <p>Status: <b
                                                class="text-uppercase"><?php echo htmlspecialchars($order['status']); ?></b>
                                        </p>
                                    </div>
                                </div>
                                <div class="dashboard_invoice_header">
                                    <div class="text">
                                        <h2>Bill/Ship To</h2>
                                        <p><b>Name:</b> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                        <p><b>Email:</b> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                                        <p><b>Phone:</b> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                                        <p><b>Address:</b> <?php echo htmlspecialchars($order['shipping_address']); ?>
                                        </p>
                                    </div>
                                    <div class="text">
                                        <h2>Payment Info</h2>
                                        <p><b>Method:</b> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                                        <p><b>Status:</b> <?php echo htmlspecialchars($order['payment_status']); ?></p>
                                    </div>
                                </div>
                                <div class="invoice_table">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Price</th>
                                                    <th>Quantity</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $subtotal = 0;
                                                while ($item = $items_res->fetch_assoc()):
                                                    $item_subtotal = $item['price'] * $item['quantity'];
                                                    $subtotal += $item_subtotal;
                                                    $img = !empty($item['image_path']) ? '../assets/uploads/products/' . $item['image_path'] : '../assets/images/no_image.png';
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <img src="<?php echo $img; ?>" alt="img"
                                                                    style="width:50px; height:50px; object-fit:cover; margin-right:10px;">
                                                                <div>
                                                                    <p class="mb-0">
                                                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                                                    </p>
                                                                    <?php if ($item['size'] || $item['color']): ?>
                                                                        <small class="text-muted">
                                                                            <?php echo $item['color'] . ($item['color'] && $item['size'] ? ', ' : '') . $item['size']; ?>
                                                                        </small>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                                        <td><?php echo $item['quantity']; ?></td>
                                                        <td>₹<?php echo number_format($item_subtotal, 2); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                                <tr>
                                                    <td colspan="3" class="text-end"><b>Subtotal</b></td>
                                                    <td><b>₹<?php echo number_format($subtotal, 2); ?></b></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-end">Delivery Charge</td>
                                                    <td>₹0.00</td> <!-- Assuming free for now as per checkout -->
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-end"><b>Total</b></td>
                                                    <td><b>₹<?php echo number_format($order['total_amount'], 2); ?></b>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="dashboard_invoice_footer">
                                    <h4>Notes</h4>
                                    <p>Thanks for your purchase</p>
                                    <!-- <a class="common_btn" href="javascript:window.print()">Print PDF</a> -->
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