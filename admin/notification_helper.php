<?php
// Function to add a notification
function addNotification($conn, $type, $message, $link)
{
    if (!$conn) {
        // Fallback for situations where connection might be closed or not passed correctly
        return false;
    }

    $type = $conn->real_escape_string($type);
    $message = $conn->real_escape_string($message);
    $link = $conn->real_escape_string($link);

    $sql = "INSERT INTO notifications (type, message, link, is_read) VALUES ('$type', '$message', '$link', 0)";

    return $conn->query($sql);
}
?>