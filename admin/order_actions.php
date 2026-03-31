<?php
require_once 'check_session.php';
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $status = $_POST['status'];

    $sql = "UPDATE orders SET status='$status' WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: order_details.php?id=$id&msg=updated");
    } else {
        echo "Error updating record: " . $conn->error;
    }
} else {
    header("Location: orders.php");
}

$conn->close();
?>