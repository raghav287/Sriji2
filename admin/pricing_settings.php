<?php
require_once 'check_session.php';
require_once 'db.php';

$page_title = "Pricing Settings";

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    if ($action == 'add_type') {
        $name = $conn->real_escape_string($_POST['name']);
        $multiplier = floatval($_POST['multiplier']); // Default Mult
        $conn->query("INSERT INTO pricing_types (name, default_multiplier) VALUES ('$name', '$multiplier')");
        header("Location: pricing_settings.php?msg=added_type");
        exit;
    } 
    elseif ($action == 'update_type_settings') {
        $id = intval($_POST['id']);
        $name = $conn->real_escape_string($_POST['name']);
        $multiplier = floatval($_POST['multiplier']);
        
        $conn->query("UPDATE pricing_types SET name='$name', default_multiplier='$multiplier' WHERE id=$id");
         header("Location: pricing_settings.php?msg=updated_type&edit_type=$id");
        exit;
    }
    elseif ($action == 'delete_type') {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM pricing_types WHERE id=$id");
        header("Location: pricing_settings.php?msg=deleted_type");
        exit;
    } elseif ($action == 'save_rules') {
        $type_id = intval($_POST['type_id']);

        // Clear existing rules for this type (Simple replacement strategy)
        $conn->query("DELETE FROM pricing_rules WHERE pricing_type_id=$type_id");

        if (isset($_POST['rules'])) {
            foreach ($_POST['rules'] as $rule) {
                $size = $conn->real_escape_string($rule['size']);
                $factor = floatval($rule['factor']);
                $constant = floatval($rule['constant']);

                if ($size !== '') {
                    $conn->query("INSERT INTO pricing_rules (pricing_type_id, size_label, multiplier_factor, constant_amount) VALUES ($type_id, '$size', '$factor', '$constant')");
                }
            }
        }
        header("Location: pricing_settings.php?msg=rules_saved&edit_type=$type_id");
        exit;
    }
}

$active_tab = isset($_GET['edit_type']) ? 'edit' : 'list';
$edit_type_id = isset($_GET['edit_type']) ? intval($_GET['edit_type']) : 0;
$edit_type_name = '';
$current_rules = [];

