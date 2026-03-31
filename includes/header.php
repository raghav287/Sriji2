<header class="header_2">
    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Determine path prefix
    $header_path_prefix = (basename(dirname($_SERVER['PHP_SELF'])) == 'user') ? '../' : '';

    // Ensure DB connection
    if (!isset($conn)) {
        require_once($header_path_prefix . 'admin/db.php');
    }
    require_once($header_path_prefix . 'includes/price_helper.php');

    // Fetch Active Categories for Header
    $header_categories = [];
    if (isset($conn)) {
        $h_cat_sql = "SELECT * FROM categories WHERE status='active'";
        $h_cat_res = $conn->query($h_cat_sql);
        if ($h_cat_res->num_rows > 0) {
            while ($row = $h_cat_res->fetch_assoc()) {
                $header_categories[] = $row;
            }
        }
    }
    ?>
    <style>
    /* ===== Header Support Responsive Fix ===== */

    .header_support {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 20px;
        flex-wrap: wrap;
        /* IMPORTANT */
    }

    .support_item {
        display: flex;
        align-items: center;
        font-size: 14px;
        white-space: nowrap;
    }

    .support_item i {
        color: #c59d5f;
        margin-right: 6px;
        font-size: 14px;
    }

    .support_item a {
        color: #333;
        text-decoration: none;
    }

    /* ===== Tablet Fix ===== */
    @media (max-width: 1200px) {
        .header_support {
            gap: 10px;
        }

        .support_item {
            font-size: 13px;
        }
    }

    /* ===== Small Laptop Fix ===== */
    @media (max-width: 992px) {
        .header_support {
            justify-content: center;
        }
    }

    /* ===== Mobile (if you ever show it) ===== */
    @media (max-width: 768px) {
        .header_support {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }

        .support_item {
            white-space: normal;
            /* allow wrapping email */
        }
    }

    .search_results_dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: #fff;
        border: 1px solid #e1e1e1;
        border-top: none;
        z-index: 99999;
        max-height: 400px;
        overflow-y: auto;
        display: none;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border-radius: 0 0 8px 8px;
    }

    .search_results_dropdown ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .search_results_dropdown li {
        border-bottom: 1px solid #f5f5f5;
    }

    .search_results_dropdown li:last-child {
        border-bottom: none;
    }

    .search_results_dropdown a {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        text-decoration: none;
        color: #222;
        transition: all 0.3s ease;
    }

    .search_results_dropdown a:hover {
        background: #f8f9fa;
    }

    .search_results_dropdown .img_box {
        width: 50px;
        height: 50px;
        min-width: 50px;
        margin-right: 15px;
        border-radius: 6px;
        overflow: hidden;
        background: #f1f1f1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .search_results_dropdown .img_box img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover;
        display: block;
    }

    .search_results_dropdown .info {
        flex: 1;
    }

    .search_results_dropdown .info h5 {
        font-size: 15px;
        margin: 0 0 4px;
        font-weight: 600;
        color: #333;
        line-height: 1.2;
    }

    .search_results_dropdown .info span {
        font-size: 13px;
        color: #e53637;
        /* Theme color ideally */
        font-weight: 500;
    }

    /* Scrollbar Styling */
    .search_results_dropdown::-webkit-scrollbar {
        width: 6px;
    }

    .search_results_dropdown::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .search_results_dropdown::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 3px;
    }

    .search_results_dropdown::-webkit-scrollbar-thumb:hover {
        background: #aaa;
    }

    .header_search_form {
        position: relative;
    }
    </style>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-2">
                <div class="header_logo_area">
                    <a href="<?php echo $header_path_prefix; ?>index" class="header_logo">
                        <img src="<?php echo $header_path_prefix; ?>assets/images/logo/logo.png" alt="Zenis"
                            class="img-fluid w-100">
                    </a>
                    <div class="mobile_menu_icon d-block d-lg-none" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">
                        <span class="mobile_menu_icon"><i class="far fa-stream menu_icon_bar"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-xxl-6 col-xl-5 col-lg-5 d-none d-lg-block">
                <form action="#" class="header_search_form">
                    <div class="input w-100">
                        <input type="text" id="searchInput" placeholder="Search your product..." autocomplete="off">
                        <button type="submit"><i class="far fa-search"></i></button>
                    </div>
                    <div id="searchResults" class="search_results_dropdown"></div>
                </form>
            </div>
            <div class="col-xxl-4 col-xl-5 col-lg-5 d-none d-lg-flex">
                <div class="header_support_user">

                    <div class="header_support">

                        <div class="support_item">
                            <i class="far fa-phone-alt"></i>
                            <a href="tel:9115936593">+91 9115936593</a>
                        </div>

                        <div class="support_item">
                            <i class="far fa-envelope"></i>
                            <a href="mailto:info@srijivastrashingarsewa.com">
                                info@srijivastrashingarsewa.com
                            </a>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</header>

