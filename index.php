<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<?php require_once 'admin/db.php'; ?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
    <title>Home - Sriji Vastra Shingar Sewa</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
    /* 🔴 IMAGE ZOOM EFFECT */
    .zoom-container {
        overflow: hidden;
    }

    .zoom-image {
        transition: transform 0.4s ease;
    }

    .zoom-container:hover .zoom-image {
        transform: scale(1.1);
    }
    </style>
    <style>
    .zoom-wrapper {
        overflow: hidden;
        position: relative;
        cursor: zoom-in;
    }

    .zoom-wrapper img {
        transition: transform 0.2s ease;
    }

    .zoom-wrapper.active img {
        cursor: zoom-out;
    }
    </style>
    <style>
    .zoom-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.85);
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .zoom-modal img {
        max-width: 95%;
        max-height: 95vh;
        object-fit: contain;
        border-radius: 8px;
        animation: zoomFade 0.3s ease;
    }

    @keyframes zoomFade {
        from {
            transform: scale(0.9);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .zoom-close {
        position: absolute;
        top: 25px;
        right: 35px;
        font-size: 35px;
        color: #fff;
        cursor: pointer;
        font-weight: 300;
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
        BANNER 2 START
    ==========================-->
    <section class="banner_2">
        <div class="container">
            <div class="row">
                <div class="col-xxl-12 col-lg-12">
                    <div class="banner_content">
                        <div class="row banner_2_slider">
                            <div class="col-xl-12">
                                <a href="shop" class="d-block">
                                    <div class="banner_slider_2 wow fadeInUp"
                                        style="background-image: url(assets/images/banner/01.jpg);">
                                        <div class="banner_slider_2_text">
                                            <!-- <h3>Explore Our Spiritual Collection</h3>
                                        <h1>Premium Poshak & Accessories</h1> -->
                                            <!-- <a class="common_btn" href="shop">shop now <i
                                                class="fas fa-long-arrow-right"></i></a> -->
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-xl-12">
                                <a href="shop" class="d-block">
                                    <div class="banner_slider_2 wow fadeInUp"
                                        style="background: url(assets/images/banner/02.jpg);">
                                        <div class="banner_slider_2_text">
                                            <!-- <h3>Sacred Offerings</h3>
                                        <h1>Handcrafted Pagdi, Mala & More</h1> -->
                                            <!-- <a class="common_btn" href="shop">shop now <i
                                                class="fas fa-long-arrow-right"></i></a> -->
                                        </div>

                                    </div>
                                </a>
                            </div>
                            <div class="col-xl-12">
                                <a href="shop" class="d-block">
                                    <div class="banner_slider_2 wow fadeInUp"
                                        style="background: url(assets/images/banner/03.jpg);">
                                        <div class="banner_slider_2_text">
                                            <!-- <h3>Best Selling Items</h3>
                                        <h1>Enhance Your Worship Space</h1> -->
                                            <!-- <a class="common_btn" href="shop">shop now <i
                                                class="fas fa-long-arrow-right"></i></a> -->
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--=========================
        BANNER 2 END
    ==========================-->


    <!--============================
        CATEGORY GRID START
    ==============================-->
    <section class="category category_2 mt_55">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section_heading_2 section_heading mb_30">
                        <h3>Shop by <span>Category</span></h3>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php
                // Fetch All Active Categories
                $cat_sql = "SELECT * FROM categories WHERE status='active' AND featured=1 LIMIT 6";
                $cat_res = $conn->query($cat_sql);
                if ($cat_res->num_rows > 0) {
                    while ($cat = $cat_res->fetch_assoc()) {
                        // Use uploaded image or default
                        $cat_img = !empty($cat['image']) ? "assets/uploads/categories/" . $cat['image'] : "assets/images/category_list_icon_1.png";
                        ?>
                <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-4 wow fadeInUp">
                    <a href="shop.php?category=<?php echo $cat['id']; ?>" class="category_item">
                        <div class="img">
                            <img src="<?php echo $cat_img; ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>"
                                class="img-fluid w-100" style="height: 100px; object-fit: contain;">
                        </div>
                        <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                    </a>
                </div>
                <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>
    <!--============================
        CATEGORY GRID END
    ==============================-->


    <?php
    // Fetch FEATURED Categories for Product Sections
    $feat_sql = "SELECT * FROM categories WHERE status='active' AND featured=1";
    $feat_res = $conn->query($feat_sql);

    if ($feat_res->num_rows > 0) {
        while ($feat_cat = $feat_res->fetch_assoc()) {
            $cat_id = $feat_cat['id'];
            $cat_name = $feat_cat['name'];

            // Fetch 4 Products with Price Range
            $prod_sql = "SELECT p.*, 
                                (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image,
                                MIN(v.price) as min_price,
                                MAX(v.price) as max_price
                         FROM products p 
                         LEFT JOIN product_sizes s ON p.id = s.product_id
                         LEFT JOIN product_variants v ON s.id = v.product_size_id
                         WHERE p.category_id = $cat_id 
                         GROUP BY p.id
                         ORDER BY p.id DESC LIMIT 4";
            $prod_res = $conn->query($prod_sql);

            if ($prod_res->num_rows > 0) {
                ?>
    <!--================================
        <?php echo strtoupper($cat_name); ?> SECTION START
                ==================================-->
    <section class="new_arrival_2 mt_95">
        <div class="container">
            <div class="row">
                <div class="col-xl-6 col-sm-9">
                    <div class="section_heading_2 section_heading">
                        <h3> <span><?php echo htmlspecialchars($cat_name); ?></span> </h3>
                    </div>
                </div>
                <div class="col-xl-6 col-sm-3 d-none d-md-block">
                    <div class="view_all_btn_area">
                        <a class="view_all_btn" href="shop.php?category=<?php echo $cat_id; ?>">View all</a>
                    </div>
                </div>
            </div>
            <div class="row mt_15">
                <?php
                            while ($prod = $prod_res->fetch_assoc()) {
                                $p_img = !empty($prod['image']) ? "assets/uploads/products/" . $prod['image'] : "assets/images/products/placeholder.png";
                                // Price Display Logic
                                $min_price = $prod['min_price'];
                                $max_price = $prod['max_price'];
                                $price_display = "";
                                if ($min_price === null) {
                                    $price_display = "Price Not Available";
                                } elseif ($min_price == $max_price) {
                                    $price_display = format_price($min_price);
                                } else {
                                    $price_display = "Starting from " . format_price($min_price);
                                }
                                ?>
                <div class="col-xl-3 col-6 col-md-4 wow fadeInUp">
                    <div class="product_item_2 product_item">
                        <div class="product_img zoom-wrapper zoom-container">
                            <a class="venobox product-lightbox" data-gall="home-gallery-<?php echo $cat_id; ?>"
                                href="<?php echo $p_img; ?>" title="<?php echo htmlspecialchars($prod['name']); ?>">
                                <img src="<?php echo $p_img; ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>"
                                    class="img-fluid w-100 zoom-image">
                            </a>

                            <div class="product-watermark">srijivastrashingarsewa.com</div>
                        </div>

                        <div class="product_text">
                            <a class="title" href="shop-details?id=<?php echo $prod['id']; ?>">
                                <?php echo htmlspecialchars($prod['name']); ?>
                            </a>
                            <p class="price"><?php echo $price_display; ?></p>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div class="row d-md-none mt-3">
                <div class="col-12">
                    <div class="view_all_btn_area justify-content-center">
                        <a class="view_all_btn" href="shop.php?category=<?php echo $cat_id; ?>">View all</a>
                    </div>
                </div>
            </div>
        </div>
        <script>
        function enableImageZoom() {
            const images = document.querySelectorAll(".zoom-image");

            images.forEach(function(img) {

                // Smooth transition
                img.style.transition = "transform 0.4s ease";

                // Zoom In
                img.addEventListener("mouseenter", function() {
                    img.style.transform = "scale(1.15)";
                });

                // Zoom Out
                img.addEventListener("mouseleave", function() {
                    img.style.transform = "scale(1)";
                });

            });
        }

        // Run after page loads
        document.addEventListener("DOMContentLoaded", function() {
            enableImageZoom();
        });
        </script>
    </section>

    <?php
            } // End if products > 0
        } // End while featured categories
    } // End if featured categories > 0
    ?>

    <!--=========================
        VISITS SECTION START
    ==========================-->
    <section class="visits_section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="visit_content wow fadeInUp">
                        <h2>Trusted by Thousands</h2>
                        <h3><span id="visit_counter">20,000</span>+ Happy Visitors</h3>
                        <p>Join our growing community of spiritual seekers.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--=========================
        VISITS SECTION END
    ==========================-->

    <!--========================
        SUBSCRIPTION 2 END
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
    <!-- <div class="progress-wrap">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </div> -->

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
    // Visits Counter Script
    document.addEventListener("DOMContentLoaded", () => {
        const counterElement = document.getElementById("visit_counter");
        if (counterElement) {
            let visits = localStorage.getItem("total_visits");

            // Initialize or parse visits
            if (!visits) {
                visits = 20000;
            } else {
                visits = parseInt(visits, 10);
                if (isNaN(visits)) visits = 20000;
            }

            // Function to update display
            const updateDisplay = () => {
                counterElement.innerText = visits.toLocaleString('en-IN');
            };

            updateDisplay();

            // Simulate increasing visits
            setInterval(() => {
                const increment = Math.floor(Math.random() * 3) + 1;
                visits += increment;
                localStorage.setItem("total_visits", visits);
                updateDisplay();
            }, 2500);
        }
    });
    </script>

    <script>
    document.addEventListener("DOMContentLoaded", () => {
        fetch("https://api.exchangerate-api.com/v4/latest/USD")
            .then(res => res.json())
            .then(data => {
                const rate = data.rates.INR;
                convertDollarToRupee(rate);
            })
            .catch(err => console.error("Currency API Error:", err));
    });

    function convertDollarToRupee(rate) {
        // Select ALL text nodes containing $
        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);

        let node;
        while (node = walker.nextNode()) {
            if (node.nodeValue.includes("$")) {
                node.nodeValue = node.nodeValue.replace(/\$(\d+(\.\d+)?)/g, (match, amount) => {
                    return "₹" + (parseFloat(amount) * rate).toFixed(2);
                });
            }
        }
    }
    </script>

    <!-- <div id="imageZoomModal" class="zoom-modal">
        <span class="zoom-close">&times;</span>
        <img class="zoom-modal-content" id="zoomedImage">
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function() {

        const images = document.querySelectorAll(".zoom-image");
        const modal = document.getElementById("imageZoomModal");
        const modalImg = document.getElementById("zoomedImage");
        const closeBtn = document.querySelector(".zoom-close");

        images.forEach(function(img) {
            img.style.cursor = "zoom-in";

            img.addEventListener("click", function() {
                modal.style.display = "block";
                modalImg.src = this.src;
            });
        });

        // Close button
        closeBtn.onclick = function() {
            modal.style.display = "none";
        };

        // Close when clicking outside image
        modal.onclick = function(e) {
            if (e.target !== modalImg) {
                modal.style.display = "none";
            }
        };

    });
    </script> -->

    <script>
    document.addEventListener("DOMContentLoaded", function() {

        const zoomWrappers = document.querySelectorAll(".zoom-wrapper");

        zoomWrappers.forEach(wrapper => {
            const img = wrapper.querySelector("img");
            let zoomed = false;

            wrapper.addEventListener("click", function(e) {

                if (!zoomed) {
                    zoomed = true;
                    wrapper.classList.add("active");
                    img.style.transform = "scale(2)";
                } else {
                    zoomed = false;
                    wrapper.classList.remove("active");
                    img.style.transform = "scale(1)";
                    img.style.transformOrigin = "center center";
                }
            });

            wrapper.addEventListener("mousemove", function(e) {
                if (!zoomed) return;

                const rect = wrapper.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;

                img.style.transformOrigin = x + "% " + y + "%";
            });

        });

    });
    </script>

</body>

</html>