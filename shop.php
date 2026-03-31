    <!DOCTYPE html>
    <html lang="en">
    <?php
include 'admin/db.php';


// --- Pagination Configuration ---
$limit = 12; // Products per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// --- Filter Parameters ---
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';
$min_price_filter = isset($_GET['min_price']) ? (int) $_GET['min_price'] : null;
$max_price_filter = isset($_GET['max_price']) ? (int) $_GET['max_price'] : null;
$sort_option = isset($_GET['sort']) ? $_GET['sort'] : 'default';
$status_filter = isset($_GET['status']) ? $_GET['status'] : []; // Array of statuses
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// --- Build Query Conditions ---
$where_clauses = ["p.status = 'active'"];
$params = [];
$types = "";

// Category Filter
if (!empty($selected_category)) {
    $where_clauses[] = "p.category_id = ?";
    $params[] = $selected_category;
    $types .= "i";
}

// Search Filter
if (!empty($search_query)) {
    $where_clauses[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search_term = "%" . $search_query . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

// Status Filter (Sale/Stock)
// Mapping: 'sale' -> ? (maybe check if discount > 0), 'stock' -> stock_quantity > 0?
// For now, let's assume 'stock' checks variant stock.
if (in_array('sale', (array) $status_filter)) {
    // Assuming products on sale have a 'featured' flag or we check price < regular_price in variants? 
    // As per `index.php` featured logic, we might use that, or check discount.
    // Let's assume 'featured' = 'On Sale' for now based on typical patterns, or strict discount check.
    // Better: Check if any variant has discount_price > 0 and < price
    // Simplified: Let's use `featured` column if exists, otherwise skip for now until verified.
}

// --- Price Range Logic (Global Limits for Slider) ---
$price_range_sql = "SELECT MIN(price) as min_global, MAX(price) as max_global FROM product_variants";
$price_range_res = $conn->query($price_range_sql);
$price_limits = $price_range_res->fetch_assoc();
$global_min_price = $price_limits['min_global'] ?? 0;
$global_max_price = $price_limits['max_global'] ?? 1000;

// Price Filter Query
if ($min_price_filter !== null && $max_price_filter !== null) {
    // We need to filter products where AT LEAST ONE variant is in range?
    // Or filter products where average price is in range? 
    // Usually: exists a variant in range.
    $where_clauses[] = "EXISTS (SELECT 1 FROM product_variants pv JOIN product_sizes ps ON pv.product_size_id = ps.id WHERE ps.product_id = p.id AND pv.price BETWEEN ? AND ?)";
    $params[] = $min_price_filter;
    $params[] = $max_price_filter;
    $types .= "dd";
}

// --- Sorting ---
$order_by = "p.created_at DESC"; // Default: New Added
if ($sort_option == 'low_high') {
    $order_by = "(SELECT MIN(v.price) FROM product_variants v JOIN product_sizes s ON v.product_size_id = s.id WHERE s.product_id = p.id) ASC";
} elseif ($sort_option == 'high_low') {
    $order_by = "(SELECT MAX(v.price) FROM product_variants v JOIN product_sizes s ON v.product_size_id = s.id WHERE s.product_id = p.id) DESC";
} elseif ($sort_option == 'on_sale') {
    // Basic implementation
    $order_by = "p.created_at DESC";
}

// --- Main Product Query ---
$where_sql = implode(" AND ", $where_clauses);

// 1. Get Total Count (for Pagination)
$count_sql = "SELECT COUNT(*) as total FROM products p WHERE $where_sql";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_products = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);

// 2. Get Products
$sql = "SELECT p.*, 
        (SELECT MIN(v.price) FROM product_variants v JOIN product_sizes s ON v.product_size_id = s.id WHERE s.product_id = p.id) as min_price,
        (SELECT MAX(v.price) FROM product_variants v JOIN product_sizes s ON v.product_size_id = s.id WHERE s.product_id = p.id) as max_price,
        (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC LIMIT 1) as image
        FROM products p 
        WHERE $where_sql 
        ORDER BY $order_by 
        LIMIT ?, ?";

// Update params for LIMIT
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products_result = $stmt->get_result();

// --- Category Query (Sidebar) ---
$cat_sql = "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.status='active') as count 
            FROM categories c WHERE c.status='active'";
$categories_result = $conn->query($cat_sql);
// --- Pagination Configuration ---

?>

    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
            content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
        <title>Shop - Sriji Vastra Shingar Sewa</title>
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

        <style>
        /* ===== SVSS SHOP CUSTOM POPUP ===== */

        .svss-popup-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.65);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 99999;
        }

        .svss-popup-box {
            background: #ffffff;
            width: 380px;
            max-width: 90%;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            position: relative;
            animation: svssPopupFade 0.3s ease;
        }

        .svss-popup-title {
            margin-bottom: 12px;
            font-size: 20px;
            font-weight: 600;
        }

        .svss-popup-text {
            font-size: 15px;
            margin-bottom: 8px;
        }

        .svss-popup-warning {
            color: #c0392b;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .svss-popup-btn {
            display: inline-block;
            padding: 10px 18px;
            background: #25D366;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            transition: 0.3s ease;
        }

        .svss-popup-btn:hover {
            background: #1ebe5d;
        }

        .svss-popup-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 22px;
            cursor: pointer;
        }

        @keyframes svssPopupFade {
            from {
                transform: scale(0.8);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
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
        PAGE BANNER START
    ==========================-->
        <section class="page_banner" style="background: url(assets/images/background/breadcrumb-bg.jpg);">
            <div class="page_banner_overlay">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="page_banner_text wow fadeInUp">
                                <h1>Shop</h1>
                                <ul>
                                    <li><a href="#"><i class="fal fa-home-lg"></i> Home</a></li>
                                    <li><a href="#">Shop</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="svss-popup-overlay" id="svssCustomPopup">
            <div class="svss-popup-box">
                <span class="svss-popup-close">&times;</span>

                <h3 class="svss-popup-title">Custom Orders</h3>

                <p class="svss-popup-text">
                    Customized orders can be directly placed via WhatsApp.
                </p>

                <p class="svss-popup-warning">
                    Note: Customized orders are non-refundable.
                </p>

                <a href="https://wa.me/919115936593" target="_blank" class="svss-popup-btn">
                    Order on WhatsApp
                </a>
            </div>
        </div>
        <!--=========================
        PAGE BANNER START
    ==========================-->


        <!--============================
        SHOP PAGE START
    =============================-->
        <section class="shop_page mt_100 mb_100">
            <div class="container">
                <div class="row">
                    <div class="col-xxl-2 col-lg-4 col-xl-3">
                        <div id="sticky_sidebar">
                            <div class="shop_filter_btn d-lg-none"> Filter </div>
                            <div class="shop_filter_area">
                                <div class="sidebar_range">
                                    <h3>Price Range</h3>
                                    <div class="range_slider"></div>
                                </div>

                                <div class="sidebar_category">
                                    <h3>Categories</h3>
                                    <ul>
                                        <?php
                                    if ($categories_result->num_rows > 0) {
                                        while ($cat = $categories_result->fetch_assoc()) {
                                            $active_class = ($selected_category == $cat['id']) ? 'active' : ''; // You might want to style active link
                                            // Preserve other query params when switching category? Usually reset page.
                                            // Or just simple link:
                                            echo '<li><a href="shop.php?category=' . $cat['id'] . '">' . htmlspecialchars($cat['name']) . ' <span>' . $cat['count'] . '</span></a></li>';
                                        }
                                    } else {
                                        echo '<li><a href="#">No Categories Found</a></li>';
                                    }
                                    ?>
                                    </ul>
                                </div>
                                <div class="mt-4">
                                    <a href="shop.php" class="common_btn w-100 text-center">Reset Filter</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-10 col-lg-8 col-xl-9">
                        <div class="product_page_top">
                            <div class="row">
                                <div class="col-4 col-xl-6 col-md-6">
                                    <div class="product_page_top_button">
                                        <nav>
                                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                                <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab"
                                                    data-bs-target="#nav-home" type="button" role="tab"
                                                    aria-controls="nav-home" aria-selected="true">
                                                    <i class="fas fa-th"></i>
                                                </button>
                                                <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab"
                                                    data-bs-target="#nav-profile" type="button" role="tab"
                                                    aria-controls="nav-profile" aria-selected="false">
                                                    <i class="fas fa-list-ul"></i>
                                                </button>
                                            </div>
                                        </nav>
                                        <p>Showing
                                            <?php echo $total_products > 0 ? ($offset + 1) : 0; ?>–<?php echo min($offset + $limit, $total_products); ?>
                                            of <?php echo $total_products; ?> results
                                        </p>
                                    </div>
                                </div>
                                <div class="col-8 col-xl-6 col-md-6">
                                    <ul class="product_page_sorting">
                                        <li>
                                            <select class="select_js" onchange="location = this.value;">
                                                <option
                                                    value="shop.php?sort=default<?php echo $selected_category ? '&category=' . $selected_category : ''; ?>"
                                                    <?php echo $sort_option == 'default' ? 'selected' : ''; ?>>Default
                                                    Sorting
                                                </option>
                                                <option
                                                    value="shop.php?sort=low_high<?php echo $selected_category ? '&category=' . $selected_category : ''; ?>"
                                                    <?php echo $sort_option == 'low_high' ? 'selected' : ''; ?>>Low to
                                                    High
                                                </option>
                                                <option
                                                    value="shop.php?sort=high_low<?php echo $selected_category ? '&category=' . $selected_category : ''; ?>"
                                                    <?php echo $sort_option == 'high_low' ? 'selected' : ''; ?>>High to
                                                    Low
                                                </option>
                                                <option
                                                    value="shop.php?sort=new_added<?php echo $selected_category ? '&category=' . $selected_category : ''; ?>"
                                                    <?php echo $sort_option == 'new_added' ? 'selected' : ''; ?>>New
                                                    Added
                                                </option>
                                            </select>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                                aria-labelledby="nav-home-tab" tabindex="0">
                                <div class="row">
                                    <?php
                                if ($products_result->num_rows > 0) {
                                    // Reset pointer for first loop
                                    $products_result->data_seek(0);
                                    while ($prod = $products_result->fetch_assoc()) {
                                        $prod_img = !empty($prod['image']) ? "assets/uploads/products/" . $prod['image'] : "assets/images/product_2.png"; // Fallback
                                        // Check if file exists, if not use fallback or placeholder. 
                                        // Assuming 'image' column holds filename.
                                
                                        // Price Logic
                                        $min_p = $prod['min_price'];
                                        $max_p = $prod['max_price'];
                                        $price_display = format_price($min_p);
                                        if ($min_p != $max_p) {
                                            $price_display = "Starting from " . format_price($min_p);
                                        }
                                        ?>
                                    <div class="col-xxl-3 col-6 col-md-4 col-lg-6 col-xl-4 wow fadeInUp">
                                        <div class="product_item_2 product_item">
                                            <div class="product_img">
                                                <img src="<?php echo $prod_img; ?>"
                                                    alt="<?php echo htmlspecialchars($prod['name']); ?>"
                                                    class="img-fluid w-100">
                                                <div class="product-watermark">srijivastrashingarsewa.com</div>

                                                <!-- Discount/New Badges could go here -->
                                                <ul class="btn_list">
                                                    <li>
                                                        <a href="shop-details?id=<?php echo $prod['id']; ?>">
                                                            <img src="assets/images/cart_icon_white.svg"
                                                                alt="Add to Cart" class="img-fluid">
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="product_text">
                                                <a class="title"
                                                    href="shop-details?id=<?php echo $prod['id']; ?>"><?php echo htmlspecialchars($prod['name']); ?></a>
                                                <p class="price"><?php echo $price_display; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    }
                                } else {
                                    echo '<div class="col-12"><p>No products found.</p></div>';
                                }
                                ?>
                                </div>

                                <!-- Pagination -->
                                <div class="row">
                                    <div class="pagination_area">
                                        <nav aria-label="...">
                                            <ul class="pagination justify-content-start mt_50">
                                                <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="?page=<?php echo $page - 1; ?>&category=<?php echo $selected_category; ?>&sort=<?php echo $sort_option; ?>&min_price=<?php echo $min_price_filter; ?>&max_price=<?php echo $max_price_filter; ?>">
                                                        <i class="far fa-arrow-left"></i>
                                                    </a>
                                                </li>
                                                <?php endif; ?>

                                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                    <a class="page-link"
                                                        href="?page=<?php echo $i; ?>&category=<?php echo $selected_category; ?>&sort=<?php echo $sort_option; ?>&min_price=<?php echo $min_price_filter; ?>&max_price=<?php echo $max_price_filter; ?>"><?php echo $i; ?></a>
                                                </li>
                                                <?php endfor; ?>

                                                <?php if ($page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="?page=<?php echo $page + 1; ?>&category=<?php echo $selected_category; ?>&sort=<?php echo $sort_option; ?>&min_price=<?php echo $min_price_filter; ?>&max_price=<?php echo $max_price_filter; ?>">
                                                        <i class="far fa-arrow-right"></i>
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>

                            <!-- List View (Optimized: Same Data) -->
                            <div class="tab-pane fade" id="nav-profile" role="tabpanel"
                                aria-labelledby="nav-profile-tab" tabindex="0">
                                <!-- Reuse logic or simplify for now, leaving empty or duplicating loop if user really needs list view different structure -->
                                <!-- Assuming standard list view structure similar to grid but full width -->
                                <div class="row">
                                    <?php
                                if ($products_result->num_rows > 0) {
                                    // Reset pointer
                                    $products_result->data_seek(0);
                                    while ($prod = $products_result->fetch_assoc()) {
                                        $prod_img = !empty($prod['image']) ? "assets/uploads/products/" . $prod['image'] : "assets/images/product_2.png";
                                        $min_p = $prod['min_price'];
                                        $max_p = $prod['max_price'];
                                        $price_display = format_price($min_p);
                                        if ($min_p != $max_p) {
                                            $price_display = "Starting from " . format_price($min_p);
                                        }
                                        ?>
                                    <div class="col-12 wow fadeInUp">
                                        <div class="product_item_2 product_item product_item_list_view">
                                            <!-- Simplified List View Structure if exists in CSS, usually just flex -->
                                            <!-- For now, using same structure but full width column, wait, list view usually has side text -->
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="product_img">
                                                        <img src="<?php echo $prod_img; ?>"
                                                            alt="<?php echo htmlspecialchars($prod['name']); ?>"
                                                            class="img-fluid w-100">
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="product_text">
                                                        <a class="title"
                                                            href="shop-details?id=<?php echo $prod['id']; ?>"><?php echo htmlspecialchars($prod['name']); ?></a>
                                                        <p class="price"><?php echo $price_display; ?></p>
                                                        <p><?php echo substr($prod['description'] ?? '', 0, 150); ?>...
                                                        </p>
                                                        <ul class="btn_list_inline">
                                                            <li><a href="shop-details?id=<?php echo $prod['id']; ?>">View
                                                                    Details</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    }
                                }
                                ?>
                                </div>
                            </div>
                        </div>

                        <script>
                        // Pass Data to JS
                        window.shopData = {
                            minPrice: <?php echo $global_min_price; ?>,
                            maxPrice: <?php echo $global_max_price; ?>,
                            currentMin: <?php echo $min_price_filter ?? $global_min_price; ?>,
                            currentMax: <?php echo $max_price_filter ?? $global_max_price; ?>
                        };
                        </script>
                    </div>
                </div>
            </div>
        </section>
        <!--============================
        SHOP PAGE END
    =============================-->


        <!--=========================
        FOOTER 2 START
    ==========================-->
        <?php include 'includes/footer.php'; ?>
        <!--=========================
        FOOTER 2 END
    ==========================-->
        <a href="https://wa.me/919115936593" target="_blank"
            class="position-fixed d-flex align-items-center justify-content-center" style="
      bottom:25px;
      right:25px;
      width:65px;
      padding:10px;
      height:65px;
      background-color:#25D366;
      border-radius:50%;
      box-shadow:0 8px 20px rgba(0,0,0,0.2);
      z-index:999;
      transition:all 0.3s ease;
   " onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
            <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" width="30" alt="WhatsApp">
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
        <script>
        document.addEventListener("DOMContentLoaded", function() {

            var popup = document.getElementById("svssCustomPopup");
            var closeBtn = document.querySelector(".svss-popup-close");

            // Show popup when page loads
            popup.style.display = "flex";

            // Close popup
            closeBtn.addEventListener("click", function() {
                popup.style.display = "none";
            });

            // Close when clicking outside box
            popup.addEventListener("click", function(e) {
                if (e.target === popup) {
                    popup.style.display = "none";
                }
            });

        });
        </script>
    </body>

    </html>