<!--=========================
        MENU 2 START
    ==========================-->
<nav class="main_menu_2 main_menu d-none d-lg-block">
    <div class="container">
        <div class="row">
            <div class="col-12 d-flex flex-wrap">
                <div class="main_menu_area">
                    <div class="menu_category_area">
                        <a href="#" class="menu_logo d-none">
                            <img src="<?php echo $header_path_prefix; ?>assets/images/logo/logo.svg" alt="Zenis"
                                class="img-fluid w-100">
                        </a>
                        <div class="menu_category_bar">
                            <p>
                                <span>
                                    <img src="<?php echo $header_path_prefix; ?>assets/images/bar_icon_white.svg"
                                        alt="category icon">
                                </span>
                                Browse Categories
                            </p>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <ul class="menu_cat_item">
                            <?php foreach ($header_categories as $h_cat):
                                $h_img = !empty($h_cat['image']) ? $header_path_prefix . "assets/uploads/categories/" . $h_cat['image'] : $header_path_prefix . "assets/images/category_list_icon_1.png";
                                ?>
                            <li>
                                <a
                                    href="<?php echo $header_path_prefix; ?>shop.php?category=<?php echo $h_cat['id']; ?>">
                                    <span><img src="<?php echo $h_img; ?>"
                                            alt="<?php echo htmlspecialchars($h_cat['name']); ?>"
                                            style="width: 20px; height: 20px; object-fit: contain;"></span>
                                    <?php echo htmlspecialchars($h_cat['name']); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <ul class="menu_item">
                        <li><a class="active" href="<?php echo $header_path_prefix; ?>/">Home</a></li>
                        <li><a href="<?php echo $header_path_prefix; ?>shop">Shop</a></li>
                        <li><a href="<?php echo $header_path_prefix; ?>about_us">About Us</a></li>
                        <li><a href="<?php echo $header_path_prefix; ?>track-order">Track Order</a></li>
                        <li><a href="<?php echo $header_path_prefix; ?>contact">Contact</a></li>
                    </ul>
                    <ul class="menu_icon">
                        <li>
                            <a data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
                                aria-controls="offcanvasRight">
                                <b>
                                    <img src="<?php echo $header_path_prefix; ?>assets/images/cart_black.svg" alt="cart"
                                        class="img-fluid">
                                </b>
                                <span id="header_cart_count">0</span>
                            </a>
                        </li>
                        <?php
                        $is_logged_in = isset($_SESSION['user_id']);
                        $user_name = $is_logged_in ? ($_SESSION['user_name'] ?? 'User') : 'Guest';
                        ?>
                        <li>
                            <a class="user" href="#">
                                <b>
                                    <img src="<?php echo $header_path_prefix; ?>assets/images/user_icon_black.svg"
                                        alt="user" class="img-fluid">
                                </b>
                                <h5> <?php echo htmlspecialchars($user_name); ?></h5>
                            </a>
                            <ul class="user_dropdown">
                                <?php if ($is_logged_in): ?>
                                <li>
                                    <a href="<?php echo $header_path_prefix; ?>user/dashboard.php">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                                        </svg>
                                        Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $header_path_prefix; ?>user/profile.php">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                        My Account
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $header_path_prefix; ?>user/orders.php">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                        </svg>
                                        My Orders
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $header_path_prefix; ?>user/logout.php">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                                        </svg>
                                        Logout
                                    </a>
                                </li>
                                <?php else: ?>
                                <li>
                                    <a href="<?php echo $header_path_prefix; ?>sign-in.php">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                        </svg>
                                        Sign In
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $header_path_prefix; ?>sign-up.php">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                        </svg>
                                        Sign Up
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="mini_cart">
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel"> my cart <span id="drawer_cart_count">(0)</span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"><i
                    class="far fa-times"></i></button>
        </div>
        <div class="offcanvas-body">
            <ul id="mini_cart_list">
                <!-- Cart Items will be loaded here -->
            </ul>
            <h5>sub total <span id="mini_cart_subtotal">₹0.00</span></h5>
            <div class="minicart_btn_area">
                <a class="common_btn" href="<?php echo $header_path_prefix; ?>cart.php">view cart</a>
            </div>
        </div>
    </div>
