<?php
session_start();
include '../admin/db.php';

// Create a session ID if not exists
if (!isset($_SESSION['cart_session_id'])) {
    $_SESSION['cart_session_id'] = session_id();
}
$session_id = $_SESSION['cart_session_id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Helper function to get cart identifier clause
require_once 'price_helper.php';

function getCartClause($conn, $user_id, $session_id)
{
    if ($user_id) {
        return "user_id = " . intval($user_id);
    } else {
        return "session_id = '" . $conn->real_escape_string($session_id) . "'";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);
        $variant_id = isset($_POST['variant_id']) && !empty($_POST['variant_id']) ? intval($_POST['variant_id']) : null;

        $user_clause = getCartClause($conn, $user_id, $session_id);

        // Check if item already exists
        $check_sql = "SELECT id, quantity FROM carts WHERE product_id = ? AND " . ($variant_id ? "variant_id = ?" : "variant_id IS NULL") . " AND $user_clause";
        $stmt = $conn->prepare($check_sql);

        if ($variant_id) {
            $stmt->bind_param("ii", $product_id, $variant_id);
        } else {
            $stmt->bind_param("i", $product_id);
        }
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            // Update quantity
            $row = $res->fetch_assoc();
            $new_qty = $row['quantity'] + $quantity;
            $update_sql = "UPDATE carts SET quantity = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $new_qty, $row['id']);
            $update_stmt->execute();
        } else {
            // Insert new
            $insert_sql = "INSERT INTO carts (user_id, session_id, product_id, variant_id, quantity) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("isiii", $user_id, $session_id, $product_id, $variant_id, $quantity);
            $insert_stmt->execute();
        }

        echo json_encode(['status' => 'success', 'message' => 'Item added to cart']);

    } elseif ($action === 'update') {
        $cart_id = intval($_POST['cart_id']);
        $quantity = intval($_POST['quantity']);

        if ($quantity > 0) {
            $update_sql = "UPDATE carts SET quantity = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ii", $quantity, $cart_id);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Cart updated']);
        } else {
            // Remove if qty is 0 or less (though frontend handles min 1)
            $del_sql = "DELETE FROM carts WHERE id = ?";
            $stmt = $conn->prepare($del_sql);
            $stmt->bind_param("i", $cart_id);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Item removed']);
        }

    } elseif ($action === 'remove') {
        $cart_id = intval($_POST['cart_id']);
        $del_sql = "DELETE FROM carts WHERE id = ?";
        $stmt = $conn->prepare($del_sql);
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        echo json_encode(['status' => 'success', 'message' => 'Item removed']);

    } elseif ($action === 'fetch') {
        $user_clause = getCartClause($conn, $user_id, $session_id);

        $sql = "SELECT c.id as cart_id, c.quantity, p.name, p.id as product_id, 
                       pi.image_path, 
                       s.size, v.color, v.price as variant_price, 
                       (SELECT MIN(v2.price) FROM product_variants v2 JOIN product_sizes s2 ON v2.product_size_id = s2.id WHERE s2.product_id = p.id) as base_price
                FROM carts c 
                JOIN products p ON c.product_id = p.id 
                LEFT JOIN product_variants v ON c.variant_id = v.id 
                LEFT JOIN product_sizes s ON v.product_size_id = s.id
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                WHERE $user_clause
                GROUP BY c.id"; // Group just in case image join duplicates (though is_primary=1 should be unique ideally)

        $result = $conn->query($sql);
        $items = [];
        $total = 0;
        $count = 0;

        while ($row = $result->fetch_assoc()) {
            $price = $row['variant_price'] ? $row['variant_price'] : $row['base_price'];
            // Fallback price if no variant logic needs closer look, but we usually have variants. 
            // If no variants for product, logic might need adjustment, but assuming variants for now as per schema.
            // Actually, if variant_id is null, we need a price. 
            // Let's assume for now products are variant based. If not, we might need a product price column.
            // Based on schema, products don't have price column, so we rely on variants.

            if (!$price)
                $price = 0;

            // Apply centralized pricing logic (Zone Rules)
            $price = calculate_price($price, $row['size']);

            $row['price'] = $price;
            $row['subtotal'] = $price * $row['quantity'];
            $row['image_path'] = $row['image_path'] ? 'assets/uploads/products/' . $row['image_path'] : 'assets/images/no_image.png';

            $total += $row['subtotal'];
            $count += 1; // Item count. or $count += $row['quantity'] for totalqty
            $items[] = $row;
        }

        echo json_encode([
            'status' => 'success',
            'items' => $items,
            'total' => $total,
            'count' => $count
        ]);
    }

} else {
    // If GET request needed later?
}
?>