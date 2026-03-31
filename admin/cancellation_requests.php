<?php
require_once 'check_session.php';
require_once 'db.php';

$page_title = "Cancellation Requests";
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
                            <h1 class="page-title">Cancellation Requests</h1>
                        </div>
                        <!-- PAGE-HEADER END -->

                        <!-- ROW -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Pending Requests</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered text-nowrap border-bottom"
                                                id="basic-datatable">
                                                <thead>
                                                    <tr>
                                                        <th class="wd-10p border-bottom-0">Order #</th>
                                                        <th class="wd-15p border-bottom-0">Customer</th>
                                                        <th class="wd-25p border-bottom-0">Reason</th>
                                                        <th class="wd-10p border-bottom-0">Requested At</th>
                                                        <th class="wd-10p border-bottom-0">Status</th>
                                                        <th class="wd-20p border-bottom-0">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $sql = "SELECT cr.*, o.order_number, u.name as customer_name, u.email as customer_email 
                                                            FROM cancellation_requests cr
                                                            LEFT JOIN orders o ON cr.order_id = o.id
                                                            LEFT JOIN users u ON cr.user_id = u.id
                                                            ORDER BY cr.created_at DESC";
                                                    $result = $conn->query($sql);
                                                    if ($result->num_rows > 0) {
                                                        while ($row = $result->fetch_assoc()) {
                                                            echo "<tr>";
                                                            echo "<td><a href='order_details.php?id=" . $row['order_id'] . "'>#" . htmlspecialchars($row['order_number']) . "</a></td>";
                                                            echo "<td>" . htmlspecialchars($row['customer_name'] ?? 'Guest') . "<br><small class='text-muted'>" . htmlspecialchars($row['customer_email']) . "</small></td>";
                                                            echo "<td><span title='" . htmlspecialchars($row['reason']) . "'>" . htmlspecialchars(substr($row['reason'], 0, 50)) . (strlen($row['reason']) > 50 ? '...' : '') . "</span></td>";
                                                            echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";

                                                            $status = $row['status'];
                                                            $badge = $status == 'pending' ? 'bg-warning' : ($status == 'approved' ? 'bg-success' : 'bg-danger');
                                                            echo "<td><span class='badge $badge'>" . ucfirst($status) . "</span></td>";

                                                            echo "<td>";
                                                            if ($status == 'pending') {
                                                                echo "<form method='POST' action='cancellation_actions.php' style='display:inline-block;'>
                                                                        <input type='hidden' name='request_id' value='" . $row['id'] . "'>
                                                                        <input type='hidden' name='order_id' value='" . $row['order_id'] . "'>
                                                                        <button type='submit' name='action' value='approve' class='btn btn-sm btn-success me-1' onclick=\"return confirm('Are you sure you want to approve this? Order will be cancelled.');\"><i class='fe fe-check'></i> Approve</button>
                                                                        <button type='submit' name='action' value='reject' class='btn btn-sm btn-danger' onclick=\"return confirm('Are you sure you want to reject this?');\"><i class='fe fe-x'></i> Reject</button>
                                                                      </form>";
                                                            } else {
                                                                echo "<span class='text-muted'>$status</span>";
                                                            }
                                                            echo "</td>";
                                                            echo "</tr>";
                                                        }
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

    <!-- INTERNAL Data tables js-->
    <script src="assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <script src="assets/plugins/datatable/js/dataTables.bootstrap5.js"></script>
    <script src="assets/plugins/datatable/dataTables.responsive.min.js"></script>


    <!-- CUSTOM JS -->
    <script src="assets/js/custom.js"></script>
    <script>
        // Data Table
        $('#basic-datatable').DataTable({
            language: {
                searchPlaceholder: 'Search...',
                sSearch: '',
            },
        });
    </script>

</body>

</html>