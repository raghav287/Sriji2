<?php
require_once 'check_session.php';
require_once 'db.php';

$page_title = "Shipping & COD Settings";

// Fetch current settings
$settings = [];
$sql = "SELECT * FROM site_settings";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
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
                            <h1 class="page-title">Shipping & COD Configuration</h1>
                        </div>
                        <!-- PAGE-HEADER END -->

                        <!-- ROW -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Manage Settings</h3>
                                    </div>
                                    <div class="card-body">
                                        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                                            <div class="alert alert-success">Settings updated successfully!</div>
                                        <?php endif; ?>

                                        <form action="setting_actions.php" method="POST">
                                            <h4 class="mb-3">Shipping Charges</h4>
                                            <div class="row mb-4">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="form-label">Free Shipping Limit Amount
                                                            (Cap)</label>
                                                        <input type="number" class="form-control" name="shipping_cap"
                                                            value="<?php echo $settings['shipping_cap'] ?? '500'; ?>"
                                                            required>
                                                        <small class="text-muted">Orders below this amount will be
                                                            charged shipping.</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="form-label">Shipping Charge (Below Limit)</label>
                                                        <input type="number" class="form-control"
                                                            name="shipping_charge_below"
                                                            value="<?php echo $settings['shipping_charge_below'] ?? '50'; ?>"
                                                            required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="form-label">Shipping Charge (Above Limit)</label>
                                                        <input type="number" class="form-control"
                                                            name="shipping_charge_above"
                                                            value="<?php echo $settings['shipping_charge_above'] ?? '0'; ?>"
                                                            required>
                                                        <small class="text-muted">Usually 0 if free shipping is enabled
                                                            above cap.</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <h4 class="mb-3">Cash on Delivery (COD)</h4>
                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="form-label">Enable COD?</label>
                                                        <select class="form-control form-select" name="cod_active">
                                                            <option value="1" <?php echo ($settings['cod_active'] ?? '1') == '1' ? 'selected' : ''; ?>>Yes, Active</option>
                                                            <option value="0" <?php echo ($settings['cod_active'] ?? '1') == '0' ? 'selected' : ''; ?>>No, Deactivate</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="form-label">COD Limit Amount (Cap)</label>
                                                        <input type="number" class="form-control" name="cod_limit"
                                                            value="<?php echo $settings['cod_limit'] ?? '500'; ?>"
                                                            required>
                                                        <small class="text-muted">Orders below this amount may have
                                                            different COD charge.</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="form-label">COD Charge (Below Limit)</label>
                                                        <input type="number" class="form-control"
                                                            name="cod_charge_below"
                                                            value="<?php echo $settings['cod_charge_below'] ?? ($settings['cod_charge'] ?? '20'); ?>"
                                                            required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="form-label">COD Charge (Above Limit)</label>
                                                        <input type="number" class="form-control"
                                                            name="cod_charge_above"
                                                            value="<?php echo $settings['cod_charge_above'] ?? '0'; ?>"
                                                            required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-4">
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
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

    <!-- INTERNAL Data tables js-->
    <script src="assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <script src="assets/plugins/datatable/js/dataTables.bootstrap5.js"></script>
    <script src="assets/plugins/datatable/dataTables.responsive.min.js"></script>


    <!-- CUSTOM JS -->
    <script src="assets/js/custom.js"></script>

</body>

</html>