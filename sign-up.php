<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
    <title>Sign Up - Sriji Vastra Shingar Sewa</title>
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/mobile_menu.css">
    <link rel="stylesheet" href="assets/css/nice-select.css">
    <link rel="stylesheet" href="assets/css/scroll_button.css">
    <link rel="stylesheet" href="assets/css/slick.css">
    <link rel="stylesheet" href="assets/css/venobox.min.css">
    <link rel="stylesheet" href="assets/css/select2.min.css">
    <link rel="stylesheet" href="assets/css/jquery.pwstabs.css">
    <link rel="stylesheet" href="assets/css/range_slider.css">
    <link rel="stylesheet" href="assets/css/multiple-image-video.css">
    <link rel="stylesheet" href="assets/css/animated_barfiller.css">
    <link rel="stylesheet" href="assets/css/custom_spacing.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>

<body class="default_home">

    <!--=========================
        HEADER START
    ==========================-->
    <?php include("includes/header.php") ?>
    <!--=========================
        HEADER END
    ==========================-->


    <!--=========================
        SIGN UP PAGE START
    ==========================-->
    <!--=========================
        SIGN UP PAGE START
    ==========================-->
    <?php
    require_once 'admin/db.php';
    $msg = '';
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $name = trim($first_name . ' ' . $last_name);
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $c_password = $_POST['c_password'] ?? '';

        if ($password !== $c_password) {
            $msg = '<div class="alert alert-danger">Passwords do not match!</div>';
        } else {
            // Check if email or phone exists
            $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
            $check->bind_param("ss", $email, $phone);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $msg = '<div class="alert alert-danger">Email or Phone already exists!</div>';
            } else {
                $hashed_pass = password_hash($password, PASSWORD_BCRYPT);
                $ins = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
                $ins->bind_param("ssss", $name, $email, $phone, $hashed_pass);
                if ($ins->execute()) {
                    $msg = '<div class="alert alert-success">Registration Successful! <a href="sign-in.php">Login here</a></div>';
                } else {
                    $msg = '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
                }
            }
        }
    }
    ?>
    <section class="sign_up mt_100 mb_100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-3 col-lg-4 col-xl-4 d-none d-lg-block wow fadeInLeft">
                    <div class="sign_in_img">
                        <img src="assets/images/background/sign-up.jpg" alt="Sign In" class="img-fluid w-100">
                    </div>
                </div>
                <div class="col-xxl-5 col-lg-8 col-xl-6 col-md-10 wow fadeInRight">
                    <div class="sign_in_form">
                        <h3>Sign Up to Continue 👋</h3>
                        <?php echo $msg; ?>
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="single_input">
                                        <label>First Name</label>
                                        <input type="text" name="first_name" placeholder="First Name" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="single_input">
                                        <label>Last Name</label>
                                        <input type="text" name="last_name" placeholder="Last Name" required>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="single_input">
                                        <label>Email</label>
                                        <input type="email" name="email" placeholder="example@gmail.com" required>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="single_input">
                                        <label>Phone</label>
                                        <input type="text" name="phone" placeholder="9876543210" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="single_input">
                                        <label>Password</label>
                                        <input type="password" name="password" placeholder="********" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="single_input">
                                        <label>Confirm Password</label>
                                        <input type="password" name="c_password" placeholder="********" required>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <button type="submit" class="common_btn">Sign Up <i
                                            class="fas fa-long-arrow-right"></i></button>
                                </div>
                            </div>
                        </form>

                        <p class="dont_account">Already have an account? <a href="sign-in.php">Sign In</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--=========================
        SIGN UP PAGE END
    ==========================-->


    <!--=========================
        FOOTER 2 START
    ==========================-->
    <?php include("includes/footer.php") ?>
    <!--=========================
        FOOTER 2 END
    ==========================-->


    <!--==========================
        SCROLL BUTTON START
    ===========================-->
    <div class="progress-wrap">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </div>
    <!--==========================
        SCROLL BUTTON END
    ===========================-->


    <!--jquery library js-->
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <!--bootstrap js-->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <!--font-awesome js-->
    <script src="assets/js/Font-Awesome.js"></script>
    <!--counter js-->
    <script src="assets/js/jquery.waypoints.min.js"></script>
    <script src="assets/js/jquery.countup.min.js"></script>
    <!--nice select js-->
    <script src="assets/js/jquery.nice-select.min.js"></script>
    <!--select 2 js-->
    <script src="assets/js/select2.min.js"></script>
    <!--simply countdown js-->
    <script src="assets/js/simplyCountdown.js"></script>
    <!--slick slider js-->
    <script src="assets/js/slick.min.js"></script>
    <!--venobox js-->
    <script src="assets/js/venobox.min.js"></script>
    <!--wow js-->
    <script src="assets/js/wow.min.js"></script>
    <!--marquee js-->
    <script src="assets/js/jquery.marquee.min.js"></script>
    <!--pws tabs js-->
    <script src="assets/js/jquery.pwstabs.min.js"></script>
    <!--scroll button js-->
    <script src="assets/js/scroll_button.js"></script>
    <!--youtube background js-->
    <script src="assets/js/jquery.youtube-background.min.js"></script>
    <!--range slider js-->
    <script src="assets/js/range_slider.js"></script>
    <!--sticky sidebar js-->
    <script src="assets/js/sticky_sidebar.js"></script>
    <!--multiple image upload js-->
    <script src="assets/js/multiple-image-video.js"></script>
    <!--animated barfiller js-->
    <script src="assets/js/animated_barfiller.js"></script>
    <!--main/custom js-->
    <script src="assets/js/custom.js"></script>

</body>

</html>