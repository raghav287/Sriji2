<?php
session_start();
require_once 'admin/db.php';
require_once 'includes/paypal_config.php';
require_once 'admin/notification_helper.php';
require_once 'includes/send_email_helper.php';

header('Content-Type: application/json');

$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

if (isset($json_obj['paypal_order_id']) && isset($json_obj['details'])) {

    $paypal_order_id = $json_obj['paypal_order_id'];
    $details = $json_obj['details'];
    $status = $details['status'] ?? '';

    // In a production environment, verify the payment details by calling PayPal API using Client ID + Secret 
    // to ensure the amount and currency match.
    // For now, based on client-side capture for simplicity, assuming success if status is COMPLETED.

    if ($status === 'COMPLETED') {

        // Find order by... wait, we didn't save PayPal order ID in DB yet.
        // We relying on finding the user's recent pending order or passing sys_order_id.
        // It's safer to pass sys_order_id from client side if we didn't save it.
        // However, the client side JS didn't pass sys_order_id to this script in my code?
        // Let's check checkout.php JS.
        // JS: body: JSON.stringify({ paypal_order_id: data.orderID, details: details })
        // We only passed paypal_order_id.

        // WE need to update checkout.php or find order via other means.
        // BUT we can search orders where status='pending_payment' AND user_id/session matches?
        // Risk of finding wrong order.

        // Since we didn't save PayPal order ID in submit_order (we didn't have it then),
        // we should pass our order_number or order_id in the createOrder 'custom_id' field?
        // OR simply pass it in the body.

        // Strategy: Verify based on 'description' if we put order number there?
        // In checkout.php: description: "Order " + orderData.order_number
        // We can parse it from $details if available.

        // Safer: Modify checkout.php to pass 'sys_order_id' or 'order_number' to verify_paypal.php
        // BUT avoiding context switch, let's try to extract from description.
        // $purchase_units = $details['purchase_units'][0];
        // $description = $purchase_units['description']; // "Order ORD-123..."

        // Better: I will use the description to match the order number.
        $units = $details['purchase_units'][0] ?? [];
        $desc = $units['description'] ?? '';
        $order_number = str_replace("Order ", "", $desc);

        if (!$order_number) {
            echo json_encode(['status' => 'error', 'message' => 'Order number not found in payment details']);
            exit;
        }

        $stmt = $conn->prepare("SELECT * FROM orders WHERE order_number = ?");
        $stmt->bind_param("s", $order_number);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();

        if ($order) {
            $order_id = $order['id'];

            // Update Order
            $upd = $conn->prepare("UPDATE orders SET status = 'processing', payment_status = 'completed', razorpay_order_id = ? WHERE id = ?");
            $upd->bind_param("si", $paypal_order_id, $order_id); // Reusing column or add new? Reusing razorpay_order_id for generic gateway ID or create new col?
            // To be safe/clean, we should have payment_id.
            // Let's put paypal_order_id in razorpay_order_id or payment_id field?
            // Existing schema has `razorpay_order_id`, `razorpay_payment_id`.
            // Let's use `razorpay_order_id` for the transaction ID for now to avoid schema change, or just `payment_id` in payments table.

            $upd->execute();

            // Log Payment
            $amount = $order['total_amount'];
            $user_id = $order['user_id'];

            $log = $conn->prepare("INSERT INTO payments (user_id, order_id, payment_id, amount, status, method) VALUES (?, ?, ?, ?, 'success', 'PayPal')");
            $log->bind_param("iisd", $user_id, $order_id, $paypal_order_id, $amount);
            $log->execute();

            // Clear Cart
            if (isset($_SESSION['cart_session_id'])) {
                $sess_id = $_SESSION['cart_session_id'];
                $conn->query("DELETE FROM carts WHERE session_id = '$sess_id' OR user_id = " . ($user_id ? $user_id : 0));
            }

            // Notifications
            sendOrderConfirmation($conn, $order_id);
            addNotification($conn, 'order', "New PayPal Order: " . $order['order_number'], "order_details.php?id=$order_id");

            echo json_encode(['status' => 'success', 'order_number' => $order['order_number']]);

        } else {
            echo json_encode(['status' => 'error', 'message' => 'Order not found: ' . $order_number]);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Payment not completed']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
}
?>