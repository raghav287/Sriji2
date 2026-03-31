<?php
require_once 'check_session.php';
require_once 'db.php';

$page_title = "Bulk Color Management";
$message = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['colors'])) {
    $updated_count = 0;

    foreach ($_POST['colors'] as $color_name => $color_code) {
        $color_name = trim($color_name);
        $color_code = trim($color_code);

        if (!empty($color_name) && !empty($color_code)) {
            // Update all variants with this color name
            $stmt = $conn->prepare("UPDATE product_variants SET color_code = ? WHERE color = ?");
            $stmt->bind_param("ss", $color_code, $color_name);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $updated_count += $stmt->affected_rows;
            }
        }
    }

    $message = "<div class='alert alert-success'>Successfully synchronized color codes for " . count($_POST['colors']) . " colors. Total variants updated: $updated_count</div>";
}

// Fetch all distinct colors and their current standard code
// MAX(color_code) will try to find a non-null existing code if available
$sql = "SELECT color, MAX(color_code) as current_code, COUNT(*) as variant_count 
        FROM product_variants 
        WHERE color IS NOT NULL AND color != '' 
        GROUP BY color 
        ORDER BY color";
$result = $conn->query($sql);
$colors = [];
while ($row = $result->fetch_assoc()) {
    $colors[] = $row;
}

?>
<!doctype html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>
        <?php echo $page_title; ?> - Admin
    </title>

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- STYLE CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- Plugins CSS -->
    <link href="assets/css/plugins.css" rel="stylesheet">
    <!--- FONT-ICONS CSS -->
    <link href="assets/css/icons.css" rel="stylesheet">

    <style>
        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 1px solid #ddd;
            display: inline-block;
            vertical-align: middle;
            margin-left: 10px;
        }
    </style>
</head>

<body class="app sidebar-mini ltr light-mode">
    <div class="page">
        <div class="page-main">

            <!-- app-Header -->
            <?php include 'includes/header.php' ?>
            <!-- app-Header -->

            <!--APP-SIDEBAR-->
            <?php include 'includes/sidebar.php' ?>
            <!--/APP-SIDEBAR-->

            <!-- APP-CONTENT OPEN -->
            <div class="main-content app-content mt-0">
                <div class="side-app">

                    <!-- CONTAINER -->
                    <div class="main-container container-fluid">

                        <!-- PAGE-HEADER -->
                        <div class="page-header">
                            <h1 class="page-title">
                                <?php echo $page_title; ?>
                            </h1>
                            <div>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        <?php echo $page_title; ?>
                                    </li>
                                </ol>
                            </div>
                        </div>

                        <?php echo $message; ?>

                        <div class="row">
                            <div class="col-xl-12">
                                <form action="" method="POST">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Manage Global Colors</h3>
                                            <div class="card-options">
                                                <button type="submit" class="btn btn-primary btn-sm"><i
                                                        class="fe fe-save"></i> Save All Colors</button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info">
                                                <i class="fe fe-info"></i> <strong>Note:</strong> Saving changes here
                                                will update the "Color Code" (Hex) for <strong>ALL</strong> products
                                                that share the same color name. This ensures consistency across your
                                                store.
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover v-align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th>Color Name</th>
                                                            <th>Affected Variants</th>
                                                            <th>Color Code Picker</th>
                                                            <th>Hex Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (count($colors) > 0): ?>
                                                            <?php foreach ($colors as $row):
                                                                $cCode = !empty($row['current_code']) ? $row['current_code'] : '#000000';
                                                                // Use base64 encoding for key to handle special characters in color name safely
                                                                $key = htmlspecialchars($row['color']);
                                                                ?>
                                                                <tr>
                                                                    <td><strong>
                                                                            <?php echo htmlspecialchars($row['color']); ?>
                                                                        </strong></td>
                                                                    <td>
                                                                        <span class="badge bg-secondary">
                                                                            <?php echo $row['variant_count']; ?> variants
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <input type="color"
                                                                            class="form-control form-control-color"
                                                                            name="colors[<?php echo $key; ?>]"
                                                                            value="<?php echo $cCode; ?>"
                                                                            title="Choose color for <?php echo $key; ?>"
                                                                            oninput="updateHex(this, 'hex_<?php echo bin2hex($row['color']); ?>')">
                                                                    </td>
                                                                    <td>
                                                                        <code
                                                                            id="hex_<?php echo bin2hex($row['color']); ?>"><?php echo $cCode; ?></code>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="4" class="text-center text-muted">No variants
                                                                    found in the database.</td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="card-footer text-end">
                                            <button type="submit" class="btn btn-primary"><i class="fe fe-save"></i>
                                                Save All Colors</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- APP-CONTENT CLOSE -->

        </div>

        <!-- FOOTER -->
        <?php include 'includes/footer.php' ?>
        <!-- FOOTER -->

    </div>

    <!-- JQUERY JS -->
    <script src="assets/js/jquery.min.js"></script>
    <!-- BOOTSTRAP JS -->
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <!-- SPARKLINE JS-->
    <script src="assets/js/jquery.sparkline.min.js"></script>
    <!-- Sticky js -->
    <script src="assets/js/sticky.js"></script>
    <!-- SIDEBAR JS -->
    <script src="assets/plugins/sidebar/sidebar.js"></script>
    <!-- INTERNAL P-scroll js -->
    <script src="assets/plugins/p-scrollbar/p-scrollbar.js"></script>
    <script src="assets/plugins/p-scrollbar/p-scroll1.js"></script>
    <!-- SIDE-MENU JS-->
    <script src="assets/plugins/sidemenu/sidemenu.js"></script>
    <!-- Theme Color js -->
    <script src="assets/js/themeColors.js"></script>
    <!-- CUSTOM JS -->
    <script src="assets/js/custom.js"></script>

    <script>
        function updateHex(input, displayId) {
            document.getElementById(displayId).textContent = input.value;
        }
    </script>
</body>

</html>