<?php
require_once 'check_session.php';
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = $_POST['request_id'];
    $order_id = $_POST['order_id'];
    $action = $_POST['action'];

    if ($action == 'approve') {
        // Start Transaction
        $conn->begin_transaction();

        try {
            // Update Request Status
            $sql_req = "UPDATE cancellation_requests SET status='approved' WHERE id=$request_id";
            if (!$conn->query($sql_req))
                throw new Exception("Error updating request: " . $conn->error);

            // Update Order Status
            $sql_ord = "UPDATE orders SET status='cancelled' WHERE id=$order_id";
            if (!$conn->query($sql_ord))
                throw new Exception("Error updating order: " . $conn->error);

            $conn->commit();
            header("Location: cancellation_requests.php?msg=approved");
        } catch (Exception $e) {
            $conn->rollback();
            echo "Failed: " . $e->getMessage();
        }

    } elseif ($action == 'reject') {
        $sql = "UPDATE cancellation_requests SET status='rejected' WHERE id=$request_id";
        if ($conn->query($sql) === TRUE) {
            header("Location: cancellation_requests.php?msg=rejected");
        } else {
            echo "Error: " . $conn->error;
        }
    }
} else {
    header("Location: cancellation_requests.php");
}
?>