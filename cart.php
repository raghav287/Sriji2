<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'admin/db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
    <title>Cart - Sriji Vastra Shingar Sewa</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
        PAGE BANNER START
    ==========================-->
    <section class="page_banner" style="background: url(assets/images/background/breadcrumb-bg.jpg);">
        <div class="page_banner_overlay">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="page_banner_text wow fadeInUp">
                            <h1>Cart View</h1>
                            <ul>
                                <li><a href="#"><i class="fal fa-home-lg"></i> Home</a></li>
                                <li><a href="#">Cart View</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--=========================
        PAGE BANNER START
    ==========================-->


    <!--============================
        CART PAGE START
    =============================-->
    <section class="cart_page mt_100 mb_100">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 wow fadeInUp">
                    <div class="cart_table_area">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <!--<th class="cart_page_checkbox">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value=""
                                                    id="flexCheckDefault">
                                            </div>
                                        </th>-->
                                        <th class="cart_page_img">Product image </th>
                                        <th class="cart_page_details">Product Details</th>
                                        <th class="cart_page_price">Unit Price</th>
                                        <th class="cart_page_quantity">Quantity</th>
                                        <th class="cart_page_total">Subtotal</th>
                                        <th class="cart_page_action">action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch Cart Items
                                    // Reuse logic - ideally this should be a function in a common file
                                    $cart_items = [];
                                    $cart_total = 0;
                                    $multiplier = get_currency_multiplier();

                                    if (!isset($_SESSION['cart_session_id'])) {
                                        $_SESSION['cart_session_id'] = session_id();
                                    }
                                    $c_sess = $_SESSION['cart_session_id'];
                                    $c_user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

                                    $c_where = $c_user ? "c.user_id = $c_user" : "c.session_id = '$c_sess'";

                                    $cart_sql = "SELECT c.id as cart_id, c.quantity, p.name, p.id as product_id, 
                                                   pi.image_path, 
                                                   s.size, v.color, v.price as variant_price, 
                                                   (SELECT MIN(v2.price) FROM product_variants v2 JOIN product_sizes s2 ON v2.product_size_id = s2.id WHERE s2.product_id = p.id) as base_price
                                            FROM carts c 
                                            JOIN products p ON c.product_id = p.id 
                                            LEFT JOIN product_variants v ON c.variant_id = v.id 
                                            LEFT JOIN product_sizes s ON v.product_size_id = s.id
                                            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                                            WHERE $c_where
                                            GROUP BY c.id";

                                    $cart_res = $conn->query($cart_sql);
                                    if ($cart_res->num_rows > 0) {
                                        while ($item = $cart_res->fetch_assoc()) {
                                            $price = $item['variant_price'] ? $item['variant_price'] : $item['base_price'];
                                            if (!$price)
                                                $price = 0;

                                            // Apply centralized pricing logic (Zone Rules)
                                            $display_price = calculate_price($price, $item['size']);
                                            $subtotal = $display_price * $item['quantity'];

                                            $cart_total += $subtotal;
                                            $img = $item['image_path'] ? 'assets/uploads/products/' . $item['image_path'] : 'assets/images/no_image.png';
                                            ?>
                                    <tr id="cart_row_<?php echo $item['cart_id']; ?>">
                                        <!--
                                                <td class="cart_page_checkbox">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value=""
                                                            id="flexCheckDefault2">
                                                    </div>
                                                </td>
                                                -->
                                        <td class="cart_page_img">
                                            <div class="img">
                                                <img src="<?php echo htmlspecialchars($img); ?>" alt="Products"
                                                    class="img-fluid w-100">
                                            </div>
                                        </td>
                                        <td class="cart_page_details">
                                            <a class="title"
                                                href="shop-details.php?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a>
                                            <!--<p>$59.00 <del>$65.00</del></p>-->
                                            <?php if ($item['color']): ?>
                                            <span><b>Color:</b> <?php echo htmlspecialchars($item['color']); ?></span>
                                            <?php endif; ?>
                                            <?php if ($item['size']): ?>
                                            <span><b>Size:</b> <?php echo htmlspecialchars($item['size']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="cart_page_price">
                                            <h3>₹<?php echo number_format($display_price, 2); ?></h3>
                                        </td>
                                        <td class="cart_page_quantity">
                                            <div class="details_qty_input">
                                                <button class="minus"
                                                    onclick="updateCartQty(<?php echo $item['cart_id']; ?>, -1)"><i
                                                        class="fal fa-minus" aria-hidden="true"></i></button>
                                                <input type="text" value="<?php echo $item['quantity']; ?>" readonly>
                                                <button class="plus"
                                                    onclick="updateCartQty(<?php echo $item['cart_id']; ?>, 1)"><i
                                                        class="fal fa-plus" aria-hidden="true"></i></button>
                                            </div>
                                        </td>
                                        <td class="cart_page_total">
                                            <h3>₹<?php echo number_format($subtotal, 2); ?></h3>
                                        </td>
                                        <td class="cart_page_action">
                                            <a href="#"
                                                onclick="removeFromCart(<?php echo $item['cart_id']; ?>, event)"> <i
                                                    class="fal fa-times"></i> Remove</a>
                                        </td>
                                    </tr>
                                    <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="7" class="text-center">Your cart is empty. <a href="shop.php">Start Shopping</a></td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>

                            <script>
                            function updateCartQty(cartId, change) {
                                // Get current qty
                                // But simpler to just call backend 
                                // Need current qty... 
                                // Actually easier to just send update. Or fetch current from DOM.
                                const row = document.getElementById('cart_row_' + cartId);
                                const input = row.querySelector('input');
                                let newQty = parseInt(input.value) + change;
                                if (newQty < 1) return;

                                fetch('includes/cart_actions.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded'
                                        },
                                        body: `action=update&cart_id=${cartId}&quantity=${newQty}`
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            location.reload();
                                        }
                                    });
                            }

                            function removeFromCart(cartId, event) {
                                event.preventDefault();
                                if (!confirm('Are you sure you want to remove this item?')) return;

                                fetch('includes/cart_actions.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded'
                                        },
                                        body: `action=remove&cart_id=${cartId}`
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            location.reload();
                                        } else {
                                            alert('Failed to remove item');
                                        }
                                    })
                                    .catch(err => console.error(err));
                            }
                            </script>
                        </div>

                    </div>
                </div>
                <div class="col-lg-4 col-md-9 wow fadeInRight">
                    <div id="sticky_sidebar">
                        <div class="cart_page_summary">
                            <h3>Billing summary</h3>
                            <ul>
                                <?php if (isset($cart_res) && $cart_res->num_rows > 0):
                                    $cart_res->data_seek(0); // Reset pointer
                                    while ($item = $cart_res->fetch_assoc()):
                                        $price = $item['variant_price'] ? $item['variant_price'] : $item['base_price'];
                                        if (!$price)
                                            $price = 0;
                                        $display_price = calculate_price($price, $item['size']); // Recalculate
                                        $img = $item['image_path'] ? 'assets/uploads/products/' . $item['image_path'] : 'assets/images/no_image.png';
                                        ?>
                                <li>
                                    <a class="img" href="#">
                                        <img src="<?php echo htmlspecialchars($img); ?>" alt="Products"
                                            class="img-fluid w-100">
                                    </a>
                                    <div class="text">
                                        <a class="title"
                                            href="shop-details.php?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a>
                                        <p>₹<?php echo number_format($display_price, 2); ?> ×
                                            <?php echo $item['quantity']; ?>
                                        </p>
                                        <p>Color: <?php echo $item['color']; ?>, Size: <?php echo $item['size']; ?></p>
                                    </div>
                                </li>
                                <?php endwhile; endif; ?>
                            </ul>

                            <h6>subtotal <span>₹<?php echo number_format($cart_total, 2); ?></span></h6>
                            <!--<h6>Tax <span>(+) $100.00</span></h6>-->
                            <!--<h6>Discount <span>(-) $45.00</span></h6>-->
                            <h4>Total <span>₹<?php echo number_format($cart_total, 2); ?></span></h4>

                            <!-- Coupon removed as requested -->
                        </div>
                        <div class="cart_summary_btn">
                            <a class="common_btn continue_shopping" href="shop.php">Continue shopping</a>
                            <a class="common_btn" href="checkout.php">checkout <i
                                    class="fas fa-long-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--============================
        CART PAGE END
    =============================-->


    <!--=========================
        FOOTER 2 START
    ==========================-->
    <?php include("includes/footer.php") ?>
    <!--=========================
        FOOTER 2 END
    ==========================-->

    <a href="https://wa.me/919115936593" target="_blank"
        class="position-fixed d-flex align-items-center justify-content-center" style="
      bottom:25px;
      right:25px;
      padding:10px;
      width:65px;
      height:65px;
      background-color:#25D366;
      border-radius:50%;
      box-shadow:0 8px 20px rgba(0,0,0,0.2);
      z-index:999;
      transition:all 0.3s ease;
   " onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
        <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/WhatsApp_icon.png" width="32" alt="WhatsApp">
    </a>
    <!--==========================
        SCROLL BUTTON START
    ===========================-->
    <!-- <div class="progress-wrap">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </div> -->
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