<?php
require_once 'admin/db.php';
require_once 'includes/razorpay_config.php';
require_once 'admin/notification_helper.php';
require_once 'includes/send_email_helper.php';

// Webhook Secret from Razorpay Dashboard
$webhook_secret = RAZORPAY_WEBHOOK_SECRET;

// Receive the Request
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

// Verify Signature
if (empty($payload) || empty($signature)) {
    http_response_code(400);
    exit();
}

try {
    $expected_signature = hash_hmac('sha256', $payload, $webhook_secret);
    if (!hash_equals($expected_signature, $signature)) {
        throw new Exception('Invalid Signature');
    }
} catch (Exception $e) {
    error_log("Razorpay Webhook Signature Verification Failed: " . $e->getMessage());
    http_response_code(400);
    exit();
}

// Process Event
$data = json_decode($payload, true);
$event = $data['event'] ?? '';

if ($event === 'payment.captured' || $event === 'order.paid') {
    $payment_entity = $data['payload']['payment']['entity'];

    $razorpay_payment_id = $payment_entity['id'];
    $razorpay_order_id = $payment_entity['order_id'];
    $amount = $payment_entity['amount'] / 100; // Amount is in paise

    // Find Order by Razorpay Order ID
    $stmt = $conn->prepare("SELECT id, user_id, status, total_amount FROM orders WHERE razorpay_order_id = ?");
    $stmt->bind_param("s", $razorpay_order_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $order_id = $row['id'];
        $user_id = $row['user_id'];
        $current_status = $row['status'];

        // Update Order if not already paid/completed
        // We check against 'completed' and 'processing' to avoid duplicate processing
        if ($current_status !== 'completed' && $current_status !== 'processing') {

            // 1. Update Order Status
            $upd = $conn->prepare("UPDATE orders SET status = 'processing', payment_status = 'completed', razorpay_payment_id = ? WHERE id = ?");
            $upd->bind_param("si", $razorpay_payment_id, $order_id);
            $upd->execute();

            // 2. Log to Payments Table
            // Check if payment log already exists for this payment_id to avoid duplicates
            $check_pay = $conn->prepare("SELECT id FROM payments WHERE payment_id = ?");
            $check_pay->bind_param("s", $razorpay_payment_id);
            $check_pay->execute();
            if ($check_pay->get_result()->num_rows == 0) {
                $status = 'success';
                $method = 'online';
                $log = $conn->prepare("INSERT INTO payments (user_id, order_id, payment_id, amount, status, method) VALUES (?, ?, ?, ?, ?, ?)");
                $log->bind_param("iisdss", $user_id, $order_id, $razorpay_payment_id, $amount, $status, $method);
                $log->execute();
            }

            // 3. Clear Cart (based on user_id)
            if ($user_id) {
                $conn->query("DELETE FROM carts WHERE user_id = $user_id");
            }

            // 4. Notification & Email
            addNotification($conn, 'order', "Webhook Payment Success: Order #$order_id", "order_details.php?id=$order_id");
            sendOrderConfirmation($conn, $order_id);
        }
    } else {
        error_log("Razorpay Webhook: Order not found for Razorpay Order ID: " . $razorpay_order_id);
    }
}

http_response_code(200);
?>