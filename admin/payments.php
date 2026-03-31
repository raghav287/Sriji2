<?php
require_once 'check_session.php';
require_once 'db.php';

$page_title = "Payments";

// Filter Logic
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = "1";
if ($status_filter) {
    $where_clause .= " AND p.status = '" . $conn->real_escape_string($status_filter) . "'";
}

// Fetch Payments
$sql = "SELECT p.*, o.order_number, u.name as user_name, u.email as user_email 
        FROM payments p 
        LEFT JOIN orders o ON p.order_id = o.id 
        LEFT JOIN users u ON p.user_id = u.id 
        WHERE $where_clause 
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);
?>
<!doctype html>
<html lang="en" dir="ltr">

<head>
    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Admin Panel">
    <meta name="author" content="Spruko Technologies Private Limited">
    <meta name="keywords" content="admin, dashboard">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/brand/favicon.ico">

    <!-- TITLE -->
    <title><?php echo $page_title; ?> - Admin</title>

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

    <div class="page">
        <div class="page-main">

            <?php include 'includes/header.php'; ?>
            <?php include 'includes/sidebar.php'; ?>

            <div class="main-content app-content mt-0">
                <div class="side-app">

                    <div class="main-container container-fluid">

                        <div class="page-header">
                            <h1 class="page-title">Payment History</h1>
                        </div>

                        <!-- Filters -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <form method="GET" class="d-flex gap-2 align-items-center">
                                    <label class="form-label mb-0">Filter Status:</label>
                                    <select name="status" class="form-control form-select w-auto"
                                        onchange="this.form.submit()">
                                        <option value="">All</option>
                                        <option value="success" <?php echo $status_filter == 'success' ? 'selected' : ''; ?>>Success</option>
                                        <option value="failed" <?php echo $status_filter == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                    </select>
                                    <?php if ($status_filter): ?>
                                        <a href="payments.php" class="btn btn-secondary btn-sm">Reset</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Transactions</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered text-nowrap border-bottom"
                                                id="payment-datatable">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Date</th>
                                                        <th>Order No</th>
                                                        <th>User</th>
                                                        <th>Payment ID</th>
                                                        <th>Amount</th>
                                                        <th>Status</th>
                                                        <th>Method</th>
                                                        <th>Details</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if ($result && $result->num_rows > 0) {
                                                        while ($row = $result->fetch_assoc()) {
                                                            $status_badge = '';
                                                            if ($row['status'] == 'success') {
                                                                $status_badge = '<span class="badge bg-success">Success</span>';
                                                            } elseif ($row['status'] == 'failed') {
                                                                $status_badge = '<span class="badge bg-danger">Failed</span>';
                                                            } else {
                                                                $status_badge = '<span class="badge bg-warning">' . ucfirst($row['status']) . '</span>';
                                                            }

                                                            $user_display = $row['user_name'] ? htmlspecialchars($row['user_name']) : 'Guest';
                                                            if ($row['user_email'])
                                                                $user_display .= "<br><small class='text-muted'>" . $row['user_email'] . "</small>";

                                                            echo "<tr>";
                                                            echo "<td>" . $row['id'] . "</td>";
                                                            echo "<td>" . date('d M Y h:i A', strtotime($row['created_at'])) . "</td>";
                                                            echo "<td>" . ($row['order_number'] ? "<a href='order_details.php?id=" . $row['order_id'] . "'>" . $row['order_number'] . "</a>" : "N/A") . "</td>";
                                                            echo "<td>" . $user_display . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['payment_id']) . "</td>";
                                                            echo "<td>₹" . number_format($row['amount'], 2) . "</td>";
                                                            echo "<td>" . $status_badge . "</td>";
                                                            echo "<td>" . ucfirst($row['method']) . "</td>";
                                                            echo "<td>" . ($row['error_message'] ? '<span class="text-danger" title="' . htmlspecialchars($row['error_message']) . '">Error Info</span>' : '-') . "</td>";
                                                            echo "</tr>";
                                                        }
                                                    } else {
                                                        // echo "<tr><td colspan='9' class='text-center'>No payments found</td></tr>";
                                                    }
                                                    ?>
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

    <!-- INTERNAL Data tables js-->
    <script src="assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <script src="assets/plugins/datatable/js/dataTables.bootstrap5.js"></script>
    <script src="assets/plugins/datatable/dataTables.responsive.min.js"></script>


    <!-- CUSTOM JS -->
    <script src="assets/js/custom.js"></script>

    <script>
        $(document).ready(function () {
            $('#payment-datatable').DataTable({
                "order": [[0, "desc"]]
            });
        });
    </script>

</body>

</html>