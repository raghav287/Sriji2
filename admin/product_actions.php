<?php
require_once 'check_session.php';
require_once 'db.php';

$target_dir = "../assets/uploads/products/";

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'delete') {
        $id = $_GET['id'];

        // Delete images from folder first
        $res = $conn->query("SELECT image_path FROM product_images WHERE product_id=$id");
        while ($row = $res->fetch_assoc()) {
            if (file_exists($target_dir . $row['image_path'])) {
                unlink($target_dir . $row['image_path']);
            }
        }

        // Delete Product (Cascade will handle DB rows for variants, specs, images)
        $sql = "DELETE FROM products WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            header("Location: products.php?msg=deleted");
        } else {
            echo "Error deleting record: " . $conn->error;
        }

    } elseif ($action == 'delete_image') {
        $id = $_GET['id'];
        $product_id = $_GET['product_id'];

        // Get image path
        $res = $conn->query("SELECT image_path FROM product_images WHERE id=$id");
        if ($row = $res->fetch_assoc()) {
            if (file_exists($target_dir . $row['image_path'])) {
                unlink($target_dir . $row['image_path']);
            }
        }

        $sql = "DELETE FROM product_images WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            header("Location: product_form.php?id=$product_id&msg=imagedeleted");
        } else {
            echo "Error deleting image: " . $conn->error;
        }
    }
}

$conn->close();
?>