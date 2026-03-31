<?php
require_once 'check_session.php';

// Fetch Dashboard Stats
// Filter Logic
$where = "WHERE 1=1";
if (!empty($_GET['start_date'])) {
    $start_date = $conn->real_escape_string($_GET['start_date']);
    $where .= " AND DATE(created_at) >= '$start_date'";
}
if (!empty($_GET['end_date'])) {
    $end_date = $conn->real_escape_string($_GET['end_date']);
    $where .= " AND DATE(created_at) <= '$end_date'";
}
if (!empty($_GET['payment_method'])) {
    $pm = $conn->real_escape_string($_GET['payment_method']);
    $where .= " AND payment_method = '$pm'";
}

// 1. Total Orders
$stmt = $conn->query("SELECT COUNT(*) as total_orders FROM orders $where");
$total_orders = $stmt->fetch_assoc()['total_orders'];

// 2. Total Sales
$stmt = $conn->query("SELECT SUM(total_amount) as total_sales FROM orders $where");
$total_sales = $stmt->fetch_assoc()['total_sales'];
$total_sales = $total_sales ? $total_sales : 0;

// 3. Total Online Orders
$stmt = $conn->query("SELECT COUNT(*) as total_online FROM orders $where AND payment_method = 'Online'");
$total_online = $stmt->fetch_assoc()['total_online'];

// 4. Total COD Orders
$stmt = $conn->query("SELECT COUNT(*) as total_cod FROM orders $where AND payment_method = 'COD'");
$total_cod = $stmt->fetch_assoc()['total_cod'];

// 5. Pending Cancellations (keeping unfiltered for now as it uses a different table)
$stmt = $conn->query("SELECT COUNT(*) as pending_cancellations FROM cancellation_requests WHERE status = 'pending'");
$pending_cancellations = $stmt->fetch_assoc()['pending_cancellations'];

// 6. Unread Messages
$stmt = $conn->query("SELECT COUNT(*) as unread_messages FROM contact_messages WHERE is_read = 0");
$unread_messages = $stmt->fetch_assoc()['unread_messages'];

// Fetch Recent Orders with Filter
$recent_orders_sql = "SELECT id, order_number, customer_name, total_amount, status, payment_method, created_at 
                      FROM orders 
                      $where
                      ORDER BY created_at DESC LIMIT 10";
$recent_orders_result = $conn->query($recent_orders_sql);

// --- CHART DATA PREPARATION ---

// Helper to get last N days data
// Helper to get last N days data
function getDailyStats($conn, $table, $date_col, $days, $where_condition = "1=1")
{
    // Remove "WHERE" if present in the passed condition to avoid "AND WHERE" error
    $condition = preg_replace('/^\s*WHERE\s+/i', '', $where_condition);

    $data = [];
    $labels = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $short_label = date('D', strtotime($date)); // Mon, Tue...

        $sql = "SELECT COUNT(*) as count FROM $table WHERE DATE($date_col) = '$date' AND $condition";
        $res = $conn->query($sql);
        if ($res === false) {
            // Fallback for debugging, though we expect it to work now
            $count = 0;
        } else {
            $count = $res->fetch_assoc()['count'];
        }

        $data[] = $count;
        $labels[] = $short_label;
    }
    return ['data' => $data, 'labels' => $labels];
}

function getDailySum($conn, $table, $sum_col, $date_col, $days, $where_condition = "1=1")
{
    // Remove "WHERE" if present
    $condition = preg_replace('/^\s*WHERE\s+/i', '', $where_condition);

    $data = [];
    $labels = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        // $short_label = date('jS M', strtotime($date)); // 1st Jan
        $short_label = date('d-M', strtotime($date)); // 01-Jan

        $sql = "SELECT SUM($sum_col) as total FROM $table WHERE DATE($date_col) = '$date' AND $condition";
        $res = $conn->query($sql);
        $total = ($res && $row = $res->fetch_assoc()) ? $row['total'] : 0;

        $data[] = $total ? $total : 0;
        $labels[] = $short_label;
    }
    return ['data' => $data, 'labels' => $labels];
}

// 1. Sales Chart (Total Orders - Last 7 Days)
$chart_orders = getDailyStats($conn, 'orders', 'created_at', 7, $where); // Uses filter $where
$orders_data = json_encode($chart_orders['data']);
$orders_labels = json_encode($chart_orders['labels']);

