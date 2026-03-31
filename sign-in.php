<?php
require_once 'admin/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$msg = '';
$otp_sent = false;
$phone_for_otp = '';

// Handle Login Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_type = $_POST['login_type'] ?? 'password';

    if ($login_type === 'password') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $chk = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $chk->bind_param("s", $email);
        $chk->execute();
        $res = $chk->get_result();
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
                header("Location: user/dashboard.php");
                exit;
            } else {
                $msg = '<div class="alert alert-danger">Invalid Password!</div>';
            }
        } else {
            $msg = '<div class="alert alert-danger">User not found!</div>';
        }

    } elseif ($login_type === 'send_otp') {
        $email = $_POST['email'] ?? '';
        $chk = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
        $chk->bind_param("s", $email);
        $chk->execute();
        $u_res = $chk->get_result();

        if ($u_res->num_rows > 0) {
            $row_user = $u_res->fetch_assoc();
            // Generate OTP
            $otp = rand(100000, 999999);
            $_SESSION['login_otp'] = $otp;
            $_SESSION['login_email'] = $email;

            // Send OTP via Email
            require_once 'includes/smtp_mailer.php';
            $smtp_host = 'smtp.hostinger.com';
            $smtp_port = 465;
            $smtp_user = 'no-reply@srijivastrashingarsewa.com';
            $smtp_pass = 'e$|KaxO1O';
            $from_name = 'Sriji Vastra Shingar Sewa';

            $mailer = new SMTPMailer($smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_user, $from_name);

            $subject = "Your Login OTP - Sriji Vastra Shingar Sewa";
            $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-top: 3px solid #e53637;'>
                    <h2 style='color: #e53637; text-align: center;'>Login OTP</h2>
                    <p>Hello <strong>" . htmlspecialchars($row_user['name']) . "</strong>,</p>
                    <p>You requested a login OTP for your Sriji Vastra Shingar Sewa account.</p>
                    <div style='background: #f9f9f9; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; margin: 20px 0; border-radius: 5px;'>
                        $otp
                    </div>
                    <p>This OTP is valid for specific time usage. Please do not share it with anyone.</p>
                    <p style='margin-top: 30px; font-size: 12px; color: #777; text-align: center;'>If you did not request this, please ignore this email.</p>
                </div>
            ";

            if ($mailer->send($email, $subject, $body)) {
                $otp_sent = true;
                $phone_for_otp = $email; // Reuse variable name or rename conceptually
                $msg = '<div class="alert alert-success">OTP sent to your email!</div>';
            } else {
                $msg = '<div class="alert alert-danger">Failed to send OTP email. Please try again.</div>';
            }
        } else {
            $msg = '<div class="alert alert-danger">Email address not registered!</div>';
        }

    } elseif ($login_type === 'verify_otp') {
        $otp_input = $_POST['otp'] ?? '';
        $email = $_SESSION['login_email'] ?? '';

        if ($otp_input == $_SESSION['login_otp'] && $email) {
            // Fetch User
            $chk = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
            $chk->bind_param("s", $email);
            $chk->execute();
            $res = $chk->get_result();
            $row = $res->fetch_assoc();

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];

            // Clear OTP session
            unset($_SESSION['login_otp']);
            unset($_SESSION['login_email']);

            header("Location: user/dashboard.php");
            exit;
        } else {
            $msg = '<div class="alert alert-danger">Invalid OTP!</div>';
            $otp_sent = true;
            $phone_for_otp = $email;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
    <title>Sign In - Sriji Vastra Shingar Sewa</title>
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
    <style>
        .login_tab_nav {
            display: flex;
            justify-content: center;
            background: #f5f5f5;
            padding: 5px;
            border-radius: 50px;
            margin-bottom: 25px;
            border: 1px solid #eee;
        }

        .login_tab_nav .nav-item {
            flex: 1;
            text-align: center;
        }

        .login_tab_nav .nav-link {
            width: 100%;
            border-radius: 50px;
            color: #555;
            font-weight: 600;
            transition: all 0.3s;
            padding: 10px 15px;
            background: transparent;
        }

        .login_tab_nav .nav-link:hover {
            color: #e53637;
        }

        .login_tab_nav .nav-link.active {
            background: #e53637;
            color: #fff;
            box-shadow: 0 4px 10px rgba(229, 54, 55, 0.3);
        }
    </style>
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
        SIGN IN PAGE START
    ==========================-->
    <!--=========================
        SIGN IN PAGE START
    ==========================-->

    <section class="sign_in mt_100 mb_100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-3 col-lg-4 col-xl-4 d-none d-lg-block wow fadeInLeft">
                    <div class="sign_in_img">
                        <img src="assets/images/background/sign-in.jpg" alt="Sign In" class="img-fluid w-100">
                    </div>
                </div>
                <div class="col-xxl-4 col-lg-7 col-xl-6 col-md-10 wow fadeInRight">
                    <div class="sign_in_form">
                        <h3>Sign In to Continue 👋</h3>
                        <?php echo $msg; ?>

                        <!-- Toggle Buttons -->
                        <ul class="nav nav-pills mb-3 login_tab_nav" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo !$otp_sent ? 'active' : ''; ?>" id="pills-email-tab"
                                    data-bs-toggle="pill" data-bs-target="#pills-email" type="button"
                                    role="tab">Password</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo $otp_sent ? 'active' : ''; ?>" id="pills-otp-tab"
                                    data-bs-toggle="pill" data-bs-target="#pills-otp" type="button"
                                    role="tab">OTP</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="pills-tabContent">
                            <!-- Email Login -->
                            <div class="tab-pane fade <?php echo !$otp_sent ? 'show active' : ''; ?>" id="pills-email"
                                role="tabpanel">
                                <form method="POST">
                                    <input type="hidden" name="login_type" value="password">
                                    <div class="single_input">
                                        <label>Email</label>
                                        <input type="email" name="email" placeholder="example@gmail.com" required>
                                    </div>
                                    <div class="single_input">
                                        <label>Password</label>
                                        <input type="password" name="password" placeholder="********" required>
                                    </div>
                                    <button type="submit" class="common_btn mt-3">Sign In <i
                                            class="fas fa-long-arrow-right"></i></button>
                                </form>
                            </div>

                            <!-- OTP Login -->
                            <div class="tab-pane fade <?php echo $otp_sent ? 'show active' : ''; ?>" id="pills-otp"
                                role="tabpanel">
                                <?php if (!$otp_sent): ?>
                                    <form method="POST">
                                        <input type="hidden" name="login_type" value="send_otp">
                                        <div class="single_input">
                                            <label>Email Address</label>
                                            <input type="email" name="email" placeholder="example@gmail.com" required>
                                        </div>
                                        <button type="submit" class="common_btn mt-3">Send OTP <i
                                                class="fas fa-paper-plane"></i></button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST">
                                        <input type="hidden" name="login_type" value="verify_otp">
                                        <p>OTP sent to: <strong><?php echo htmlspecialchars($phone_for_otp); ?></strong></p>
                                        <div class="single_input">
                                            <label>Enter OTP</label>
                                            <input type="text" name="otp" placeholder="123456" required>
                                        </div>
                                        <button type="submit" class="common_btn mt-3">Verify & Login <i
                                                class="fas fa-check"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <p class="dont_account mt-4">Don't have an account? <a href="sign-up.php">Sign Up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--=========================
        SIGN IN PAGE END
    ==========================-->


    <!--=========================
        FOOTER 2 START
    ==========================-->
    <footer class="footer_2 pt_100" style="background: url(assets/images/footer_2_bg_2.jpg);">
        <div class="container">
            <div class="row justify-content-between">
                <div class="col-xl-3 col-md-6 col-lg-3 wow fadeInUp" data-wow-delay=".7s">
                    <div class="footer_2_logo_area">
                        <a class="footer_logo" href="index.html">
                            <img src="assets/images/footer_logo_2.png" alt="Zenis" class="img-fluid w-100">
                        </a>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Sequi, distinctio molestiae error
                            ullam obcaecati dolorem inventore.</p>
                        <ul>
                            <li><span>Follow :</span></li>
                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                            <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                            <li><a href="#"><i class="fab fa-google-plus-g"></i></a></li>
                            <li><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-xl-2 col-sm-6 col-md-4 col-lg-2 wow fadeInUp" data-wow-delay="1s">
                    <div class="footer_link">
                        <h3>Company</h3>
                        <ul>
                            <li><a href="#about">About us</a></li>
                            <li><a href="#">Contact Us</a></li>
                            <li><a href="#">Affiliate</a></li>
                            <li><a href="#">Career</a></li>
                            <li><a href="#">Latest News</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-xl-2 col-sm-6 col-md-4 col-lg-2 wow fadeInUp" data-wow-delay="1.3s">
                    <div class="footer_link">
                        <h3>Category</h3>
                        <ul>
                            <li><a href="#">Men’s Fashion</a></li>
                            <li><a href="#">denim Collection</a></li>
                            <li><a href="#">western wear</a></li>
                            <li><a href="#">sport wear</a></li>
                            <li><a href="#">fashion jewellery</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-xl-2 col-sm-6 col-md-4 col-lg-2 wow fadeInUp" data-wow-delay="1.6s">
                    <div class="footer_link">
                        <h3>Quick Links</h3>
                        <ul>
                            <li><a href="#">Privacy Ploicy</a></li>
                            <li><a href="#">Terms and Condition</a></li>
                            <li><a href="#">Return Policy</a></li>
                            <li><a href="#">FAQ's</a></li>
                            <li><a href="#">Become a Vendor</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-md-4 col-lg-3 wow fadeInUp" data-wow-delay="1.9s">
                    <div class="footer_link footer_logo_area">
                        <h3>Contact Us</h3>
                        <p>It is a long established fact that reader distracted looking layout It is a long established
                            fact.</p>
                        <span>
                            <b><img src="assets/images/location_icon_white.png" alt="Map" class="img-fluid"></b>
                            37 W 24th St, New York, NY</span>
                        <span>
                            <b><img src="assets/images/phone_icon_white.png" alt="Call" class="img-fluid"></b>
                            <a href="callto:+123324587939">+123 324 5879 39</a>
                        </span>
                        <span>
                            <b><img src="assets/images/mail_icon_white.png" alt="Mail" class="img-fluid"></b>
                            <a href="mailto:support@mail.com">info@Zenis.com</a>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="footer_copyright mt_75">
                        <p>Copyright @ <b>Zenis</b> 2025. All right reserved.</p>
                        <ul class="payment">
                            <li>Payment by :</li>
                            <li>
                                <img src="assets/images/footer_payment_icon_1.jpg" alt="payment"
                                    class="img-fluid w-100">
                            </li>
                            <li>
                                <img src="assets/images/footer_payment_icon_2.jpg" alt="payment"
                                    class="img-fluid w-100">
                            </li>
                            <li>
                                <img src="assets/images/footer_payment_icon_3.jpg" alt="payment"
                                    class="img-fluid w-100">
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>
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