<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Address - Sriji Vastra Shingar Sewa</title>
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
    $addr_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $msg = '';

    $name = '';
    $email = '';
    $phone = '';
    $country = '';
    $state = '';
    $city = '';
    $zip_code = '';
    $address = '';
    $type = 'Home';

    // Fetch if edit
    if ($addr_id > 0) {
        $stmt = $conn->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $addr_id, $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $name = $row['name'];
            $email = $row['email'];
            $phone = $row['phone'];
            $country = $row['country'];
            $state = $row['state'];
            $city = $row['city'];
            $zip_code = $row['zip_code'];
            $address = $row['address'];
            if (isset($row['type']))
                $type = $row['type'];
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $country = $_POST['country'];
        $state = $_POST['state'];
        $city = $_POST['city'];
        $zip_code = $_POST['zip_code'];
        $address = $_POST['address'];
        $type = $_POST['type'] ?? 'Home';

        if ($addr_id > 0) {
            $upd = $conn->prepare("UPDATE user_addresses SET name=?, email=?, phone=?, country=?, state=?, city=?, zip_code=?, address=?, type=? WHERE id=? AND user_id=?");
            $upd->bind_param("sssssssssii", $name, $email, $phone, $country, $state, $city, $zip_code, $address, $type, $addr_id, $user_id);
            if ($upd->execute()) {
                header("Location: address.php");
                exit;
            } else {
                $msg = '<div class="alert alert-danger">Error updating address.</div>';
            }
        } else {
            $ins = $conn->prepare("INSERT INTO user_addresses (user_id, name, email, phone, country, state, city, zip_code, address, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->bind_param("isssssssss", $user_id, $name, $email, $phone, $country, $state, $city, $zip_code, $address, $type);
            if ($ins->execute()) {
                header("Location: address.php");
                exit;
            } else {
                $msg = '<div class="alert alert-danger">Error adding address.</div>';
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
                            <h1><?php echo $addr_id ? 'Edit' : 'Add'; ?> Address</h1>
                            <ul>
                                <li><a href="dashboard.php">Dashboard</a></li>
                                <li><a href="address.php">Address</a></li>
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
                        <h3 class="dashboard_title"><?php echo $addr_id ? 'Edit' : 'Add New'; ?> Address</h3>
                        <?php echo $msg; ?>
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="single_input">
                                        <label>Name</label>
                                        <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="single_input">
                                        <label>Email</label>
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="single_input">
                                        <label>Phone</label>
                                        <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="single_input">
                                        <label>Type (Home/Office)</label>
                                        <select name="type" class="select_2">
                                            <option value="Home" <?php if ($type == 'Home')
                                                echo 'selected'; ?>>Home
                                            </option>
                                            <option value="Office" <?php if ($type == 'Office')
                                                echo 'selected'; ?>>Office
                                            </option>
                                            <option value="Other" <?php if ($type == 'Other')
                                                echo 'selected'; ?>>Other
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="single_input">
                                        <label>Address</label>
                                        <textarea name="address"
                                            rows="3"><?php echo htmlspecialchars($address); ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="single_input">
                                        <label>Country</label>
                                        <input type="text" name="country"
                                            value="<?php echo htmlspecialchars($country); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="single_input">
                                        <label>State</label>
                                        <input type="text" name="state" value="<?php echo htmlspecialchars($state); ?>"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="single_input">
                                        <label>City</label>
                                        <input type="text" name="city" value="<?php echo htmlspecialchars($city); ?>"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="single_input">
                                        <label>Zip Code</label>
                                        <input type="text" name="zip_code"
                                            value="<?php echo htmlspecialchars($zip_code); ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-4">
                                    <button type="submit" class="common_btn"><?php echo $addr_id ? 'Update' : 'Save'; ?>
                                        Address</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include("../includes/footer.php") ?>

    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/Font-Awesome.js"></script>
    <script src="../assets/js/jquery.nice-select.min.js"></script>
    <script src="../assets/js/select2.min.js"></script>
    <script src="../assets/js/slick.min.js"></script>
    <script src="../assets/js/venobox.min.js"></script>
    <script src="../assets/js/wow.min.js"></script>
    <script src="../assets/js/custom.js"></script>
</body>

</html>