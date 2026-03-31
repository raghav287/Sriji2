<?php
require_once '../admin/db.php';

header('Content-Type: application/json');

if (isset($_GET['term'])) {
    $term = $_GET['term'];
    $term = "%" . $conn->real_escape_string($term) . "%";

    $sql = "SELECT p.id, p.name, 
            (SELECT MIN(v.price) FROM product_variants v JOIN product_sizes s ON v.product_size_id = s.id WHERE s.product_id = p.id) as price,
            (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
            FROM products p 
            WHERE p.name LIKE ? AND p.status = 'active'
            LIMIT 10";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $term);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $row['image'] = !empty($row['image']) ? 'assets/uploads/products/' . $row['image'] : 'assets/images/no_image.png';
        $products[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $products]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No search term provided']);
}
?>