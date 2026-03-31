<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'admin/db.php';
include 'includes/price_helper.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: shop.php");
    exit();
}

$product_id = intval($_GET['id']);

// 1. Fetch Product Details
$stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "<h1>Product not found</h1>";
    exit();
}

// 2. Fetch Product Images
$stmt_img = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC");
$stmt_img->bind_param("i", $product_id);
$stmt_img->execute();
$images_result = $stmt_img->get_result();
$images = [];
while ($row = $images_result->fetch_assoc()) {
    $images[] = $row;
}
if (empty($images)) {
    // Fallback if no images
    $images[] = ['image_path' => 'assets/images/no_image.png'];
}

// 3. Fetch Variants (New Structure)
$stmt_var = $conn->prepare("SELECT v.*, s.size as variant_name FROM product_variants v JOIN product_sizes s ON v.product_size_id = s.id WHERE s.product_id = ?");
$stmt_var->bind_param("i", $product_id);
$stmt_var->execute();
$variants_result = $stmt_var->get_result();
$variants = [];
$unique_sizes = [];
$unique_colors = [];
$unique_colors_data = [];
$min_price = null;
$max_price = null;
$min_strike_price = null;
$max_strike_price = null;
$total_stock = 0;

while ($row = $variants_result->fetch_assoc()) {
    $variants[] = $row;
    if (!empty($row['variant_name']))
        $unique_sizes[$row['variant_name']] = $row['variant_name'];
    if (!empty($row['color'])) {
        $unique_colors[$row['color']] = $row['color'];
        // Store code keyed by name to avoid duplicates
        $unique_colors_data[$row['color']] = [
            'name' => $row['color'],
            'code' => !empty($row['color_code']) ? $row['color_code'] : $row['color']
        ];
    }

    // Apply zone pricing to get the actual display price
    $base_price = $row['price'];
    $size_name = $row['variant_name'];
    $calculated_price = calculate_price($base_price, $size_name);

    if ($min_price === null || $calculated_price < $min_price)
        $min_price = $calculated_price;
    if ($max_price === null || $calculated_price > $max_price)
        $max_price = $calculated_price;

    // Track strike prices if available
    if (!empty($row['strike_price']) && $row['strike_price'] > 0) {
        $strike_base = $row['strike_price'];
        $calculated_strike = calculate_price($strike_base, $size_name);

        if ($min_strike_price === null || $calculated_strike < $min_strike_price)
            $min_strike_price = $calculated_strike;
        if ($max_strike_price === null || $calculated_strike > $max_strike_price)
            $max_strike_price = $calculated_strike;
    }

    $total_stock += $row['stock_quantity'];
}

// If no variants, use product base price/stock if those columns exist (checking schema implies they might only be in variants? 
// The schema check for 'products' wasn't fully shown but typically 'price' is in products table too?
// Let's assume if variants exist, use them. If not, maybe the product table has price? 
// Based on previous 'shop.php' work, we used 'p.price' in sorting. So 'products' likely has 'price'.
if (empty($variants)) {
    // If no variants, fallback to 0 or handle logic. 
    // Since products table has no price, we can't get it from there.
    $min_price = 0;
    $max_price = 0;
    $total_stock = 0;
}

// 4. Fetch Specifications
$stmt_spec = $conn->prepare("SELECT * FROM product_specifications WHERE product_id = ?");
$stmt_spec->bind_param("i", $product_id);
$stmt_spec->execute();
$specs_result = $stmt_spec->get_result();
$specs = [];
while ($row = $specs_result->fetch_assoc()) {
    $specs[] = $row;
}