if ($edit_type_id) {
    if (isset($_GET['tab']))
        $active_tab = $_GET['tab']; // Support direct tab linking if needed

    $res = $conn->query("SELECT * FROM pricing_types WHERE id=$edit_type_id");
    if ($res->num_rows > 0) {
        $edit_type_name = $res->fetch_assoc()['name'];
        $rules_res = $conn->query("SELECT * FROM pricing_rules WHERE pricing_type_id=$edit_type_id ORDER BY size_label ASC");
        while ($r = $rules_res->fetch_assoc()) {
            $current_rules[] = $r;
        }
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
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keywords"
        content="">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/brand/favicon.ico">
    <title>Pricing Settings - Admin</title>
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

    <div class="page">
        <div class="page-main">
            <?php include 'includes/header.php'; ?>
            <?php include 'includes/sidebar.php'; ?>

            <div class="main-content app-content mt-0">
                <div class="side-app">
                    <div class="main-container container-fluid">
                        <div class="page-header">
                            <h1 class="page-title">Pricing Formulas & Zones</h1>
                        </div>

                        <div class="row">
                            <!-- List Types -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Formulas / Zones</h3>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" class="mb-4">
                                            <input type="hidden" name="action" value="add_type">
                                            <div class="mb-2">
                                                <input type="text" class="form-control" name="name" placeholder="New Zone Name" required>
                                            </div>
                                            <div class="input-group">
                                                <input type="number" step="0.01" class="form-control" name="multiplier" placeholder="Default Multiplier (e.g. 2.5)" required>
                                                <button class="btn btn-primary" type="submit">Add</button>
                                            </div>
                                        </form>

                                        <ul class="list-group">
                                            <?php
                                            $types = $conn->query("SELECT * FROM pricing_types");
                                            while ($t = $types->fetch_assoc()):
                                                ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center <?php echo $edit_type_id == $t['id'] ? 'active' : ''; ?>">
                                                        <a href="?edit_type=<?php echo $t['id']; ?>" class="<?php echo $edit_type_id == $t['id'] ? 'text-white' : ''; ?>">
                                                            <?php echo htmlspecialchars($t['name']); ?>
                                                        </a>
                                                        <?php if ($edit_type_id != $t['id']): ?>
                                                            <form method="POST" style="display:inline;">
                                                                <input type="hidden" name="action" value="delete_type">
                                                                <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger py-0" onclick="return confirm('Delete this formula?');"><i class="fe fe-trash"></i></button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </li>
                                            <?php endwhile; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Rules & Countries -->
                            <?php if ($edit_type_id): ?>
                                <div class="col-md-8">
                                    <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Managing Zone</h3>
                                    </div>
                                    <div class="card-body">
                                        <!-- Zone Settings -->
                                        <form method="POST" class="row g-3 mb-4 border-bottom pb-4">
                                            <input type="hidden" name="action" value="update_type_settings">
                                            <input type="hidden" name="id" value="<?php echo $edit_type_id; ?>">
                                            <div class="col-md-6">
                                                <label class="form-label">Zone Name</label>
                                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($edit_type_name); ?>" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Default Multiplier</label>
                                                <?php
                                                // Need to fetch current multiplier. It wasn't fetched in previous logic block, fetching again or relying on $edit_type_data
                                                $ez_res = $conn->query("SELECT default_multiplier FROM pricing_types WHERE id=$edit_type_id");
                                                $ez_mult = $ez_res->fetch_assoc()['default_multiplier'];
                                                ?>
                                                <input type="number" step="0.01" class="form-control" name="multiplier" value="<?php echo $ez_mult; ?>" required>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="submit" class="btn btn-primary w-100">Update</button>
                                            </div>
                                        </form>
                                        
                                        <!-- Tabs -->
                                            <ul class="nav nav-tabs mb-4" id="pricingTab" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" id="rules-tab" data-bs-toggle="tab" href="#rules" role="tab" aria-controls="rules" aria-selected="true">Pricing Rules</a>
                                                </li>
                                            </ul>
                                        
                                            <div class="tab-content" id="pricingTabContent">
                                            
                                                <!-- Tab 1: Rules -->
                                                <div class="tab-pane fade show active" id="rules" role="tabpanel" aria-labelledby="rules-tab">
                                                    <p><small>Formula: (Price * Multiplier) + Constant</small></p>
                                                    <form method="POST">
                                                        <input type="hidden" name="action" value="save_rules">
                                                        <input type="hidden" name="type_id" value="<?php echo $edit_type_id; ?>">
                                                    
                                                        <table class="table table-bordered table-sm" id="rules_table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Size Label</th>
                                                                    <th>Multiplier Factor</th>
                                                                    <th>Constant Amount</th>
                                                                    <th style="width:50px;"></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($current_rules as $k => $rule): ?>
                                                                    <tr>
                                                                        <td><input type="text" class="form-control form-control-sm" name="rules[<?php echo $k; ?>][size]" value="<?php echo htmlspecialchars($rule['size_label']); ?>" required></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="rules[<?php echo $k; ?>][factor]" value="<?php echo $rule['multiplier_factor']; ?>" required></td>
                                                                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="rules[<?php echo $k; ?>][constant]" value="<?php echo $rule['constant_amount']; ?>" required></td>
                                                                        <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fe fe-trash"></i></button></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    
                                                        <div class="mt-3">
                                                            <button type="button" class="btn btn-info btn-sm" id="add_rule_row"><i class="fe fe-plus"></i> Add Rule</button>
                                                            <button type="submit" class="btn btn-success float-end">Save Rules</button>
                                                        </div>
                                                    </form>
                                                </div>

                                                <!-- Tab 2: Countries (Removed) -->
                                            
                                            </div> <!-- End Tab Content -->
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
            <?php include 'includes/footer.php'; ?>
        </div>
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
        $(document).ready(function(){
            // Dynamic Rule Row
            $('#add_rule_row').click(function(){
                var index = Date.now();
                var html = `<tr>
                                <td><input type="text" class="form-control form-control-sm" name="rules[${index}][size]" placeholder="Total Size" required></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm" name="rules[${index}][factor]" value="1.0" required></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm" name="rules[${index}][constant]" value="0" required></td>
                                <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fe fe-trash"></i></button></td>
                            </tr>`;
                $('#rules_table tbody').append(html);
            });
            
            $(document).on('click', '.remove-row', function(){
                $(this).closest('tr').remove();
            });

        });
    </script>
</body>
</html>