</div>
<!--=========================
        MENU 2 END
    ==========================-->


<!--============================
        MOBILE MENU START
    ==============================-->
<div class="mobile_menu_area">
    <div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions">
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"><i
                class="fal fa-times"></i></button>
        <div class="offcanvas-body">
            <ul class="mobile_menu_header d-flex flex-wrap">
                <li>
                    <a data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                        <b><img src="<?php echo $header_path_prefix; ?>assets/images/cart_black.svg" alt="cart"
                                class="img-fluid"></b>
                        <span id="mobile_cart_count">0</span>
                    </a>
                </li>
                <li>
                    <a
                        href="<?php echo $is_logged_in ? $header_path_prefix . 'user/dashboard.php' : $header_path_prefix . 'sign-in.php'; ?>">
                        <b><img src="<?php echo $header_path_prefix; ?>assets/images/user_icon_black.svg" alt="user"
                                class="img-fluid"></b>
                    </a>
                </li>
            </ul>

            <form class="mobile_menu_search" onsubmit="return false;">
                <input type="text" placeholder="Search" id="mobileSearchInput">
                <button type="submit"><i class="far fa-search"></i></button>
            </form>
            <div id="mobileSearchResults" class="search_results_dropdown"
                style="position:relative; top:0; box-shadow:none; border:none; max-height:none;"></div>

            <div class="mobile_menu_item_area">
                <ul class="nav nav-pills" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home"
                            aria-selected="true">Categories</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile"
                            aria-selected="false">menu</button>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel"
                        aria-labelledby="pills-home-tab" tabindex="0">
                        <ul class="main_mobile_menu">
                            <?php foreach ($header_categories as $h_cat): ?>
                            <li><a
                                    href="<?php echo $header_path_prefix; ?>shop.php?category=<?php echo $h_cat['id']; ?>"><?php echo htmlspecialchars($h_cat['name']); ?></a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab"
                        tabindex="0">
                        <ul class="main_mobile_menu">
                            <li><a href="<?php echo $header_path_prefix; ?>index.php">Home</a></li>
                            <li><a href="<?php echo $header_path_prefix; ?>shop.php">Shop</a></li>
                            <li><a href="<?php echo $header_path_prefix; ?>about_us.php">About Us</a></li>
                            <li><a href="<?php echo $header_path_prefix; ?>track-order.php">Track Order</a></li>
                            <li><a href="<?php echo $header_path_prefix; ?>contact.php">Contact Us</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--============================
        MOBILE MENU END
    ==============================-->
