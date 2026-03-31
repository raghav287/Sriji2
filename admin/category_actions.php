<?php
require_once 'check_session.php';
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    // File Upload Handler
    $image_name = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../assets/uploads/categories/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $image_name = uniqid() . "." . $file_extension;
        $target_file = $target_dir . $image_name;

        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            echo "Sorry, there was an error uploading your file.";
            exit;
        }
    }

    if ($action == 'add') {
        $name = $conn->real_escape_string($_POST['name']);
        $status = $_POST['status'];
        $featured = isset($_POST['featured']) ? 1 : 0;

        $sql = "INSERT INTO categories (name, status, featured, image) VALUES ('$name', '$status', $featured, '$image_name')";
        if ($conn->query($sql) === TRUE) {
            header("Location: categories.php?msg=added");
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

    } elseif ($action == 'edit') {
        $id = $_POST['id'];
        $name = $conn->real_escape_string($_POST['name']);
        $status = $_POST['status'];
        $featured = isset($_POST['featured']) ? 1 : 0;

        if ($image_name != "") {
            // Update with new image
            $sql = "UPDATE categories SET name='$name', status='$status', featured=$featured, image='$image_name' WHERE id=$id";
        } else {
            // Keep existing image
            $sql = "UPDATE categories SET name='$name', status='$status', featured=$featured WHERE id=$id";
        }

        if ($conn->query($sql) === TRUE) {
            header("Location: categories.php?msg=updated");
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
} elseif (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = $_GET['id'];
    $sql = "DELETE FROM categories WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: categories.php?msg=deleted");
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

$conn->close();
?>