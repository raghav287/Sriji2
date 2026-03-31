<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
    <title>Change Password - Sriji Vastra Shingar Sewa</title>
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

    <?php include("../includes/header.php") ?>

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
    $msg = '';

    // Fetch current info
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $current_hash = $user['password'];

    // Check if using default password 'User@123'
    $is_default_pass = false;
    if (password_verify('User@123', $current_hash)) {
        $is_default_pass = true;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $current_pass = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        $valid = true;

        if (!$is_default_pass) {
            if (!password_verify($current_pass, $current_hash)) {
                $msg = '<div class="alert alert-danger">Current password is incorrect.</div>';
                $valid = false;
            }
        }

        if ($valid) {
            if ($new_pass !== $confirm_pass) {
                $msg = '<div class="alert alert-danger">New passwords do not match.</div>';
            } elseif (strlen($new_pass) < 6) {
                $msg = '<div class="alert alert-danger">Password must be at least 6 characters.</div>';
            } else {
                $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
                $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $upd->bind_param("si", $new_hash, $user_id);
                if ($upd->execute()) {
                    $msg = '<div class="alert alert-success">Password updated successfully!</div>';
                    // Update current hash in memory so the form updates state if needed
                    $current_hash = $new_hash;
                    $is_default_pass = false;
                } else {
                    $msg = '<div class="alert alert-danger">Error updating password.</div>';
                }
            }
        }
    }
    ?>

    <section class="page_banner" style="background: url(../assets/images/page_banner_bg.jpg);">
        <div class="page_banner_overlay">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="page_banner_text wow fadeInUp">
                            <h1>My Account</h1>
                            <ul>
                                <li><a href="dashboard.php">Dashboard</a></li>
                                <li><a href="#">Change Password</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard mb_100">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 wow fadeInUp">
                    <?php include("includes/dashboard-sidebar.php") ?>
                </div>
                <div class="col-lg-9 wow fadeInRight">
                    <div class="dashboard_content mt_100">
                        <h3 class="dashboard_title">Change Password</h3>
                        <?php echo $msg; ?>

                        <?php if ($is_default_pass): ?>
                            <div class="alert alert-info">It looks like you are using a temporary password. Please set a new
                                secure password.</div>
                        <?php endif; ?>

                        <div class="dashboard_profile_info">
                            <form method="POST">
                                <div class="row">
                                    <?php if (!$is_default_pass): ?>
                                        <div class="col-md-12">
                                            <div class="single_input">
                                                <label>Current Password</label>
                                                <input type="password" name="current_password" required
                                                    placeholder="Current Password">
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="col-md-6">
                                        <div class="single_input">
                                            <label>New Password</label>
                                            <input type="password" name="new_password" required
                                                placeholder="New Password">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single_input">
                                            <label>Confirm Password</label>
                                            <input type="password" name="confirm_password" required
                                                placeholder="Confirm Password">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="common_btn">Update Password</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include("../includes/footer.php") ?>

    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/jquery.nice-select.min.js"></script>
    <script src="../assets/js/select2.min.js"></script>
    <script src="../assets/js/slick.min.js"></script>
    <script src="../assets/js/wow.min.js"></script>
    <script src="../assets/js/custom.js"></script>
</body>

</html>