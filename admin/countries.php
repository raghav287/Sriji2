<?php
require_once 'check_session.php';
require_once 'db.php';

$page_title = "Countries & Regions";
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


    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container {
            width: 100% !important;
            z-index: 9999;
            /* Ensure visible in modal */
        }

        .select2-dropdown {
            z-index: 9999;
        }
    </style>
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
                            <h1 class="page-title">Country & Region Management</h1>
                            <div>
                                <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#addCountryModal">
                                    <i class="fe fe-plus me-2"></i>Add Country
                                </button>
                            </div>
                        </div>
                        <!-- PAGE-HEADER END -->

                        <!-- ROW -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Countries List</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered text-nowrap border-bottom"
                                                id="basic-datatable">
                                                <thead>
                                                    <tr>
                                                        <th class="wd-15p border-bottom-0">ID</th>
                                                        <th class="wd-20p border-bottom-0">Name</th>
                                                        <th class="wd-15p border-bottom-0">Code</th>
                                                        <th class="wd-15p border-bottom-0">Pricing Zone</th>
                                                        <th class="wd-15p border-bottom-0">Status</th>
                                                        <th class="wd-25p border-bottom-0">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $sql = "SELECT c.*, pt.name as zone_name, pt.default_multiplier FROM countries c LEFT JOIN pricing_types pt ON c.pricing_type_id = pt.id ORDER BY c.name ASC";
                                                    $result = $conn->query($sql);
                                                    if ($result->num_rows > 0) {
                                                        while ($row = $result->fetch_assoc()) {
                                                            echo "<tr>";
                                                            echo "<td>" . $row['id'] . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['code']) . "</td>";

                                                            $zone_display = '-';
                                                            if ($row['zone_name']) {
                                                                $zone_display = '<span class="badge bg-primary">' . htmlspecialchars($row['zone_name']) . ' (x' . $row['default_multiplier'] . ')</span>';
                                                            } else {
                                                                // Fallback if data not migrated or unset
                                                                $zone_display = '<span class="badge bg-danger">Not Assigned</span>';
                                                            }
                                                            echo "<td>" . $zone_display . "</td>";

                                                            echo "<td>";
                                                            if ($row['status'] == 'active') {
                                                                echo '<span class="badge bg-success">Active</span>';
                                                            } else {
                                                                echo '<span class="badge bg-danger">Inactive</span>';
                                                            }
                                                            echo "</td>";
                                                            echo "<td>
                                                                    <button class='btn btn-sm btn-primary edit-country-btn' 
                                                                        data-id='" . $row['id'] . "' 
                                                                        data-name='" . htmlspecialchars($row['name']) . "' 
                                                                        data-code='" . htmlspecialchars($row['code']) . "' 
                                                                        data-multiplier='" . $row['multiplier'] . "' 
                                                                        data-pricing_model='" . $row['pricing_model'] . "' 
                                                                        data-status='" . $row['status'] . "' 
                                                                        data-bs-toggle='modal' data-bs-target='#editCountryModal'><i class='fe fe-edit'></i></button>
                                                                <a href='country_actions.php?action=delete&id=" . $row['id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'><i class='fe fe-trash'></i></a>
                                                                </td>";
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

        <!-- Add Country Modal -->
        <div class="modal fade" id="addCountryModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Country</h5>
                        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <form action="country_actions.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Country Name</label>
                                <!-- Converted to Select2 -->
                                <select class="form-control select2-add-country" name="name" id="add_country_name"
                                    style="width: 100%;" required>
                                    <option value="">Select Country</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Country Code (e.g. AU, US)</label>
                                <input type="text" class="form-control" name="code" required maxlength="5"
                                    style="text-transform: uppercase;">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pricing Zone</label>
                                <select class="form-control form-select" name="pricing_type_id" required>
                                    <option value="">Select Zone</option>
                                    <?php
                                    $pt_sql = "SELECT * FROM pricing_types ORDER BY name ASC";
                                    $pt_res = $conn->query($pt_sql);
                                    if ($pt_res->num_rows > 0) {
                                        while ($pt = $pt_res->fetch_assoc()) {
                                            echo '<option value="' . $pt['id'] . '">' . htmlspecialchars($pt['name']) . ' (Default x' . $pt['default_multiplier'] . ')</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select class="form-control form-select" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Country Modal -->
        <div class="modal fade" id="editCountryModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Country</h5>
                        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <form action="country_actions.php" method="POST">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Country Name</label>
                                <input type="text" class="form-control" name="name" id="edit_name" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Country Code</label>
                                <input type="text" class="form-control" name="code" id="edit_code" required
                                    maxlength="5" style="text-transform: uppercase;">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pricing Zone</label>
                                <select class="form-control form-select" name="pricing_type_id"
                                    id="edit_pricing_type_id" required>
                                    <option value="">Select Zone</option>
                                    <?php
                                    if (isset($pt_res) && $pt_res->num_rows > 0) {
                                        $pt_res->data_seek(0);
                                        while ($pt = $pt_res->fetch_assoc()) {
                                            echo '<option value="' . $pt['id'] . '">' . htmlspecialchars($pt['name']) . ' (Default x' . $pt['default_multiplier'] . ')</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select class="form-control form-select" name="status" id="edit_status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
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
        // Data Table
        $('#basic-datatable').DataTable({
            language: {
                searchPlaceholder: 'Search...',
                sSearch: '',
            }
        });

        // Edit Modal Data Passing
        $(document).on("click", ".edit-country-btn", function () {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var code = $(this).data('code');
            var pricing_type_id = $(this).data('pricing_type_id');
            var status = $(this).data('status');

            $("#editCountryModal input[name='id']").val(id);
            $("#editCountryModal input[name='name']").val(name);
            $("#editCountryModal input[name='code']").val(code);
            $("#edit_pricing_type_id").val(pricing_type_id);
            $("#editCountryModal select[name='status']").val(status);
        });
    </script>

    <!-- SELECT2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            // Init Select2 for Add Modal
            $('.select2-add-country').select2({
                dropdownParent: $('#addCountryModal')
            });

            // Fetch countries for Add Modal
            $.get("https://countriesnow.space/api/v0.1/countries/iso", function (data) {
                if (!data.error) {
                    var options = '<option value="">Select Country</option>';
                    data.data.forEach(function (c) {
                        // Store ISO Code in data attribute
                        options += `<option value="${c.name}" data-code="${c.Iso2}">${c.name}</option>`;
                    });
                    $('#add_country_name').html(options);
                }
            });

            // Auto-fill Code on Change
            $('#add_country_name').on('change', function () {
                var selected = $(this).find(':selected');
                var code = selected.data('code');
                if (code) {
                    $('input[name="code"]').val(code);
                }
            });
        });
    </script>

</body>

</html>