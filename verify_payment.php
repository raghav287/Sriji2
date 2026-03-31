<?php
session_start();
require_once 'admin/db.php';
require_once 'includes/razorpay_config.php';
require_once 'admin/notification_helper.php';
require_once 'includes/send_email_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $razorpay_payment_id = $_POST['razorpay_payment_id'] ?? '';
    $razorpay_order_id = $_POST['razorpay_order_id'] ?? '';
    $razorpay_signature = $_POST['razorpay_signature'] ?? '';
    $order_id = intval($_POST['order_id'] ?? 0);

    if (empty($razorpay_payment_id) || empty($razorpay_order_id) || empty($razorpay_signature) || !$order_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
        exit;
    }

    // Verify Signature
    $api_secret = RAZORPAY_KEY_SECRET;
    $generated_signature = hash_hmac('sha256', $razorpay_order_id . "|" . $razorpay_payment_id, $api_secret);

    if ($generated_signature === $razorpay_signature) {
        // Payment Successful

        // 1. Fetch Order
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();

        if ($order) {
            // 2. Update Order Status
            $upd = $conn->prepare("UPDATE orders SET status = 'processing', payment_status = 'completed', razorpay_payment_id = ? WHERE id = ?");
            $upd->bind_param("si", $razorpay_payment_id, $order_id);
            $upd->execute();

            // 3. Log to Payments Table
            $amount = $order['total_amount']; // Assuming exact match
            $user_id = $order['user_id'];

            $log = $conn->prepare("INSERT INTO payments (user_id, order_id, payment_id, amount, status, method) VALUES (?, ?, ?, ?, 'success', 'online')");
            $log->bind_param("iisd", $user_id, $order_id, $razorpay_payment_id, $amount);
            $log->execute();

            // 4. Clear Cart
            if (isset($_SESSION['cart_session_id'])) {
                $sess_id = $_SESSION['cart_session_id'];
                $conn->query("DELETE FROM carts WHERE session_id = '$sess_id' OR user_id = " . ($user_id ? $user_id : 0));
            }

            // 5. Send Email & Notification
            sendOrderConfirmation($conn, $order_id);
            addNotification($conn, 'order', "New Online Order: " . $order['order_number'], "order_details.php?id=$order_id");

            echo json_encode([
                'status' => 'success',
                'order_number' => $order['order_number']
            ]);

        } else {
            echo json_encode(['status' => 'error', 'message' => 'Order not found']);
        }

    } else {
        // Payment Verification Failed (Signature Mismatch)
        $conn->query("INSERT INTO payments (order_id, payment_id, amount, status, method, error_message) VALUES ($order_id, '$razorpay_payment_id', 0, 'failed', 'online', 'Signature Mismatch')");

        echo json_encode(['status' => 'error', 'message' => 'Payment verification failed']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}
?>