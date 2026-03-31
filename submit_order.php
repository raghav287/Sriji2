<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'admin/db.php';
require_once 'admin/notification_helper.php';
require_once 'includes/razorpay_config.php';
require_once 'includes/price_helper.php'; // For get_currency_multiplier

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 0. Helpers & Settings ---
    $multiplier = get_currency_multiplier();

    $s_res = $conn->query("SELECT * FROM site_settings");
    $settings = [];
    while ($r = $s_res->fetch_assoc())
        $settings[$r['setting_key']] = $r['setting_value'];

    $s_cap = floatval($settings['shipping_cap'] ?? 500) * $multiplier;
    $s_below = floatval($settings['shipping_charge_below'] ?? 50) * $multiplier;
    $s_above = floatval($settings['shipping_charge_above'] ?? 0) * $multiplier;
    $cod_active = ($settings['cod_active'] ?? '1') == '1';

    // COD Logic
    $c_cap = floatval($settings['cod_limit'] ?? 500) * $multiplier;
    $c_below = floatval($settings['cod_charge_below'] ?? ($settings['cod_charge'] ?? 20)) * $multiplier;
    $c_above = floatval($settings['cod_charge_above'] ?? 0) * $multiplier;


    // --- 1. Gather Data ---
    $name_input = $_POST['name'] ?? 'Guest';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $order_notes = $_POST['order_notes'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'COD';

    // Address handling (New vs Saved)
    $selected_addr = $_POST['selected_address'] ?? 'new';
    $final_address = '';
    $final_country = '';
    $final_state = '';
    $final_city = '';

    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if ($selected_addr === 'new') {
        $final_address = $_POST['address'] ?? '';
        $final_country = $_POST['country'] ?? '';
        $final_state = $_POST['state'] ?? '';
        $final_city = $_POST['city'] ?? '';
        $final_zip = $_POST['zip_code'] ?? '';

        if (!$user_id && $email) {
            $u_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $u_check->bind_param("s", $email);
            $u_check->execute();
            $u_res = $u_check->get_result();
            if ($u_res->num_rows > 0) {
                $u_row = $u_res->fetch_assoc();
                $user_id = $u_row['id'];
            } else {
                $password = password_hash('User@123', PASSWORD_BCRYPT);
                $ins_u = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
                $ins_u->bind_param("ssss", $name_input, $email, $phone, $password);
                if ($ins_u->execute()) {
                    $user_id = $ins_u->insert_id;
                }
            }
        }

        if ($user_id) {
            $ins_addr = $conn->prepare("INSERT INTO user_addresses (user_id, name, email, phone, country, state, city, zip_code, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ins_addr->bind_param("issssssss", $user_id, $name_input, $email, $phone, $final_country, $final_state, $final_city, $final_zip, $final_address);
            $ins_addr->execute();
        }

    } else {
        $addr_id = intval($selected_addr);
        $fetch_addr = $conn->query("SELECT * FROM user_addresses WHERE id = $addr_id");
        if ($fetch_addr->num_rows > 0) {
            $a_row = $fetch_addr->fetch_assoc();
            $final_address = $a_row['address'];
            $final_country = $a_row['country'];
            $final_state = $a_row['state'];
            $final_city = $a_row['city'];
            $final_zip = $a_row['zip_code'];
            $phone = $a_row['phone'];
            $name_input = $a_row['name'];
            $email = $a_row['email'];
        }
    }

    $full_shipping_address = "$final_address, $final_city, $final_state, $final_country - $final_zip";

    // --- 2. Calculate Total from Cart ---
    if (!isset($_SESSION['cart_session_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
        exit;
    }
    $c_sess = $_SESSION['cart_session_id'];
    $c_where = $user_id ? "(c.user_id = $user_id OR c.session_id = '$c_sess')" : "c.session_id = '$c_sess'";

    $cart_sql = "SELECT c.id, c.product_id, c.variant_id, c.quantity, 
                   (SELECT MIN(v2.price) FROM product_variants v2 JOIN product_sizes s2 ON v2.product_size_id = s2.id WHERE s2.product_id = c.product_id) as base_price,
                   v.price as variant_price
                 FROM carts c 
                 LEFT JOIN product_variants v ON c.variant_id = v.id
                 WHERE $c_where";

    $c_res = $conn->query($cart_sql);
    $cart_subtotal = 0;
    $order_items = [];

    while ($row = $c_res->fetch_assoc()) {
        $price = $row['variant_price'] ? $row['variant_price'] : $row['base_price'];
        if (!$price)
            $price = 0;

        // Apply Multiplier
        $final_price = $price * $multiplier;

        $cart_subtotal += $final_price * $row['quantity'];
        $row['final_price'] = $final_price;
        $order_items[] = $row;
    }

    if ($cart_subtotal <= 0 || empty($order_items)) {
        echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
        exit;
    }

    // Apply Shipping
    $shipping_cost = ($cart_subtotal < $s_cap) ? $s_below : $s_above;

    // Apply COD Fee
    $final_cod_charge = 0;
    if ($payment_method === 'COD' && $cod_active) {
        $final_cod_charge = ($cart_subtotal < $c_cap) ? $c_below : $c_above;
    }

    $grand_total = $cart_subtotal + $shipping_cost + $final_cod_charge;

    // --- 3. Create Order in DB ---
    $order_number = "ORD-" . time() . "-" . rand(100, 999);
    $status = 'pending';
    if ($payment_method === 'Online') {
        $status = 'pending_payment';
    }

    // Insert Order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, customer_name, customer_email, customer_phone, shipping_address, total_amount, status, payment_method, payment_status, order_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
    $stmt->bind_param("isssssdsss", $user_id, $order_number, $name_input, $email, $phone, $full_shipping_address, $grand_total, $status, $payment_method, $order_notes);

    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Order creation failed: ' . $stmt->error]);
        exit;
    }
    $order_id = $stmt->insert_id;

    // Move Items to order_items
    $item_sql = "INSERT INTO order_items (order_id, product_id, variant_id, quantity, price) VALUES (?, ?, ?, ?, ?)";
    $item_stmt = $conn->prepare($item_sql);
    foreach ($order_items as $item) {
        $v_id = $item['variant_id'];
        $item_stmt->bind_param("iiiid", $order_id, $item['product_id'], $v_id, $item['quantity'], $item['final_price']);
        $item_stmt->execute();
    }

    // --- 4. Handle Payment Method ---
    if ($payment_method === 'COD') {
        // Clear Cart
        $delete_where = str_replace('c.', '', $c_where);
        $conn->query("DELETE FROM carts WHERE $delete_where");

        // Send Email
        require_once 'includes/send_email_helper.php';
        sendOrderConfirmation($conn, $order_id);

        addNotification($conn, 'order', "New Order ($payment_method): $order_number", "order_details.php?id=$order_id");
        echo json_encode([
            'status' => 'success',
            'message' => 'Order placed successfully',
            'order_number' => $order_number,
            'redirect_url' => 'cod-success.php?order_number=' . $order_number . '&amount=' . $grand_total
        ]);

    } elseif ($payment_method === 'Online') {
        // Call Razorpay API
        $api_key = RAZORPAY_KEY_ID;
        $api_secret = RAZORPAY_KEY_SECRET;

        $url = "https://api.razorpay.com/v1/orders";
        $data = [
            'amount' => $grand_total * 100, // In paise
            'currency' => 'INR', // Always INR as per current system config
            'receipt' => $order_number,
            'payment_capture' => 1
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$api_key:$api_secret");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $resp = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $r_data = json_decode($resp, true);

        if ($http_code === 200 && isset($r_data['id'])) {
            $rzp_order_id = $r_data['id'];

            $upd = $conn->prepare("UPDATE orders SET razorpay_order_id = ? WHERE id = ?");
            $upd->bind_param("si", $rzp_order_id, $order_id);
            $upd->execute();

            echo json_encode([
                'status' => 'razorpay_init',
                'razorpay_order_id' => $rzp_order_id,
                'amount' => $grand_total * 100,
                'sys_order_id' => $order_id
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Payment gateway initialization failed']);
        }

    } elseif ($payment_method === 'PayPal') {
        // PayPal Initialization
        include 'includes/paypal_config.php';

        $inr_to_usd = getUsdRate($conn);
        $amount_usd = round($grand_total / $inr_to_usd, 2);

        echo json_encode([
            'status' => 'paypal_init',
            'amount_usd' => $amount_usd,
            'amount_inr' => $grand_total,
            'sys_order_id' => $order_id,
            'order_number' => $order_number
        ]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}
?>