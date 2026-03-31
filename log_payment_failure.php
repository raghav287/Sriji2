<?php
session_start();
require_once 'admin/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $payment_id = $_POST['payment_id'] ?? '';
    $error_description = $_POST['error_description'] ?? 'Unknown Error';
    $error_code = $_POST['error_code'] ?? '';

    if ($order_id) {
        $stmt = $conn->prepare("SELECT user_id, total_amount FROM orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $order = $res->fetch_assoc();
            $user_id = $order['user_id'];
            $amount = $order['total_amount'];

            // Log to payments
            $ins = $conn->prepare("INSERT INTO payments (user_id, order_id, payment_id, amount, status, method, error_message) VALUES (?, ?, ?, ?, 'failed', 'online', ?)");
            $ins->bind_param("iisds", $user_id, $order_id, $payment_id, $amount, $error_description);
            $ins->execute();

            // Optionally update order status to failed/cancelled if needed, but 'pending_payment' is fine.
        }
    }

    echo json_encode(['status' => 'success']);
}
?>