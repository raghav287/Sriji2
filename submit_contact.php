<?php
require_once 'admin/db.php';
require_once 'admin/notification_helper.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);

    $sql = "INSERT INTO contact_messages (name, email, phone, subject, message) VALUES ('$name', '$email', '$phone', '$subject', '$message')";

    if ($conn->query($sql) === TRUE) {
        $last_id = $conn->insert_id;

        // Add Notification
        addNotification($conn, 'contact', "New Inquiry: $subject", "message_details.php?id=$last_id");

        // Redirect back with success
        header("Location: contact.php?status=success");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    header("Location: contact.php");
}
?>