<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'admin/db.php';
include 'includes/razorpay_config.php';
include 'includes/paypal_config.php';
require_once 'includes/price_helper.php';
// ... rest of header code

// --- Cart Logic (Similar to included header/cart actions) ---
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
$cart_items = [];
$cart_total = 0;
$multiplier = get_currency_multiplier();

// Fetch Site Settings
$settings_res = $conn->query("SELECT * FROM site_settings");
$site_settings = [];
if ($settings_res) {
    while ($r = $settings_res->fetch_assoc()) {
        $site_settings[$r['setting_key']] = $r['setting_value'];
    }
}

// Defaults
$s_cap = floatval($site_settings['shipping_cap'] ?? 500) * $multiplier;
$s_below = floatval($site_settings['shipping_charge_below'] ?? 50) * $multiplier;
$s_above = floatval($site_settings['shipping_charge_above'] ?? 0) * $multiplier;

$cod_active = ($site_settings['cod_active'] ?? '1') == '1';
// Fetch COD settings
$c_cap = floatval($site_settings['cod_limit'] ?? 500) * $multiplier;
$c_below = floatval($site_settings['cod_charge_below'] ?? ($site_settings['cod_charge'] ?? 20)) * $multiplier;
$c_above = floatval($site_settings['cod_charge_above'] ?? 0) * $multiplier;


if ($cart_res->num_rows > 0) {
    while ($item = $cart_res->fetch_assoc()) {
        $price = $item['variant_price'] ? $item['variant_price'] : $item['base_price'];
        if (!$price)
            $price = 0;

        // Use centralized pricing helper to respect Zone Rules + Multiplier
        $item['final_price'] = calculate_price($price, $item['size']); 
        $item['subtotal'] = $item['final_price'] * $item['quantity'];
        $cart_total += $item['subtotal'];
        $item['image'] = $item['image_path'] ? 'assets/uploads/products/' . $item['image_path'] : 'assets/images/no_image.png';
        $cart_items[] = $item;
    }
}

// Shipping Cost Calculation
$shipping_cost = ($cart_total < $s_cap) ? $s_below : $s_above;
// COD Cost Calculation
$cod_charge = ($cart_total < $c_cap) ? $c_below : $c_above;

$total_with_shipping = $cart_total + $shipping_cost;

// Ensure cart is not empty
if (empty($cart_items)) {
    header("Location: shop.php");
    exit;
}

// User Addresses
$saved_addresses = [];
if ($c_user) {
    $addr_sql = "SELECT * FROM user_addresses WHERE user_id = $c_user";
    $addr_res = $conn->query($addr_sql);
    while ($row = $addr_res->fetch_assoc()) {
        $saved_addresses[] = $row;
    }
}
// Fetch Active Countries for Dropdown
$countries_res = $conn->query("SELECT * FROM countries WHERE status='active' ORDER BY name ASC");
$active_countries = [];
while ($cr = $countries_res->fetch_assoc()) {
    $active_countries[] = $cr;
}

// Current Selected Country (from Session or Default)
$current_country_code = $_SESSION['selected_country_code'] ?? 'IN'; // Default India
$current_country_id = $_SESSION['selected_country_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
    <title>Checkout - Sriji Vastra Shingar Sewa</title>
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
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 50px;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
        }
    </style>
    <!-- PayPal SDK -->
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=USD"></script>

</head>

