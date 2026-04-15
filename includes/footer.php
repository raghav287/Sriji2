<?php
// Ensure prefix is available if header wasn't included or scope issue (though usually it is)
if (!isset($header_path_prefix)) {
    $header_path_prefix = (basename(dirname($_SERVER['PHP_SELF'])) == 'user') ? '../' : '';
}
?>
<footer class="footer_2 pt_100"
    style="background: url(<?php echo $header_path_prefix; ?>assets/images/footer_2_bg_2.jpg);">
    <div class="container">
        <div class="row justify-content-between">

            <!-- Column 1 -->
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <div class="footer_2_logo_area">
                    <a class="footer_logo" href="<?php echo $header_path_prefix; ?>index.php">
                        <img src="<?php echo $header_path_prefix; ?>assets/images/logo/logo.png"
                            alt="Sriji Vastra Shingar Sewa" class="img-fluid w-100">
                    </a>
                    <p>Bringing sacred and divine essentials to your home with purity and devotion.</p>
                    <ul>
                        <li><span>Follow :</span></li>
                        <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                        <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                        <li><a href="#"><i class="fab fa-google-plus-g"></i></a></li>
                        <li><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                    </ul>
                </div>
            </div>

            <!-- Column 2 -->
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <div class="footer_link">
                    <h3>Company</h3>
                    <ul>
                        <li><a href="<?php echo $header_path_prefix; ?>about-us">About us</a></li>
                        <li><a href="<?php echo $header_path_prefix; ?>contact">Contact Us</a></li>
                        <!--<li><a href="#">Affiliate</a></li>-->
                    </ul>
                </div>
            </div>

            <!-- Column 3 -->
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <div class="footer_link">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="<?php echo $header_path_prefix; ?>privacy-policy.php">Privacy Policy</a></li>
                        <li><a href="<?php echo $header_path_prefix; ?>terms-and-conditions.php">Terms & Conditions</a>
                        </li>
                        <li><a href="<?php echo $header_path_prefix; ?>return-policy.php">Return Policy</a></li>
                        <li><a href="faq.php">FAQ's</a></li>
                        <li><a href="<?php echo $header_path_prefix; ?>shipping-policy.php">Shipping Policy</a></li>
                    </ul>
                </div>
            </div>

            <!-- Column 4 -->
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <div class="footer_link footer_logo_area">
                    <h3>Contact Us</h3>
                    <p>Feel free to reach out for assistance, product support, or spiritual guidance.</p>

                    <span>
                        <b><img src="<?php echo $header_path_prefix; ?>assets/images/location_icon_white.png" alt="Map"
                                class="img-fluid"></b>
                        Palm Village Apartment, Sector-126, Mohali
                    </span>

                    <span>
                        <b><img src="<?php echo $header_path_prefix; ?>assets/images/phone_icon_white.png" alt="Call"
                                class="img-fluid"></b>
                        <a href="tel:+919115936593">+91 9115936593</a>
                    </span>

                    <span>
                        <b><img src="<?php echo $header_path_prefix; ?>assets/images/mail_icon_white.png" alt="Mail"
                                class="img-fluid"></b>
                        <a href="mailto:info@srijivastrashingarsewa.com">info@srijivastrashingarsewa.com</a>
                    </span>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-12">
                <div class="footer_copyright mt_75 d-flex justify-content-between flex-wrap align-items-center">
                    <p>
                        ©
                        <a href="#" target="_blank" style="text-decoration: none;">
                            Sri Ji Vastra Shingar Sewa
                        </a>
                        <?php echo date('Y'); ?> |
                        Designed & Developed By
                        <a href="https://makes360.com" target="_blank" style="text-decoration: none;">
                            Makes360
                        </a>
                    </p>
                    <div class="footer_country_selector mt-2 mt-md-0">
                        <?php
                        // Check connection if needed, though usually available.
                        if (isset($conn)) {
                            $c_sql = "SELECT * FROM countries WHERE status='active' ORDER BY name ASC";
                            $c_res = $conn->query($c_sql);
                            if ($c_res->num_rows > 0) {
                                ?>
                        <form action="<?php echo $header_path_prefix; ?>includes/price_helper.php" method="POST"
                            class="d-flex align-items-center">
                            <input type="hidden" name="action" value="change_country">
                            <label class="text-white me-2">Region:</label>
                            <select name="country_id" class="form-select form-select-sm"
                                style="width:auto; display:inline-block;" onchange="this.form.submit()">
                                <option value="0">Default (India)</option>
                                <?php
                                        $curr_c = $_SESSION['selected_country_id'] ?? 0;
                                        while ($crow = $c_res->fetch_assoc()) {
                                            $sel = ($curr_c == $crow['id']) ? 'selected' : '';
                                            echo '<option value="' . $crow['id'] . '" ' . $sel . '>' . htmlspecialchars($crow['name']) . '</option>';
                                        }
                                        ?>
                            </select>
                        </form>
                        <?php }
                        } ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
    </div>
</footer>

<script>
function addToCartListing(productId, event) {
    if (event) event.preventDefault();

    const prefix = "<?php echo $header_path_prefix; ?>";

    fetch(prefix + 'includes/cart_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'action=add&quantity=1&product_id=' + productId
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Added to cart!');
                if (typeof loadMiniCart === 'function') {
                    loadMiniCart();
                }
            } else if (data.status === 'requires_selection') {
                // Redirect to details page
                window.location.href = data.redirect;
            } else {
                alert(data.message || 'Error adding to cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Fallback redirect if something fails
            window.location.href = prefix + 'shop-details.php?id=' + productId;
        });
}
</script>