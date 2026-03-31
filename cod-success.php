<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
    <title>Order Placed - Sriji Vastra Shingar Sewa</title>
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

    <?php include 'includes/header.php'; ?>

    <section class="payment_success mt_100 mb_100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-6 col-md-10 col-lg-8 col-xxl-5 wow fadeInUp">
                    <div class="payment_success_text">
                        <div class="img">
                            <img src="assets/images/payment_success_img.png" alt="success" class="img-fluid w-100">
                        </div>
                        <h3>Order Placed Successfully</h3>
                        <p>Thank you for your order! Your order has been placed successfully.</p>
                        <?php if (isset($_GET['order_number'])): ?>
                            <p>Order ID: <b><?php echo htmlspecialchars($_GET['order_number']); ?></b></p>
                        <?php endif; ?>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle"></i>
                            Please pay the
                            <strong>₹<?php echo isset($_GET['amount']) ? number_format($_GET['amount'], 2) : '0.00'; ?></strong>
                            amount at the time of delivery.
                        </div>

                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-3 mt-4">
                            <a href="index.php" class="common_btn go_btn">
                                Go to Home
                            </a>
                            <a href="shop.php" class="common_btn">
                                Continue Shopping
                                <i class="fas fa-long-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include("includes/footer.php") ?>

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/Font-Awesome.js"></script>
    <script src="assets/js/custom.js"></script>

</body>

</html>