<body>

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
                            <h1>Checkout</h1>
                            <ul>
                                <li><a href="index.php"><i class="fal fa-home-lg"></i> Home</a></li>
                                <li><a href="#">Checkout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--============================
        CHECKOUT START
    =============================-->
    <section class="checkout_page mt_100 mb_100">
        <div class="container">
            <!-- Form to Submit Order -->
            <form action="submit_order.php" method="POST" id="checkoutForm">
                <div class="row">
                    <div class="col-lg-8 wow fadeInUp">
                        <div class="checkout_header">
                            <h3>Shipping Information</h3>
                            <?php if ($c_user && isset($_SESSION['user_name'])): ?>
                                <p>Logged in as: <b><?php echo htmlspecialchars($_SESSION['user_name']); ?></b></p>
                            <?php else: ?>
                                <p>Already have an account? <a href="sign-in.php">Login here</a></p>
                            <?php endif; ?>
                        </div>

                        <div class="checkout_address_area">
                            <!-- Saved Addresses (Radio) -->
                            <?php if (!empty($saved_addresses)): ?>
                                <div class="row mb-4">
                                    <h6>Select a saved address:</h6>
                                    <?php foreach ($saved_addresses as $index => $addr): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="checkout_single_address border p-3 rounded">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="selected_address"
                                                        id="addr_<?php echo $addr['id']; ?>" value="<?php echo $addr['id']; ?>"
                                                        <?php echo $index === 0 ? 'checked' : ''; ?>
                                                        onchange="toggleNewAddress(false)">
                                                    <label class="form-check-label" for="addr_<?php echo $addr['id']; ?>">
                                                        <strong><?php echo htmlspecialchars($addr['name'] ?? 'Address #' . ($index + 1)); ?></strong><br>
                                                        <?php echo htmlspecialchars($addr['address']); ?><br>
                                                        <?php echo htmlspecialchars($addr['city'] . ', ' . $addr['state'] . ', ' . $addr['country']); ?><br>
                                                        Phone: <?php echo htmlspecialchars($addr['phone']); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="selected_address"
                                                id="addr_new" value="new" onchange="toggleNewAddress(true)">
                                            <label class="form-check-label" for="addr_new">
                                                <b>Ship to a new address</b>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="selected_address" value="new">
                            <?php endif; ?>

                            <!-- New Address Form -->
                            <div id="new_address_form"
                                style="<?php echo !empty($saved_addresses) ? 'display:none;' : ''; ?>">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="single_input">
                                            <label>Full Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" placeholder="John Doe" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single_input">
                                            <label>Email Address <span class="text-danger">*</span></label>
                                            <input type="email" name="email" placeholder="example@email.com" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="single_input">
                                            <label>Phone Number <span class="text-danger">*</span></label>
                                            <input type="text" name="phone" placeholder="+91 1234567890" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6"></div> <!-- Spacer -->

                                    <div class="col-md-4">
                                        <div class="single_input">
                                            <label>Country <span class="text-danger">*</span></label>
                                            <select class="select_2 w-100" name="country" id="countryId" required>
                                                <option value="">Select Country</option>
                                                <?php foreach ($active_countries as $country): ?>
                                                    <option value="<?php echo htmlspecialchars($country['name']); ?>" 
                                                            data-id="<?php echo $country['id']; ?>"
                                                            data-code="<?php echo $country['code']; ?>"
                                                            <?php echo ($current_country_id == $country['id'] || $current_country_code == $country['code'] || ($country['name'] == 'India' && $current_country_code == 'IN')) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($country['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="single_input">
                                            <label>State <span class="text-danger">*</span></label>
                                            <select class="select_2 w-100" name="state" id="stateId" required>
                                                <option value="">Select State</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="single_input">
                                            <label>City <span class="text-danger">*</span></label>
                                            <select class="select_2 w-100" name="city" id="cityId" required>
                                                <option value="">Select City</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="single_input">
                                            <label>Zip/Postal Code <span class="text-danger">*</span></label>
                                            <input type="text" name="zip_code" placeholder="123456" required>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="single_input">
                                            <label>Address <span class="text-danger">*</span></label>
                                            <textarea rows="3" name="address"
                                                placeholder="Street address, house number..." required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-12 mt-3">
                                <div class="single_input">
                                    <label>Order notes (optional)</label>
                                    <textarea rows="2" name="order_notes"
                                        placeholder="Notes about your order, e.g. special notes for delivery."></textarea>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-lg-4 wow fadeInRight">
                        <div class="cart_page_summary">
                            <h3>Billing summary</h3>
                            <ul>
                                <?php foreach ($cart_items as $item): ?>
                                    <li>
                                        <a class="img" href="#">
                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="product"
                                                class="img-fluid w-100">
                                        </a>
                                        <div class="text">
                                            <a class="title" href="#"><?php echo htmlspecialchars($item['name']); ?></a>
                                            <p>₹<?php echo number_format($item['final_price'], 2); ?> ×
                                                <?php echo $item['quantity']; ?>
                                            </p>
                                            <?php if ($item['size'] || $item['color']): ?>
                                                <p><?php echo $item['color'] . ($item['color'] && $item['size'] ? ', ' : '') . $item['size']; ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <h6>subtotal <span>₹<?php echo number_format($cart_total, 2); ?></span></h6>
                            <h6>Shipping
                                <span><?php echo $shipping_cost > 0 ? '₹' . number_format($shipping_cost, 2) : 'Free'; ?></span>
                            </h6>

                            <h6 id="cod_fee_display" style="display: <?php echo $cod_active ? 'flex' : 'none'; ?>;">COD
                                Fee <span>₹<?php echo number_format($cod_charge, 2); ?></span></h6>

                            <h4 id="total_display_area">Total
                                <span>₹<?php echo number_format($total_with_shipping + ($cod_active ? $cod_charge : 0), 2); ?></span>
                            </h4>

                            <!-- Hidden inputs for JS/Form -->
                            <input type="hidden" id="base_total" value="<?php echo $total_with_shipping; ?>">
                            <input type="hidden" id="cod_charge_val" value="<?php echo $cod_charge; ?>">

                            <div class="checkout_payment">
                                <h3>payment method</h3>
                                <?php if ($cod_active): ?>
                                    <div class="form-check" id="cod_payment_section">
                                        <input class="form-check-input payment-radio" type="radio" name="payment_method"
                                            id="cod" value="COD" checked>
                                        <label class="form-check-label" for="cod">
                                            Cash on Delivery
                                            <?php if ($cod_charge > 0)
                                                echo "(+₹" . number_format($cod_charge, 2) . ")"; ?>
                                        </label>
                                    </div>
                                <?php endif; ?>
                                <div class="form-check" id="online_payment_section">
                                    <input class="form-check-input payment-radio" type="radio" name="payment_method"
                                        id="online" value="Online" <?php echo !$cod_active ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="online">
                                        Online Payment
                                    </label>
                                </div>
                                <div class="form-check" id="paypal_option" style="display:none;">
                                    <div id="paypal-button-container"></div>
                                </div>

                                <button type="submit" class="common_btn">Place order <i
                                        class="fas fa-long-arrow-right"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <!--============================
        CHECKOUT END
    =============================-->

    <?php include("includes/footer.php") ?>

    <!--jquery library js-->
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/Font-Awesome.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="assets/js/custom.js"></script>

    <script>
        // ... (Existing scripts can stay or be part of this)
        // We will move the submit logic to inline here or custom.js, 
        // but for now let's keep it consistent.
        
        // Country Change Handler for Dynamic Pricing
        $('#countryId').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var countryId = selectedOption.data('id');
            var countryCode = selectedOption.data('code'); // Assuming code column exists
            var countryName = $(this).val();

            if(countryId) {
                // Determine if we need to reload (Multi-currency support)
                // We'll update the session via generic helper
                $.post('includes/price_helper.php', { 
                    action: 'change_country', 
                    country_id: countryId 
                }, function(resp) {
                    // Reload to apply new prices
                    window.location.reload(); 
                });
            }
        });

        // Determine initial visibility
        $(document).ready(function() {
            checkPaymentMethods();
            
            // Initialize Address Form State to remove required attributes if hidden
            var selectedRadio = $('input[name="selected_address"]:checked');
            var selectedVal = selectedRadio.val();
            
            if (!selectedVal) {
                // Check for hidden input if no radio (e.g. no saved addresses)
                var hiddenInput = $('input[name="selected_address"][type="hidden"]');
                if (hiddenInput.length > 0) {
                    selectedVal = hiddenInput.val();
                }
            }
            
            if (selectedVal === 'new') {
                toggleNewAddress(true);
            } else if (selectedVal) {
                toggleNewAddress(false);
            }
        });

        function checkPaymentMethods() {
            var countryName = $('#countryId').val(); // Name
            
            // Case-insensitive check for India
            var isIndia = (countryName && countryName.toLowerCase() === 'india') || !countryName; 
            
            if (isIndia) {
                // Show India Payment Methods
                $('#online_payment_section').show();
                $('#cod_payment_section').show();
                $('#paypal_option').hide();
                
                // Ensure online/cod is selected if needed and available
                if (!$('input[name="payment_method"]:checked').val()) {
                     if($('#online').length > 0) $('#online').prop('checked', true);
                     else if($('#cod').length > 0) $('#cod').prop('checked', true);
                }
            } else {
                // Hide India Payment Methods
                $('#online_payment_section').hide();
                $('#cod_payment_section').hide();
                $('#paypal_option').show();
                // PayPal doesn't use a radio button, so we don't need to select one.
            }
        }
        

        $('#checkoutForm').on('submit', function (e) {
            e.preventDefault();

            // Basic validation check
            if (!this.checkValidity()) {
                this.reportValidity();
                return;
            }

            // Check if PayPal is the active method
            if ($('#paypal_option').is(':visible')) {
                // Do nothing, PayPal button handles it?
                // Actually, user might click "Place Order" button which is for standard flow.
                // We should Hide the standard "Place Order" button when PayPal is active?
                // Or let PayPal button be the only way.
                alert("Please use the PayPal button to complete your order.");
                return;
            }

            const paymentMethod = $('input[name="payment_method"]:checked').val();

            if (paymentMethod === 'COD') {
                $('#codConfirmationModal').modal('show');
            } else {
                processOrderSubmission(this);
            }
        });

        function processOrderSubmission(form) {
            const formData = new FormData(form);
            const submitBtn = $(form).find('button[type="submit"]');
            submitBtn.prop('disabled', true).text('Processing...');

            fetch('submit_order.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = data.redirect_url ? data.redirect_url : 'payment-success.php';
                    } else if (data.status === 'razorpay_init') {
                        // Initialize Razorpay
                        var options = {
                            "key": "<?php echo RAZORPAY_KEY_ID; ?>", // Enter the Key ID generated from the Dashboard
                            "amount": data.amount, // Amount is in currency subunits. Default currency is INR.
                            "currency": "INR",
                            "name": "Sriji Vastra Shingar Sewa",
                            "description": "Order Payment",
                            "image": "assets/images/logo/logo.png", // Ensure this path is correct relative to domain
                            "order_id": data.razorpay_order_id,
                            "handler": function (response) {
                                // Verify Payment
                                verifyPayment(response, data.sys_order_id);
                            },
                            "prefill": {
                                "name": $('input[name="name"]').val(),
                                "email": $('input[name="email"]').val(),
                                "contact": $('input[name="phone"]').val()
                            },
                            "color": "#B48E43",
                            "modal": {
                                "ondismiss": function () {
                                    $('#paymentExitModal').modal('show');
                                }
                            }
                        };
                        window.rzp1 = new Razorpay(options); // Make global
                        window.rzp1.on('payment.failed', function (response) {
                            // Log failure
                            const fd = new FormData();
                            fd.append('order_id', data.sys_order_id);
                            fd.append('payment_id', response.error.metadata.payment_id);
                            fd.append('error_description', response.error.description);
                            fd.append('error_code', response.error.code);

                            navigator.sendBeacon('log_payment_failure.php', fd);
                            window.location.href = 'payment-failed.php?error=' + encodeURIComponent(response.error.description);
                        });
                        window.rzp1.open();
                    } else {
                        alert(data.message || 'Error processing order');
                        submitBtn.prop('disabled', false).text('Place order');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Connection error');
                    submitBtn.prop('disabled', false).text('Place order');
                });
        }

        // Payment Exit Modal Handlers
        $(document).ready(function () {
            $('#btnContinuePayment').on('click', function () {
                $('#paymentExitModal').modal('hide');
                if (window.rzp1) {
                    window.rzp1.open();
                }
            });

            $('#btnCancelPayment').on('click', function () {
                $('#paymentExitModal').modal('hide');
                const submitBtn = $('#checkoutForm').find('button[type="submit"]');
                submitBtn.prop('disabled', false).text('Place order');
            });

            // Confirm COD Order Handler
            $('#confirmCodOrder').on('click', function () {
                $('#codConfirmationModal').modal('hide');
                const form = document.getElementById('checkoutForm');
                processOrderSubmission(form);
            });
        });

        function verifyPayment(razorpay_response, sys_order_id) {
            fetch('verify_payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `razorpay_payment_id=${razorpay_response.razorpay_payment_id}&razorpay_order_id=${razorpay_response.razorpay_order_id}&razorpay_signature=${razorpay_response.razorpay_signature}&order_id=${sys_order_id}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = 'payment-success.php?order_number=' + (data.order_number || '');
                    } else {
                        window.location.href = 'payment-failed.php?error=' + encodeURIComponent(data.message);
                    }
                })
                .catch(err => {
                    alert('Error verifying payment');
                });
        }
    </script>

    <script>
        // Update Total based on Payment Method
        $(document).ready(function () {
            function updateCheckoutTotal() {
                var paymentMethod = $('input[name="payment_method"]:checked').val();
                var baseTotal = parseFloat($('#base_total').val());
                var codCharge = parseFloat($('#cod_charge_val').val());
                var finalTotal = baseTotal;

                if (paymentMethod === 'COD') {
                    finalTotal += codCharge;
                    $('#cod_fee_display').show();
                } else {
                    $('#cod_fee_display').hide();
                }
                
                // Hide total display if PayPal (or keep it?)
                // PayPal takes care of its own display usually, but good to keep.

                $('#total_display_area span').text('₹' + finalTotal.toFixed(2));
            }

            $('.payment-radio').on('change', updateCheckoutTotal);

            // Init check
            updateCheckoutTotal();
        });
    </script>
    <script>
        // Toggle New Address Form
        function toggleNewAddress(show) {
            const form = document.getElementById('new_address_form');
            const inputs = form.querySelectorAll('input, select, textarea');
            if (show) {
                form.style.display = 'block';
                inputs.forEach(i => i.required = true);
            } else {
                form.style.display = 'none';
                inputs.forEach(i => i.required = false);
            }
        }

        // --- Location API Integration ---
        $(document).ready(function () {
            $('.select_2').select2();

            // Fetch Countries - REMOVED to use server-side active countries list
            /* 
            $.get("https://countriesnow.space/api/v0.1/countries", function (data) {
                ...
            });
            */


            // Fetch States Logic
            function fetchStates(country) {
                if(!country) return;
                $('#stateId').html('<option value="">Loading...</option>');
                $.post("https://countriesnow.space/api/v0.1/countries/states", { country: country }, function (data) {
                    if (!data.error) {
                        let options = '<option value="">Select State</option>';
                        data.data.states.forEach(s => {
                            options += `<option value="${s.name}">${s.name}</option>`;
                        });
                        $('#stateId').html(options);
                    } else {
                        $('#stateId').html('<option value="">No states found</option>');
                    }
                });
            }

            // Fetch States on Country Change
            $('#countryId').on('change', function () {
                const country = $(this).val();
                fetchStates(country);
            });

            // Fetch States on Init (if country selected)
            const initialCountry = $('#countryId').val();
            if(initialCountry) {
                fetchStates(initialCountry);
            }


            // Fetch Cities on State Change
            $('#stateId').on('change', function () {
                const country = $('#countryId').val();
                const state = $(this).val();
                $('#cityId').html('<option value="">Loading...</option>');

                $.post("https://countriesnow.space/api/v0.1/countries/state/cities", { country: country, state: state }, function (data) {
                    if (!data.error) {
                        let options = '<option value="">Select City</option>';
                        data.data.forEach(c => {
                            options += `<option value="${c}">${c}</option>`;
                        });
                        $('#cityId').html(options);
                    } else {
                        $('#cityId').html('<option value="">No cities found</option>');
                    }
                });
            });
        });
    </script>
    <!-- Payment Exit Modal -->
    <div class="modal fade" id="paymentExitModal" tabindex="-1" aria-labelledby="paymentExitModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="paymentExitModalLabel">Wait! Don't leave yet...</h5>
                    <!-- No close button to force choice -->
                </div>
                <div class="modal-body text-center">
                    <img src="assets/images/payment_success_img2.png" alt="wait" class="img-fluid mb-3"
                        style="max-height: 150px;">
                    <p class="mb-3">We're sad to see you go! You are just moments away from completing your order.</p>
                    <p class="text-muted small">Complete your payment now to secure your items.</p>
                </div>
                <div class="modal-footer justify-content-center border-0 pb-4">
                    <button type="button" class="btn btn-secondary" id="btnCancelPayment">Cancel Order</button>
                    <button type="button" class="btn btn-primary common_btn" id="btnContinuePayment"
                        style="padding: 10px 20px;">Continue Payment</button>
                </div>
            </div>
        </div>
    </div>

    <!-- COD Confirmation Modal -->
    <div class="modal fade" id="codConfirmationModal" tabindex="-1" aria-labelledby="codConfirmationModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="codConfirmationModalLabel">Confirm Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to place this order using <strong>Cash on Delivery</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary common_btn" id="confirmCodOrder">Yes, Place
                        Order</button>
                </div>
            </div>
        </div>
    </div>

    <!-- PayPal Button Render Logic -->
    <script>
        paypal.Buttons({
            createOrder: function(data, actions) {
                var form = document.getElementById('checkoutForm');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return Promise.reject("Form invalid");
                }
                
                const formData = new FormData(form);
                formData.append('payment_method', 'PayPal'); 
                
                return fetch('submit_order.php', {
                    method: 'POST',
                    body: formData
                }).then(function(res) {
                    return res.json();
                }).then(function(orderData) {
                    if(orderData.status === 'paypal_init') {
                        return actions.order.create({
                            purchase_units: [{
                                amount: {
                                    value: orderData.amount_usd // Amount in USD
                                },
                                description: "Order " + orderData.order_number
                            }],
                             application_context: {
                                shipping_preference: 'NO_SHIPPING'
                            }
                        });
                    } else {
                         throw new Error(orderData.message);
                    }
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    // Payment Successful
                    // console.log('Capture result', details); 
                    return fetch('verify_paypal.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            paypal_order_id: data.orderID,
                            details: details
                        })
                    }).then(res => {
                        if (!res.ok) { throw new Error('Network response was not ok'); }
                        return res.json();
                    })
                    .then(resData => {
                        if(resData.status === 'success') {
                             window.location.href = 'payment-success.php?order_number=' + resData.order_number;
                        } else {
                             window.location.href = 'payment-failed.php?error=' + encodeURIComponent(resData.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error verifying payment:', error);
                        // Redirect to failed or contact support
                        window.location.href = 'payment-failed.php?error=' + encodeURIComponent("Payment captured but verification failed. Please contact support.");
                    });
                });
            },
            onError: function (err) {
                console.error('PayPal Error', err);
                alert('PayPal Error: ' + err);
            }
        }).render('#paypal-button-container');
    </script>
</body>
</html>