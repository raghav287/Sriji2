<?php
require_once 'check_session.php';
require_once 'db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch the link first
    $sql_link = "SELECT link FROM notifications WHERE id = $id";
    $result = $conn->query($sql_link);
    $link = "index.php"; // Default fallback
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $link = $row['link'];
    }

    // Mark as read
    $sql_update = "UPDATE notifications SET is_read = 1 WHERE id = $id";
    $conn->query($sql_update);

    // Redirect
    header("Location: " . $link);
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>