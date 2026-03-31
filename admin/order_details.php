<?php
require_once 'check_session.php';
require_once 'db.php';

if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$id = $_GET['id'];
$sql = "SELECT * FROM orders WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: orders.php");
    exit();
}

$order = $result->fetch_assoc();
$page_title = "Order Details #" . $order['order_number'];
?>
<!doctype html>
<html lang="en" dir="ltr">

<head>
    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sash – Bootstrap 5  Admin & Dashboard Template">
    <meta name="author" content="Spruko Technologies Private Limited">
    <meta name="keywords"
        content="admin,admin dashboard,admin panel,admin template,bootstrap,clean,dashboard,flat,jquery,modern,responsive,premium admin templates,responsive admin,ui,ui kit.">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/brand/favicon.ico">

    <!-- TITLE -->
    <title><?php echo $page_title; ?> - GSA Industries Admin</title>

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- STYLE CSS -->
    <link href="assets/css/style.css" rel="stylesheet">

    <!-- Plugins CSS -->
    <link href="assets/css/plugins.css" rel="stylesheet">

    <!--- FONT-ICONS CSS -->
    <link href="assets/css/icons.css" rel="stylesheet">

    <!-- INTERNAL Switcher css -->
    <link href="assets/switcher/css/switcher.css" rel="stylesheet">
    <link href="assets/switcher/demo.css" rel="stylesheet">

</head>

<body class="app sidebar-mini ltr light-mode">


    <!-- GLOBAL-LOADER -->
    <div id="global-loader">
        <img src="assets/images/loader.svg" class="loader-img" alt="Loader">
    </div>
    <!-- /GLOBAL-LOADER -->

    <!-- PAGE -->
    <div class="page">
        <div class="page-main">

            <!-- app-Header -->
            <?php include 'includes/header.php'; ?>
            <!-- /app-Header -->

            <!--APP-SIDEBAR-->
            <?php include 'includes/sidebar.php'; ?>
            <!--/APP-SIDEBAR-->

            <!--app-content open-->
            <div class="main-content app-content mt-0">
                <div class="side-app">

                    <!-- CONTAINER -->
                    <div class="main-container container-fluid">

                        <!-- PAGE-HEADER -->
                        <div class="page-header">
                            <h1 class="page-title">Order Details:
                                #<?php echo htmlspecialchars($order['order_number']); ?></h1>
                            <div>
                                <a href="orders.php" class="btn btn-secondary">
                                    <i class="fe fe-arrow-left me-2"></i>Back to List
                                </a>
                            </div>
                        </div>
                        <!-- PAGE-HEADER END -->

                        <!-- ROW -->
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Order Items</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered border-bottom">
                                                <thead>
                                                    <tr>
                                                        <th>Image</th>
                                                        <th>Product</th>
                                                        <th>Variant</th>
                                                        <th>Price</th>
                                                        <th>Quantity</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $item_sql = "SELECT oi.*, p.name as product_name, 
                                                                 s.size as variant_size, pv.color as variant_color,
                                                                 (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as product_image 
                                                                 FROM order_items oi 
                                                                 LEFT JOIN products p ON oi.product_id = p.id 
                                                                 LEFT JOIN product_variants pv ON oi.variant_id = pv.id 
                                                                 LEFT JOIN product_sizes s ON pv.product_size_id = s.id
                                                                 WHERE oi.order_id = $id";
                                                    $item_result = $conn->query($item_sql);
                                                    $subtotal = 0;
                                                    if ($item_result && $item_result->num_rows > 0) {
                                                        while ($item = $item_result->fetch_assoc()) {
                                                            $total = $item['price'] * $item['quantity'];
                                                            $subtotal += $total;

                                                            // Image logic
                                                            $img_path = 'assets/images/users/6.jpg'; // Default
                                                            if (!empty($item['product_image'])) {
                                                                $img_path = '../assets/uploads/products/' . $item['product_image'];
                                                            }

                                                            $variant_info = [];
                                                            if (!empty($item['variant_size']))
                                                                $variant_info[] = $item['variant_size'];
                                                            if (!empty($item['variant_color']))
                                                                $variant_info[] = $item['variant_color'];
                                                            $variant_str = implode(', ', $variant_info);

                                                            echo "<tr>";
                                                            echo "<td><div class='d-flex align-items-center'><a href='" . htmlspecialchars($img_path) . "' target='_blank'><img src='" . htmlspecialchars($img_path) . "' class='avatar avatar-md rounded-circle me-3' alt='product-img'></a></div></td>";
                                                            echo "<td>" . htmlspecialchars($item['product_name']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($variant_str) . "</td>";
                                                            echo "<td>₹" . number_format($item['price'], 2) . "</td>";
                                                            echo "<td>" . $item['quantity'] . "</td>";
                                                            echo "<td>₹" . number_format($total, 2) . "</td>";
                                                            echo "</tr>";
                                                        }
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td colspan="5" class="fw-bold text-end">Total Amount</td>
                                                        <td class="fw-bold">
                                                            ₹<?php echo number_format($order['total_amount'], 2); ?>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Customer Details</h3>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Name:</strong>
                                            <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                        <p><strong>Email:</strong>
                                            <?php echo htmlspecialchars($order['customer_email']); ?></p>
                                        <p><strong>Phone:</strong>
                                            <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                                        <p><strong>Payment Method:</strong>
                                            <?php echo htmlspecialchars($order['payment_method']); ?></p>
                                        <hr>
                                        <p><strong>Shipping Address:</strong><br>
                                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                                        </p>
                                        <?php if (!empty($order['order_notes'])): ?>
                                            <hr>
                                            <p><strong>Order Notes:</strong><br>
                                                <?php echo nl2br(htmlspecialchars($order['order_notes'])); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Update Status</h3>
                                    </div>
                                    <div class="card-body">
                                        <form action="order_actions.php" method="POST">
                                            <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                            <div class="form-group">
                                                <label class="form-label">Order Status</label>
                                                <select class="form-control form-select" name="status">
                                                    <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="processing" <?php echo ($order['status'] == 'processing') ? 'selected' : ''; ?>>
                                                        Processing</option>
                                                    <option value="shipped" <?php echo ($order['status'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                                    <option value="delivered" <?php echo ($order['status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                                    <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">Update Status</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- END ROW -->

                    </div>
                    <!-- CONTAINER END -->
                </div>
            </div>
            <!--app-content close-->

        </div>

        <?php include 'includes/footer.php'; ?>

    </div>

    <!-- BACK-TO-TOP -->
    <a href="#top" id="back-to-top"><i class="fa fa-angle-up"></i></a>

    <!-- JQUERY JS -->
    <script src="assets/js/jquery.min.js"></script>

    <!-- BOOTSTRAP JS -->
    <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>

    <!-- SPARKLINE JS-->
    <script src="assets/js/jquery.sparkline.min.js"></script>

    <!-- Sticky js -->
    <script src="assets/js/sticky.js"></script>

    <!-- SIDEBAR JS -->
    <script src="assets/plugins/sidebar/sidebar.js"></script>

    <!-- Perfect SCROLLBAR JS-->
    <script src="assets/plugins/p-scroll/perfect-scrollbar.js"></script>
    <script src="assets/plugins/p-scroll/pscroll.js"></script>
    <script src="assets/plugins/p-scroll/pscroll-1.js"></script>

    <!-- SIDE-MENU JS-->
    <script src="assets/plugins/sidemenu/sidemenu.js"></script>

    <!-- Color Theme js -->
    <script src="assets/js/themeColors.js"></script>

    <!-- CUSTOM JS -->
    <script src="assets/js/custom.js"></script>

</body>

</html>