// 5. Fetch Related Products (Same Category)
// We need to join product_variants to get a price, or use a subquery. 
// Use LIMIT 1 variant per product to get a representative price and stock status if needed.
$stmt_rel = $conn->prepare("
    SELECT p.*, 
           (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC LIMIT 1) as image,
           (SELECT MIN(v.price) FROM product_variants v JOIN product_sizes s ON v.product_size_id = s.id WHERE s.product_id = p.id) as min_price,
           (SELECT v.strike_price FROM product_variants v JOIN product_sizes s ON v.product_size_id = s.id WHERE s.product_id = p.id ORDER BY v.price ASC LIMIT 1) as min_strike_price
    FROM products p 
    WHERE p.category_id = ? AND p.id != ? 
    LIMIT 4
");
$stmt_rel->bind_param("ii", $product['category_id'], $product_id);
$stmt_rel->execute();
$related_products = $stmt_rel->get_result();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
    <title><?php echo htmlspecialchars($product['name']); ?> - Shop Details</title>
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
        PAGE BANNER START
    ==========================-->
    <section class="page_banner" style="background: url(assets/images/background/breadcrumb-bg.jpg);">
        <div class="page_banner_overlay">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="page_banner_text wow fadeInUp">
                            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                            <ul>
                                <li><a href="index.php"><i class="fal fa-home-lg"></i> Home</a></li>
                                <li><a href="shop.php">Shop</a></li>
                                <li><a href="#"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                                <li><a href="#"><?php echo htmlspecialchars($product['name']); ?></a></li>
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
        SHOP DETAILS START
    =============================-->
    <section class="shop_details mt_100">
        <div class="container">
            <div class="row">
                <div class="col-xxl-10">
                    <div class="row">
                        <div class="col-lg-6 col-md-10 wow fadeInLeft">
                            <div class="shop_details_slider_area">
                                <div class="row">
                                    <div class="col-xl-2 col-lg-3 col-md-3 order-2 order-md-1">
                                        <div class="row details_slider_nav">
                                            <?php foreach ($images as $img): ?>
                                            <div class="col-12">
                                                <div class="details_slider_nav_item">
                                                    <img src="assets/uploads/products/<?php echo htmlspecialchars($img['image_path']); ?>"
                                                        alt="Product" class="img-fluid w-100">
                                                    <!-- <div class="product-watermark">srijivastrashingarsewa.com</div> -->
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-xl-10 col-lg-9 col-md-9  order-md-1">
                                        <div class="row details_slider_thumb">
                                            <?php foreach ($images as $img): ?>
                                            <div class="col-12">
                                                <div class="details_slider_thumb_item">
                                                    <img src="assets/uploads/products/<?php echo htmlspecialchars($img['image_path']); ?>"
                                                        alt="Product" class="img-fluid w-100">
                                                    <!-- <div class="product-watermark">srijivastrashingarsewa.com</div> -->
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 wow fadeInUp">
                            <div class="shop_details_text">
                                <p class="category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                <h2 class="details_title"><?php echo htmlspecialchars($product['name']); ?></h2>
                                <div class="d-flex flex-wrap align-items-center">
                                    <?php if ($total_stock > 0): ?>
                                    <p class="stock">In Stock</p>
                                    <?php else: ?>
                                    <p class="stock out_stock">Out of Stock</p>
                                    <?php endif; ?>
                                </div>
                                <h3 class="price" id="product-price">
                                    <?php
                                    // Min/max prices are already calculated with zone pricing applied
                                    if ($min_price != $max_price) {
                                        echo "<span style='font-size: 0.6em; color: #666; font-weight: normal;'>Starting from</span> ";
                                    }
                                    echo "₹" . number_format($min_price, 2);

                                    // Show strike price if available
                                    if ($min_strike_price !== null) {
                                        echo ' <del style="color: #999;">₹' . number_format($min_strike_price, 2);
                                        if ($min_strike_price != $max_strike_price) {
                                            echo ' - ₹' . number_format($max_strike_price, 2);
                                        }
                                        echo '</del>';
                                    }
                                    ?>
                                </h3>
                                <p class="short_description">
                                    <?php
                                    $desc_clean = strip_tags($product['description']);
                                    echo htmlspecialchars(mb_strimwidth($desc_clean, 0, 200, "..."));
                                    ?>
                                </p>
                                <?php if (!empty($unique_sizes)): ?>
                                <div class="details_single_variant">
                                    <p class="variant_title">Size :</p>
                                    <ul class="details_variant_size">
                                        <?php
                                            $first = true;
                                            foreach ($unique_sizes as $size):
                                                // $active_class = $first ? 'active' : ''; // Removed auto-active to encourage selection, or keep key logic later
                                                // Let's keep logic simple: user selects size.
                                                $active_class = '';
                                                ?>
                                        <li class="<?php echo $active_class; ?>"
                                            data-size="<?php echo htmlspecialchars($size); ?>">
                                            <?php echo htmlspecialchars($size); ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($unique_colors)): ?>
                                <div class="details_single_variant">
                                    <p class="variant_title">Color :</p>
                                    <ul class="details_variant_color d-flex flex-wrap"
                                        style="gap: 10px; list-style: none; padding: 0;">
                                        <?php
                                            // Ensure unique_colors_data is set (fallback for old logic if not updated)
                                            $colors_to_show = isset($unique_colors_data) ? $unique_colors_data : array_map(function ($c) {
                                                return ['name' => $c, 'code' => $c];
                                            }, $unique_colors);

                                            foreach ($colors_to_show as $colorData):
                                                $cName = $colorData['name'];
                                                $cCode = $colorData['code'];
                                                ?>
                                        <li class="color_item" data-color="<?php echo htmlspecialchars($cName); ?>"
                                            style="width: 30px; height: 30px; border-radius: 50%; background: <?php echo htmlspecialchars($cCode); ?>; border: 1px solid #ddd; cursor: pointer;"
                                            title="<?php echo htmlspecialchars($cName); ?>"></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>

                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="details_qty_input">
                                        <button class="minus"><i class="fal fa-minus"></i></button>
                                        <input type="text" placeholder="01" value="1">
                                        <button class="plus"><i class="fal fa-plus"></i></button>
                                    </div>
                                    <div class="details_btn_area">
                                        <a class="common_btn buy_now" href="#">Buy Now <i
                                                class="fas fa-long-arrow-right"></i></a>
                                        <a class="common_btn" href="#">Add to cart <i
                                                class="fas fa-long-arrow-right"></i></a>
                                    </div>
                                </div> <br>

                                <ul class="details_tags_sku">
                                    <li><span>SKU:</span>
                                        <?php echo 'SKU-' . $product['id']; // generating dummy SKU using ID ?></li>
                                </ul>

                                <ul class="shop_details_shate">
                                    <?php
                                    $share_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                                    $share_title = urlencode($product['name']);
                                    $share_url_enc = urlencode($share_url);
                                    ?>
                                    <li>Share:</li>
                                    <li><a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url_enc; ?>"
                                            target="_blank"><i class="fab fa-facebook-f"></i></a></li>
                                    <li><a href="https://twitter.com/intent/tweet?text=<?php echo $share_title; ?>&url=<?php echo $share_url_enc; ?>"
                                            target="_blank"><i class="fab fa-twitter"></i></a></li>
                                    <!-- Instagram removed (no direct web share) -->
                                    <li><a href="https://api.whatsapp.com/send?text=<?php echo $share_title . ' ' . $share_url_enc; ?>"
                                            target="_blank"><i class="fab fa-whatsapp"></i></a></li>
                                </ul>

                            </div>
                        </div>
                    </div>
                    <div class="row mt_90 wow fadeInUp">
                        <div class="col-12">
                            <div class="shop_details_des_area">
                                <ul class="nav nav-pills" id="pills-tab2" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="description-tab" data-bs-toggle="pill"
                                            data-bs-target="#description" type="button" role="tab"
                                            aria-controls="description" aria-selected="false">Description</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="description-tab2" data-bs-toggle="pill"
                                            data-bs-target="#description2" type="button" role="tab"
                                            aria-controls="description2" aria-selected="false">Additional info</button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="pills-tabContent2">
                                    <div class="tab-pane fade show active" id="description" role="tabpanel"
                                        aria-labelledby="description-tab" tabindex="0">
                                        <div class="shop_details_description">
                                            <h3>Description</h3>
                                            <?php echo $product['description']; ?>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="description2" role="tabpanel"
                                        aria-labelledby="description-tab2" tabindex="0">
                                        <div class="shop_details_additional_info">
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <tbody>
                                                        <table class="table table-striped">
                                                            <tbody>
                                                                <?php if (!empty($specs)): ?>
                                                                <?php foreach ($specs as $spec): ?>
                                                                <tr>
                                                                    <th><?php echo htmlspecialchars($spec['spec_key']); ?>
                                                                    </th>
                                                                    <td><?php echo htmlspecialchars($spec['spec_value']); ?>
                                                                    </td>
                                                                </tr>
                                                                <?php endforeach; ?>
                                                                <?php else: ?>
                                                                <tr>
                                                                    <td colspan="2">No additional information available.
                                                                    </td>
                                                                </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--============================
        SHOP DETAILS END
    =============================-->


    <!--============================
        RELATED PRODUCTS START
    =============================-->
    <section class="related_products mt_90 mb_70 wow fadeInUp">
        <div class="container">
            <div class="row">
                <div class="col-xl-6">
                    <div class="section_heading_2 section_heading">
                        <h3><span>Related</span> Products</h3>
                    </div>
                </div>
            </div>
            <div class="row mt_25 flash_sell_2_slider">
                <?php
                while ($rel = $related_products->fetch_assoc()):
                    // Determine price
                    $rel_price = $rel['min_price'] ?? 0;
                    $rel_strike_price = $rel['min_strike_price'];
                    $rel_img = !empty($rel['image']) ? 'assets/uploads/products/' . $rel['image'] : 'assets/images/no_image.png';
                    ?>
                <div class="col-xl-1-5">
                    <div class="product_item_2 product_item">
                        <div class="product_img">
                            <img src="<?php echo htmlspecialchars($rel_img); ?>" alt="Product" class="img-fluid w-100">
                            <ul class="btn_list">
                                <!-- Wishlist removed as per request -->
                                <!-- <li>
                                        <a href="#">
                                            <img src="assets/images/love_icon_white.svg" alt="Love" class="img-fluid">
                                        </a>
                                    </li> -->

                            </ul>
                        </div>
                        <div class="product_text">
                            <a class="title"
                                href="shop-details.php?id=<?php echo $rel['id']; ?>"><?php echo htmlspecialchars($rel['name']); ?></a>
                            <p class="price"><?php
                                echo "<span style='font-size: 0.8em; color: #666; font-weight: normal;'>Starting from</span> ";
                                echo format_price($rel_price);
                                ?>
                                <?php if ($rel_strike_price > $rel_price): ?>
                                <del><?php echo format_price($rel_strike_price); ?></del>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    </div>
    </div>
    </section>
    <!--============================
        RELATED PRODUCTS END
    =============================-->

    <script>
    // Pass variants data and pricing config to JS
    const productVariants = <?php echo json_encode($variants); ?>;
    const productImages = <?php echo json_encode($images); ?>;
    const productId = <?php echo $product_id; ?>;

    <?php
        // Enforce Fresh Rules from DB (Fixes Session Stale Cache issues)
        // This ensures if Admin renames "Size 0" to "2 Inch", the user sees it immediately without re-login.
        if (function_exists('refresh_session_rules')) {
            refresh_session_rules($conn);
        }

        $pricing_model = get_pricing_model(); // Re-fetch from session (now updated)
        $formula_rules = [];
        if (strpos($pricing_model, 'formula_') === 0) {
            $formula_rules = $_SESSION['current_formula_rules'] ?? [];
        }
        ?>

    // Pricing Configuration
    const pricingConfig = {
        multiplier: <?php echo get_currency_multiplier(); ?>,
        model: '<?php echo $pricing_model; ?>',
        rules: <?php echo json_encode($formula_rules); ?>
    };

    function calculatePriceJS(basePrice, sizeName) {
        basePrice = parseFloat(basePrice);

        // Check if model indicates formula
        if (pricingConfig.model.startsWith('formula_') && sizeName) {
            let s = sizeName.trim();

            // Lookup rule for this size
            if (pricingConfig.rules && pricingConfig.rules[s]) {
                let factor = parseFloat(pricingConfig.rules[s].factor);
                let constant = parseFloat(pricingConfig.rules[s].constant);
                return (basePrice * factor) + constant;
            }

            // Fallback to multiplier
            return basePrice * pricingConfig.multiplier;
        }
        return basePrice * pricingConfig.multiplier;
    }

    function formatPriceJS(price) {
        return "₹" + price.toFixed(2);
    }

    function filterImages(color) {
        // Filter images: match color or no color (universal)
        let relevantImages = productImages;

        if (color) {
            // 1. Try to find images specifically for this color
            const colorSpecific = productImages.filter(img => img.color && img.color.toLowerCase() === color
                .toLowerCase());

            if (colorSpecific.length > 0) {
                relevantImages = colorSpecific;
            } else {
                // 2. If no color specific images, do we show ALL or just Generic?
                // Typically if I select "Red" and there are no Red images, showing specific "Blue" images is wrong.
                // So we show "Generic" (no color) images.
                relevantImages = productImages.filter(img => !img.color);

                // 3. If NO generic images either, fallback to ALL (last resort)
                if (relevantImages.length === 0) relevantImages = productImages;
            }
        }

        const navContainer = $('.details_slider_nav');
        const thumbContainer = $('.details_slider_thumb');

        // Destroy Slick
        try {
            thumbContainer.slick('unslick');
            navContainer.slick('unslick');
        } catch (e) {
            console.log("Slick not initialized yet or error unslicking");
        }

        // Clear DOM
        navContainer.empty();
        thumbContainer.empty();

        // Re-populate
        relevantImages.forEach(img => {
            const imgPath = 'assets/uploads/products/' + img.image_path;

            const navHtml = `
                    <div class="col-12">
                        <div class="details_slider_nav_item">
                            <img src="${imgPath}" alt="Product" class="img-fluid w-100">
                        </div>
                    </div>`;

            const thumbHtml = `
                    <div class="col-12">
                        <div class="details_slider_thumb_item">
                            <img src="${imgPath}" alt="Product" class="img-fluid w-100">
                        </div>
                    </div>`;

            navContainer.append(navHtml);
            thumbContainer.append(thumbHtml);
        });

        // Re-init Slick with timeouts to ensure DOM is ready
        setTimeout(() => {
            $('.details_slider_thumb').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: false,
                fade: true,
                asNavFor: '.details_slider_nav'
            });
            $('.details_slider_nav').slick({
                slidesToShow: 5,
                slidesToScroll: 1,
                asNavFor: '.details_slider_thumb',
                autoplay: true,
                autoplaySpeed: 3000,
                dots: false,
                arrows: false,
                centerMode: true,
                centerPadding: '0',
                focusOnSelect: true,
                vertical: true,
                responsive: [{
                        breakpoint: 1200,
                        settings: {
                            slidesToShow: 3,
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 5,
                            vertical: false,
                        }
                    },
                    {
                        breakpoint: 576,
                        settings: {
                            slidesToShow: 3,
                            vertical: false,
                        }
                    }
                ]
            });
        }, 50);
    }


    document.addEventListener('DOMContentLoaded', function() {
        const sizeItems = document.querySelectorAll('.details_variant_size li');
        const colorItems = document.querySelectorAll('.details_variant_color li');
        const priceElement = document.getElementById('product-price');
        const addToCartBtn = document.querySelector(
            '.common_btn[href="#"]'); // More specific selector might be needed if multiple common_btn
        // Let's target the exact Add to Cart button. It usually has "Add to cart" text.
        const btns = document.querySelectorAll('.common_btn');
        let cartBtn = null;
        btns.forEach(b => {
            if (b.textContent.toLowerCase().includes('add to cart')) cartBtn = b;
        });

        let selectedSize = null;
        let selectedColor = null;

        function updatePrice() {
            let relevantVariants = productVariants;

            if (selectedSize) {
                relevantVariants = relevantVariants.filter(v => v.variant_name === selectedSize);
            }

            if (selectedColor) {
                relevantVariants = relevantVariants.filter(v => v.color === selectedColor);
            }

            if (relevantVariants.length > 0) {
                // Calculate prices for all relevant variants using the new logic
                const prices = relevantVariants.map(v => {
                    let sizeName = v.variant_name;
                    return calculatePriceJS(v.price, sizeName);
                });

                // Calculate strike prices if available
                const strikePrices = relevantVariants
                    .filter(v => v.strike_price && parseFloat(v.strike_price) > 0)
                    .map(v => {
                        let sizeName = v.variant_name;
                        return calculatePriceJS(v.strike_price, sizeName);
                    });

                const minP = Math.min(...prices);
                const maxP = Math.max(...prices);

                // Build price display
                let priceHTML = '';
                if (minP === maxP) {
                    priceHTML = formatPriceJS(minP);
                } else {
                    // priceHTML = formatPriceJS(minP) + " - " + formatPriceJS(maxP);
                    priceHTML =
                        "<span style='font-size: 0.6em; color: #666; font-weight: normal;'>Starting from</span> " +
                        formatPriceJS(minP);
                }

                // Add strike price if available
                if (strikePrices.length > 0) {
                    const minStrike = Math.min(...strikePrices);
                    const maxStrike = Math.max(...strikePrices);
                    let strikeHTML = '';
                    if (minStrike === maxStrike) {
                        strikeHTML = ' <del style="color: #999;">' + formatPriceJS(minStrike) + '</del>';
                    } else {
                        strikeHTML = ' <del style="color: #999;">' + formatPriceJS(minStrike) + ' - ' +
                            formatPriceJS(maxStrike) + '</del>';
                    }
                    priceHTML += strikeHTML;
                }

                priceElement.innerHTML = priceHTML;
            }
        }

        // Helper to get variants by size
        function getVariantsBySize(size) {
            return productVariants.filter(v => v.variant_name === size);
        }

        sizeItems.forEach(item => {
            item.addEventListener('click', function() {
                // 1. Handle Active State
                sizeItems.forEach(li => li.classList.remove('active'));
                this.classList.add('active');

                selectedSize = this.getAttribute('data-size');

                // Filter logic for colors
                const relevantVariants = getVariantsBySize(selectedSize);
                const availableColors = relevantVariants.map(v => v.color);

                // Reset color selection if not available
                if (selectedColor && !availableColors.includes(selectedColor)) {
                    selectedColor = null;
                    colorItems.forEach(c => c.classList.remove('active'));
                }

                colorItems.forEach(colorLi => {
                    const colorVal = colorLi.getAttribute('data-color');
                    if (availableColors.includes(colorVal)) {
                        colorLi.style.opacity = '1';
                        colorLi.style.pointerEvents = 'auto';
                        colorLi.style.border = '1px solid #ddd';
                    } else {
                        colorLi.style.opacity = '0.3';
                        colorLi.style.pointerEvents = 'none';
                        colorLi.style.border = '1px dashed #ccc';
                    }
                });

                updatePrice();
            });
        });

        colorItems.forEach(item => {
            item.addEventListener('click', function() {
                // If disabled/opacity low, usually pointer-events: none handles it, but good to check
                if (window.getComputedStyle(this).pointerEvents === 'none') return;

                colorItems.forEach(li => li.classList.remove('active'));
                this.classList.add('active');
                selectedColor = this.getAttribute('data-color');

                updatePrice();
                filterImages(selectedColor); // Update slider
            });
        });

        if (cartBtn) {
            cartBtn.addEventListener('click', function(e) {
                addToCart(e, false);
            });
        }

        const buyNowBtn = document.querySelector('.buy_now');
        if (buyNowBtn) {
            buyNowBtn.addEventListener('click', function(e) {
                addToCart(e, true);
            });
        }

        // Quantity Buttons
        const qtyInput = document.querySelector('.details_qty_input input');
        const minusBtn = document.querySelector('.details_qty_input .minus');
        const plusBtn = document.querySelector('.details_qty_input .plus');

        if (qtyInput && minusBtn && plusBtn) {
            minusBtn.addEventListener('click', function(e) {
                e.preventDefault();
                let currentVal = parseInt(qtyInput.value) || 1;
                if (currentVal > 1) {
                    qtyInput.value = currentVal - 1;
                }
            });

            plusBtn.addEventListener('click', function(e) {
                e.preventDefault();
                let currentVal = parseInt(qtyInput.value) || 1;
                qtyInput.value = currentVal + 1;
            });
        }

        function addToCart(e, isBuyNow) {
            e.preventDefault();

            // Validation
            const hasSizes = sizeItems.length > 0;
            const hasColors = colorItems.length > 0;

            if (hasSizes && !selectedSize) {
                alert("Please select a size.");
                return;
            }
            if (hasColors && !selectedColor) {
                alert("Please select a color.");
                return;
            }

            // Find Variant ID
            let variantId = null;
            if (productVariants.length > 0) {
                const variant = productVariants.find(v => {
                    let matchSize = true;
                    let matchColor = true;
                    if (hasSizes) matchSize = v.variant_name === selectedSize;
                    if (hasColors) matchColor = v.color === selectedColor;
                    return matchSize && matchColor;
                });
                if (variant) {
                    variantId = variant.id;
                } else {
                    alert("Selected variation not available.");
                    return;
                }
            }

            const quantity = qtyInput ? parseInt(qtyInput.value) : 1;

            // AJAX
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('product_id', productId);
            if (variantId) formData.append('variant_id', variantId);
            formData.append('quantity', quantity);

            fetch('includes/cart_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (isBuyNow) {
                            window.location.href = "checkout.php";
                        } else {
                            alert(data.message);
                            if (typeof loadMiniCart === 'function') {
                                loadMiniCart();
                            } else {
                                location.reload();
                            }
                        }
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    });
    </script>


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