// 2. Leads Chart (Total Sales - Last 10 Days)
$chart_sales = getDailySum($conn, 'orders', 'total_amount', 'created_at', 10, $where);
$sales_data = json_encode($chart_sales['data']);
$sales_labels = json_encode($chart_sales['labels']);

// 3. Profit Chart (Pending Cancellations - Last 7 Days)
// Note: Created_at of cancellation request
$chart_cancel = getDailyStats($conn, 'cancellation_requests', 'created_at', 7, "status = 'pending'");
$cancel_data = json_encode($chart_cancel['data']);
$cancel_labels = json_encode($chart_cancel['labels']);

// 4. Cost Chart (Unread Messages - Last 10 Days)
$chart_msgs = getDailyStats($conn, 'contact_messages', 'created_at', 10, "is_read = 0");
$msgs_data = json_encode($chart_msgs['data']);
$msgs_labels = json_encode($chart_msgs['labels']);

// 5. Online Orders (Last 7 Days)
$chart_online = getDailyStats($conn, 'orders', 'created_at', 7, "$where AND payment_method = 'Online'");
$online_data = json_encode($chart_online['data']);

// 6. COD Orders (Last 7 Days)
$chart_cod = getDailyStats($conn, 'orders', 'created_at', 7, "$where AND payment_method = 'COD'");
$cod_data = json_encode($chart_cod['data']);

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
    <title>Dasboard - Admin Panel</title>

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
            <?php include 'includes/header.php' ?>
            <!-- app-Header -->

            <!--APP-SIDEBAR-->
            <?php include 'includes/sidebar.php' ?>
            <!--/APP-SIDEBAR-->

            <!--app-content open-->
            <div class="main-content app-content mt-0">
                <div class="side-app">

                    <!-- CONTAINER -->
                    <div class="main-container container-fluid">

                        <!-- PAGE-HEADER -->
                        <div class="page-header">
                            <h1 class="page-title">Dashboard</h1>
                            <div>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                                </ol>
                            </div>
                        </div>
                        <!-- PAGE-HEADER END -->

                        <!-- FILTER ROW -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Filter Data</h3>
                                    </div>
                                    <div class="card-body">
                                        <form method="GET" action="">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Start Date</label>
                                                        <input type="date" class="form-control" name="start_date"
                                                            value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">End Date</label>
                                                        <input type="date" class="form-control" name="end_date"
                                                            value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Payment Method</label>
                                                        <select class="form-control form-select" name="payment_method">
                                                            <option value="">All</option>
                                                            <option value="Online" <?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == 'Online') ? 'selected' : ''; ?>>Online</option>
                                                            <option value="COD" <?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == 'COD') ? 'selected' : ''; ?>>
                                                                Cash on Delivery</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group mt-4 pt-1">
                                                        <button type="submit" class="btn btn-primary btn-block">Apply
                                                            Filter</button>
                                                        <a href="index.php" class="btn btn-light btn-block">Reset</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- FILTER ROW END -->

                        <!-- ROW-1 -->
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xl-12">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                                        <div class="card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex">
                                                    <div class="mt-2">
                                                        <h6 class="">Total Orders</h6>
                                                        <h2 class="mb-0 number-font">
                                                            <?php echo number_format($total_orders); ?>
                                                        </h2>
                                                    </div>
                                                    <div class="ms-auto">
                                                        <div class="chart-wrapper mt-1">
                                                            <canvas id="saleschart"
                                                                class="h-8 w-9 chart-dropshadow"></canvas>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="text-muted fs-12">Last 7 days</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                                        <div class="card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex">
                                                    <div class="mt-2">
                                                        <h6 class="">Total Sales</h6>
                                                        <h2 class="mb-0 number-font">
                                                            ₹<?php echo number_format($total_sales, 2); ?></h2>
                                                    </div>
                                                    <div class="ms-auto">
                                                        <div class="chart-wrapper mt-1">
                                                            <canvas id="leadschart"
                                                                class="h-8 w-9 chart-dropshadow"></canvas>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="text-muted fs-12">Last 10 days</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                                        <div class="card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex">
                                                    <div class="mt-2">
                                                        <h6 class="">Pending Cancellations</h6>
                                                        <h2 class="mb-0 number-font">
                                                            <?php echo number_format($pending_cancellations); ?>
                                                        </h2>
                                                    </div>
                                                    <div class="ms-auto">
                                                        <div class="chart-wrapper mt-1">
                                                            <canvas id="profitchart"
                                                                class="h-8 w-9 chart-dropshadow"></canvas>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="text-muted fs-12">Last 7 days</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                                        <div class="card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex">
                                                    <div class="mt-2">
                                                        <h6 class="">Unread Messages</h6>
                                                        <h2 class="mb-0 number-font">
                                                            <?php echo number_format($unread_messages); ?>
                                                        </h2>
                                                    </div>
                                                    <div class="ms-auto">
                                                        <div class="chart-wrapper mt-1">
                                                            <canvas id="costchart"
                                                                class="h-8 w-9 chart-dropshadow"></canvas>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="text-muted fs-12">Last 10 days</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                                        <div class="card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex">
                                                    <div class="mt-2">
                                                        <h6 class="">Online Orders</h6>
                                                        <h2 class="mb-0 number-font">
                                                            <?php echo number_format($total_online); ?>
                                                        </h2>
                                                    </div>
                                                    <div class="ms-auto">
                                                        <div class="chart-wrapper mt-1">
                                                            <canvas id="saleschart2"
                                                                class="h-8 w-9 chart-dropshadow"></canvas>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="text-muted fs-12"><span class="text-info"><i
                                                            class="fe fe-credit-card text-info"></i></span>
                                                    Total Online</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                                        <div class="card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex">
                                                    <div class="mt-2">
                                                        <h6 class="">COD Orders</h6>
                                                        <h2 class="mb-0 number-font">
                                                            <?php echo number_format($total_cod); ?>
                                                        </h2>
                                                    </div>
                                                    <div class="ms-auto">
                                                        <div class="chart-wrapper mt-1">
                                                            <canvas id="saleschart3"
                                                                class="h-8 w-9 chart-dropshadow"></canvas>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="text-muted fs-12"><span class="text-success"><i
                                                            class="fe fe-truck text-success"></i></span>
                                                    Total COD</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ROW-1 END -->

                        <!-- ROW-4 -->
                        <div class="row">
                            <div class="col-12 col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title mb-0">Recent Orders</h3>
                                    </div>
                                    <div class="card-body pt-4">
                                        <div class="table-responsive">
                                            <table id="data-table" class="table table-bordered text-nowrap mb-0">
                                                <thead class="border-top">
                                                    <tr>
                                                        <th class="bg-transparent border-bottom-0" style="width: 5%;">
                                                            Tracking Id</th>
                                                        <th class="bg-transparent border-bottom-0">
                                                            Customer</th>
                                                        <th class="bg-transparent border-bottom-0">
                                                            Date</th>
                                                        <th class="bg-transparent border-bottom-0">
                                                            Amount</th>
                                                        <th class="bg-transparent border-bottom-0">
                                                            Payment Mode</th>
                                                        <th class="bg-transparent border-bottom-0" style="width: 10%;">
                                                            Status</th>
                                                        <th class="bg-transparent border-bottom-0" style="width: 5%;">
                                                            Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($order = $recent_orders_result->fetch_assoc()): ?>
                                                        <tr class="border-bottom">
                                                            <td class="text-center">
                                                                <div class="mt-0 mt-sm-2 d-block">
                                                                    <h6 class="mb-0 fs-14 fw-semibold">
                                                                        <?php echo $order['order_number']; ?>
                                                                    </h6>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex">
                                                                    <div class="mt-0 mt-sm-3 d-block">
                                                                        <h6 class="mb-0 fs-14 fw-semibold">
                                                                            <?php echo htmlspecialchars($order['customer_name']); ?>
                                                                        </h6>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><span
                                                                    class="mt-sm-2 d-block"><?php echo date('d M Y', strtotime($order['created_at'])); ?></span>
                                                            </td>
                                                            <td><span
                                                                    class="fw-semibold mt-sm-2 d-block">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex">
                                                                    <div class="mt-0 mt-sm-3 d-block">
                                                                        <h6 class="mb-0 fs-14 fw-semibold">
                                                                            <?php echo htmlspecialchars($order['payment_method']); ?>
                                                                        </h6>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="mt-sm-1 d-block">
                                                                    <?php
                                                                    $status_class = 'bg-warning-transparent text-warning';
                                                                    if ($order['status'] == 'completed' || $order['status'] == 'shipped') {
                                                                        $status_class = 'bg-success-transparent text-success';
                                                                    } elseif ($order['status'] == 'cancelled') {
                                                                        $status_class = 'bg-danger-transparent text-danger';
                                                                    }
                                                                    ?>
                                                                    <span
                                                                        class="badge <?php echo $status_class; ?> rounded-pill p-2 px-3"><?php echo ucfirst($order['status']); ?></span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="g-2">
                                                                    <a href="order_details.php?id=<?php echo $order['id']; ?>"
                                                                        class="btn text-primary btn-sm"
                                                                        data-bs-toggle="tooltip"
                                                                        data-bs-original-title="View"><span
                                                                            class="fe fe-eye fs-14"></span></a>
                                                                </div>
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
                        <!-- ROW-4 END -->
                    </div>
                    <!-- CONTAINER END -->
                </div>
            </div>
            <!--app-content close-->

        </div>

        <?php include 'includes/footer.php'; ?>

        <!-- BACK-TO-TOP -->
        <a href="#top" id="back-to-top"><i class="fa fa-angle-up"></i></a>

        <!-- JQUERY JS -->
        <script src="assets/js/jquery.min.js"></script>

        <script>
            // Dynamic Chart Data from PHP
            var ordersData = <?php echo $orders_data; ?>;
            var ordersLabels = <?php echo $orders_labels; ?>;

            var salesData = <?php echo $sales_data; ?>;
            var salesLabels = <?php echo $sales_labels; ?>;

            var cancelData = <?php echo $cancel_data; ?>;
            var cancelLabels = <?php echo $cancel_labels; ?>;

            var msgsData = <?php echo $msgs_data; ?>;
            var msgsLabels = <?php echo $msgs_labels; ?>;

            var onlineData = <?php echo $online_data; ?>;
            var codData = <?php echo $cod_data; ?>;
        </script>

        <!-- BOOTSTRAP JS -->
        <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
        <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>

        <!-- SPARKLINE JS-->
        <script src="assets/js/jquery.sparkline.min.js"></script>

        <!-- Sticky js -->
        <script src="assets/js/sticky.js"></script>

        <!-- CHART-CIRCLE JS-->
        <script src="assets/js/circle-progress.min.js"></script>

        <!-- PIETY CHART JS-->
        <script src="assets/plugins/peitychart/jquery.peity.min.js"></script>
        <script src="assets/plugins/peitychart/peitychart.init.js"></script>

        <!-- SIDEBAR JS -->
        <script src="assets/plugins/sidebar/sidebar.js"></script>

        <!-- Perfect SCROLLBAR JS-->
        <script src="assets/plugins/p-scroll/perfect-scrollbar.js"></script>
        <script src="assets/plugins/p-scroll/pscroll.js"></script>
        <script src="assets/plugins/p-scroll/pscroll-1.js"></script>

        <!-- INTERNAL CHARTJS CHART JS-->
        <script src="assets/plugins/chart/Chart.bundle.js"></script>
        <script src="assets/plugins/chart/utils.js"></script>

        <!-- INTERNAL SELECT2 JS -->
        <script src="assets/plugins/select2/select2.full.min.js"></script>

        <!-- INTERNAL Data tables js-->
        <script src="assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
        <script src="assets/plugins/datatable/js/dataTables.bootstrap5.js"></script>
        <script src="assets/plugins/datatable/dataTables.responsive.min.js"></script>

        <!-- INTERNAL APEXCHART JS -->
        <script src="assets/js/apexcharts.js"></script>
        <script src="assets/plugins/apexchart/irregular-data-series.js"></script>

        <!-- INTERNAL Flot JS -->
        <script src="assets/plugins/flot/jquery.flot.js"></script>
        <script src="assets/plugins/flot/jquery.flot.fillbetween.js"></script>
        <script src="assets/plugins/flot/chart.flot.sampledata.js"></script>
        <script src="assets/plugins/flot/dashboard.sampledata.js"></script>

        <!-- INTERNAL Vector js -->
        <script src="assets/plugins/jvectormap/jquery-jvectormap-2.0.2.min.js"></script>
        <script src="assets/plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>

        <!-- SIDE-MENU JS-->
        <script src="assets/plugins/sidemenu/sidemenu.js"></script>

        <!-- TypeHead js -->
        <script src="assets/plugins/bootstrap5-typehead/autocomplete.js"></script>
        <script src="assets/js/typehead.js"></script>

        <!-- INTERNAL INDEX JS -->
        <script src="assets/js/index1.js"></script>

        <!-- Color Theme js -->
        <script src="assets/js/themeColors.js"></script>

        <!-- CUSTOM JS -->
        <script src="assets/js/custom.js"></script>

        <!-- Custom-switcher -->
        <script src="assets/js/custom-swicher.js"></script>

        <!-- Switcher js -->
        <script src="assets/switcher/js/switcher.js"></script>

</body>

</html>