<script>
function loadMiniCart() {
    const prefix = "<?php echo $header_path_prefix; ?>";
    fetch(prefix + 'includes/cart_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'action=fetch'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const headerCount = document.getElementById('header_cart_count');
                if (headerCount) headerCount.innerText = data.count;

                const drawerCount = document.getElementById('drawer_cart_count');
                if (drawerCount) drawerCount.innerText = '(' + data.count + ')';

                const mobileCount = document.getElementById('mobile_cart_count');
                if (mobileCount) mobileCount.innerText = data.count;

                const subtotal = document.getElementById('mini_cart_subtotal');
                if (subtotal) subtotal.innerText = '₹' + parseFloat(data.total).toFixed(2);

                const list = document.getElementById('mini_cart_list');
                if (list) {
                    list.innerHTML = '';

                    if (data.items.length === 0) {
                        list.innerHTML = '<li style="text-align:center;">Your cart is empty</li>';
                    } else {
                        data.items.forEach(item => {
                            // Adjust image path for cart items if needed
                            // Backend usually returns relative to root assets/...
                            // If we are in user/, we need ../assets/...
                            // But data.items seems to already contain image_path. 
                            // Let's check how image_path is stored. 
                            // checkout.php: 'assets/uploads/products/' . $item['image_path']
                            // If I am in user/, I need to prepend prefix.
                            let imgPath = item.image || item.image_path; // Cart action might return 'image'
                            // If path already has ../ dont add it?
                            // Better: cart_actions usually returns what checkout.php does.
                            // checkout said: $item['image'] = 'assets/uploads/products/'...

                            // Let's simplify: Prepend user prefix to the path returned.
                            // If path doesn't start with http...
                            if (imgPath && !imgPath.startsWith('http') && !imgPath.startsWith('../')) {
                                imgPath = prefix + imgPath;
                            }

                            const li = document.createElement('li');
                            li.innerHTML = `
                            <a href="${prefix}shop-details.php?id=${item.product_id}" class="cart_img">
                                <img src="${imgPath}" alt="product" class="img-fluid w-100">
                            </a>
                            <div class="cart_text">
                                <a class="cart_title" href="${prefix}shop-details.php?id=${item.product_id}">${item.name}</a>
                                <p>₹${parseFloat(item.price).toFixed(2)} x ${item.quantity}</p>
                                ${item.size ? `<span><b>Size:</b> ${item.size}</span>` : ''}
                                ${item.color ? `<span><b>Color:</b> ${item.color}</span>` : ''}
                            </div>
                            <a class="del_icon" href="#" onclick="removeFromCart(${item.cart_id}, event)"><i class="fal fa-times"></i></a>
                        `;
                            list.appendChild(li);
                        });
                    }
                }
            }
        })
        .catch(error => console.error('Error loading cart:', error));
}

function removeFromCart(cartId, event) {
    if (event) event.preventDefault();
    if (!confirm('Remove this item?')) return;

    const prefix = "<?php echo $header_path_prefix; ?>";

    fetch(prefix + 'includes/cart_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'action=remove&cart_id=' + cartId
        })
        .then(resp => resp.json())
        .then(data => {
            if (data.status === 'success') {
                loadMiniCart();
                // If on cart page, maybe reload there too?
                if (window.location.pathname.includes('cart.php')) location.reload();
            }
        });
}

document.addEventListener('DOMContentLoaded', () => {
    loadMiniCart();

    // Search Logic (Desktop & Mobile)
    function initSearch(inputId, resultsId) {
        const searchInput = document.getElementById(inputId);
        const searchResults = document.getElementById(resultsId);
        const prefix = "<?php echo $header_path_prefix; ?>";
        let debounceTimer;

        if (searchInput && searchResults) {
            searchInput.addEventListener('input', function() {
                const term = this.value.trim();
                clearTimeout(debounceTimer);

                if (term.length < 2) {
                    searchResults.style.display = 'none';
                    searchResults.innerHTML = '';
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetch(prefix + 'includes/search_products.php?term=' + encodeURIComponent(
                            term))
                        .then(res => {
                            if (!res.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return res.text().then(text => {
                                try {
                                    return JSON.parse(text);
                                } catch (e) {
                                    console.log('Search Raw Response:', text);
                                    throw new Error('Invalid JSON');
                                }
                            });
                        })
                        .then(data => {
                            // console.log('Search Data:', data);
                            if (data.status === 'success' && data.data.length > 0) {
                                let html = '<ul>';
                                data.data.forEach(prod => {
                                    let img = prod.image;
                                    // Handle image path
                                    if (img && !img.startsWith('http') && !img
                                        .startsWith('../')) {
                                        img = prefix + img;
                                    }

                                    html += `<li>
                                            <a href="${prefix}shop-details.php?id=${prod.id}">
                                                <div class="img_box">
                                                    <img src="${img}" alt="${prod.name}">
                                                </div>
                                                <div class="info">
                                                    <h5>${prod.name}</h5>
                                                    <span>₹${parseFloat(prod.price).toFixed(2)}</span>
                                                </div>
                                            </a>
                                        </li>`;
                                });
                                html += '</ul>';
                                searchResults.innerHTML = html;
                                searchResults.style.display = 'block';
                            } else {
                                searchResults.innerHTML =
                                    '<div style="padding:10px;">No products found</div>';
                                searchResults.style.display = 'block';
                            }
                        })
                        .catch(err => {
                            console.error('Search Error:', err);
                            // Optional: Show error in UI
                        });
                }, 300);
            });

            // Hide on click outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });
        }
    }

    initSearch('searchInput', 'searchResults');
    initSearch('mobileSearchInput', 'mobileSearchResults');
});
</script>