<?php
require_once 'check_session.php';
require_once 'db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM contact_messages WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: contact_messages.php?msg=deleted");
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    header("Location: contact_messages.php");
}
$conn->close();
?>