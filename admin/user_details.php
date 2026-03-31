<?php
require_once 'check_session.php';
require_once 'db.php';

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$id = $_GET['id'];
$sql = "SELECT * FROM users WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: users.php");
    exit();
}

$user = $result->fetch_assoc();
$page_title = "User Details: " . $user['name'];
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
                            <h1 class="page-title">User Details</h1>
                            <div>
                                <a href="users.php" class="btn btn-secondary">
                                    <i class="fe fe-arrow-left me-2"></i>Back to List
                                </a>
                            </div>
                        </div>
                        <!-- PAGE-HEADER END -->

                        <!-- ROW -->
                        <div class="row">
                            <div class="col-xl-4">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">Profile</div>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center chat-image mb-5">
                                            <div class="avatar avatar-xxl chat-profile mb-3 brround">
                                                <?php
                                                $u_img = 'assets/images/users/7.jpg';
                                                if (!empty($user['profile_image'])) {
                                                    $u_img = '../assets/uploads/users/' . $user['profile_image'];
                                                }
                                                ?>
                                                <img alt="avatar" src="<?php echo htmlspecialchars($u_img); ?>"
                                                    class="brround"
                                                    style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                            <div class="main-chat-msg-name">
                                                <a href="javascript:void(0)">
                                                    <h5 class="mb-1 text-dark fw-semibold">
                                                        <?php echo htmlspecialchars($user['name']); ?>
                                                    </h5>
                                                </a>
                                                <p class="text-muted mt-0 mb-0 pt-0 fs-13">Customer</p>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table border-0 mb-0">
                                                <tbody>
                                                    <tr>
                                                        <td class="py-2 px-0">
                                                            <span class="w-50">Email</span>
                                                        </td>
                                                        <td class="py-2 px-0">
                                                            : <?php echo htmlspecialchars($user['email']); ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="py-2 px-0">
                                                            <span class="w-50">Phone</span>
                                                        </td>
                                                        <td class="py-2 px-0">
                                                            : <?php echo htmlspecialchars($user['phone']); ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="py-2 px-0">
                                                            <span class="w-50">Joined</span>
                                                        </td>
                                                        <td class="py-2 px-0">
                                                            :
                                                            <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">Address Book</div>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        $addr_sql = "SELECT * FROM user_addresses WHERE user_id = $id ORDER BY is_default DESC";
                                        $addr_res = $conn->query($addr_sql);
                                        if ($addr_res->num_rows > 0) {
                                            while ($addr = $addr_res->fetch_assoc()) {
                                                echo '<div class="mb-4 class="border-bottom pb-2">';
                                                echo '<h5 class="fw-semibold">' . htmlspecialchars($addr['address_type']) . ($addr['is_default'] ? ' <span class="badge bg-success badge-sm">Default</span>' : '') . '</h5>';
                                                echo '<p class="mb-1">' . htmlspecialchars($addr['recipient_name']) . '</p>';
                                                echo '<p class="mb-1">' . htmlspecialchars($addr['address']) . '</p>';
                                                echo '<p class="mb-1">' . htmlspecialchars($addr['city']) . ', ' . htmlspecialchars($addr['state']) . ' ' . htmlspecialchars($addr['zip_code']) . '</p>';
                                                echo '<p class="mb-0">' . htmlspecialchars($addr['country']) . '</p>';
                                                echo '</div>';
                                            }
                                        } else {
                                            echo "<p class='text-muted'>No addresses found.</p>";
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Order History</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered text-nowrap border-bottom">
                                                <thead>
                                                    <tr>
                                                        <th>Order #</th>
                                                        <th>Date</th>
                                                        <th>Total</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    // Link orders by ID preferably, fall back to email if user logic dictates
                                                    $ord_sql = "SELECT * FROM orders WHERE user_id = $id OR customer_email = '" . $user['email'] . "' ORDER BY created_at DESC";
                                                    $ord_res = $conn->query($ord_sql);
                                                    if ($ord_res->num_rows > 0) {
                                                        while ($order = $ord_res->fetch_assoc()) {
                                                            echo "<tr>";
                                                            echo "<td>#" . htmlspecialchars($order['order_number']) . "</td>";
                                                            echo "<td>" . date('M d, Y', strtotime($order['created_at'])) . "</td>";
                                                            echo "<td>$" . number_format($order['total_amount'], 2) . "</td>";
                                                            echo "<td><span class='badge bg-primary'>" . ucfirst($order['status']) . "</span></td>";
                                                            echo "<td><a href='order_details.php?id=" . $order['id'] . "' class='btn btn-sm btn-light'>View</a></td>";
                                                            echo "</tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='5' class='text-center'>No orders found.</td></tr>";
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
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