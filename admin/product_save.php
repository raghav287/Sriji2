<?php
require_once 'check_session.php';
require_once 'db.php';

// Create uploads directory if not exists
$target_dir = "../assets/uploads/products/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if POST is empty but Content-Length is set (sign of post_max_size exceeded)
    if (empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $max_size = ini_get('post_max_size');
        die("<div style='color:red; padding:20px; font-family:sans-serif; text-align:center; margin-top:50px;'>
                <h2>Upload Failed</h2>
                <p>The total size of your uploaded files exceeds the server limit (post_max_size = <strong>$max_size</strong>).</p>
                <p>Please try uploading fewer images at a time or resize them before uploading.</p>
                <a href='javascript:history.back()' style='padding:10px 20px; background:#ddd; text-decoration:none; border-radius:5px;'>Go Back</a>
             </div>");
    }

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : 'active';

    // 1. SAVE PRODUCT
    if ($product_id) {
        // UPDATE Product
        $sql = "UPDATE products SET name='$name', category_id='$category_id', description='$description', status='$status' WHERE id=$product_id";
        $conn->query($sql);
    } else {
        // INSERT Product
        $sql = "INSERT INTO products (name, category_id, description, status) VALUES ('$name', '$category_id', '$description', '$status')";
        if ($conn->query($sql) === TRUE) {
            $product_id = $conn->insert_id;
        } else {
            die("Error creating product: " . $conn->error);
        }
    }

    // 2. SAVE VARIANTS (NEW SIMPLIFIED STRUCTURE)
    // Structure: $_POST['variants'] = [ index => [id (optional), size, color, price, strike_price, stock] ]

    $submitted_variants = isset($_POST['variants']) ? $_POST['variants'] : [];
    $kept_size_ids = [];
    $kept_variant_ids = [];

    foreach ($submitted_variants as $v_idx => $variant_data) {
        if (empty($variant_data['size']) || empty($variant_data['price'])) {
            continue; // Skip incomplete variants
        }

        $size_name = $conn->real_escape_string($variant_data['size']);
        $color = isset($variant_data['color']) ? $conn->real_escape_string($variant_data['color']) : '';
        $color_code = isset($variant_data['color_code']) ? $conn->real_escape_string($variant_data['color_code']) : '#000000';
        $price = floatval($variant_data['price']);
        $strike_price = !empty($variant_data['strike_price']) ? floatval($variant_data['strike_price']) : NULL;
        $stock = !empty($variant_data['stock']) ? intval($variant_data['stock']) : 0;

        // Check if size exists for this product
        $size_check = $conn->query("SELECT id FROM product_sizes WHERE product_id=$product_id AND size='$size_name'");

        if ($size_check->num_rows > 0) {
            $size_id = $size_check->fetch_assoc()['id'];
        } else {
            $conn->query("INSERT INTO product_sizes (product_id, size) VALUES ('$product_id', '$size_name')");
            $size_id = $conn->insert_id;
        }
        $kept_size_ids[] = $size_id;

        // Save/Update Variant
        if (isset($variant_data['id']) && !empty($variant_data['id'])) {
            // UPDATE existing variant
            $v_id = intval($variant_data['id']);
            $strike_sql = $strike_price !== NULL ? "'$strike_price'" : "NULL";

            $sql = "UPDATE product_variants SET 
                    color='$color', 
                    color_code='$color_code',
                    price='$price', 
                    strike_price=$strike_sql, 
                    stock_quantity='$stock'
                    WHERE id=$v_id AND product_size_id=$size_id";
            $conn->query($sql);
            $kept_variant_ids[] = $v_id;
        } else {
            // INSERT new variant
            $strike_sql = $strike_price !== NULL ? "'$strike_price'" : "NULL";

            $sql = "INSERT INTO product_variants (product_size_id, color, color_code, price, strike_price, stock_quantity) 
                    VALUES ('$size_id', '$color', '$color_code', '$price', $strike_sql, '$stock')";
            $conn->query($sql);
            $kept_variant_ids[] = $conn->insert_id;
        }
    }

    // Delete removed variants
    if ($product_id && !empty($kept_variant_ids)) {
        $kept_v_ids_str = implode(",", $kept_variant_ids);
        $conn->query("DELETE v FROM product_variants v 
                      JOIN product_sizes s ON v.product_size_id = s.id 
                      WHERE s.product_id=$product_id AND v.id NOT IN ($kept_v_ids_str)");
    } elseif ($product_id) {
        // No variants kept, delete all
        $conn->query("DELETE v FROM product_variants v 
                      JOIN product_sizes s ON v.product_size_id = s.id 
                      WHERE s.product_id=$product_id");
    }

    // Delete orphaned sizes (sizes with no variants)
    if ($product_id) {
        $conn->query("DELETE FROM product_sizes 
                      WHERE product_id=$product_id 
                      AND id NOT IN (SELECT DISTINCT product_size_id FROM product_variants)");
    }

    // 3. SAVE SPECIFICATIONS
    $submitted_specs = isset($_POST['specs']) ? $_POST['specs'] : [];
    $kept_spec_ids = [];

    foreach ($submitted_specs as $s_idx => $spec_data) {
        if (empty($spec_data['key'])) {
            continue; // Skip empty specs
        }

        $s_key = $conn->real_escape_string($spec_data['key']);
        $s_val = isset($spec_data['value']) ? $conn->real_escape_string($spec_data['value']) : '';

        if (isset($spec_data['id']) && !empty($spec_data['id'])) {
            // UPDATE
            $s_id = intval($spec_data['id']);
            $conn->query("UPDATE product_specifications SET spec_key='$s_key', spec_value='$s_val' WHERE id=$s_id");
            $kept_spec_ids[] = $s_id;
        } else {
            // INSERT
            $conn->query("INSERT INTO product_specifications (product_id, spec_key, spec_value) VALUES ('$product_id', '$s_key', '$s_val')");
            $kept_spec_ids[] = $conn->insert_id;
        }
    }

    // Delete removed specs
    if ($product_id && !empty($kept_spec_ids)) {
        $kept_s_ids_str = implode(",", $kept_spec_ids);
        $conn->query("DELETE FROM product_specifications WHERE product_id=$product_id AND id NOT IN ($kept_s_ids_str)");
    } elseif ($product_id) {
        $conn->query("DELETE FROM product_specifications WHERE product_id=$product_id");
    }

    // 4. HANDLE IMAGE DELETIONS
    if (isset($_POST['deleted_images']) && !empty($_POST['deleted_images'])) {
        $deleted_ids = json_decode($_POST['deleted_images'], true);
        if (is_array($deleted_ids)) {
            foreach ($deleted_ids as $del_id) {
                $del_id = intval($del_id);
                // Fetch image path to delete file
                $img_res = $conn->query("SELECT image_path FROM product_images WHERE id=$del_id AND product_id=$product_id");
                if ($img_res->num_rows > 0) {
                    $img_path = $img_res->fetch_assoc()['image_path'];
                    if (file_exists($target_dir . $img_path)) {
                        unlink($target_dir . $img_path);
                    }
                    $conn->query("DELETE FROM product_images WHERE id=$del_id");
                }
            }
        }
    }

    // 5. UPDATE EXISTING IMAGES (COLOR ASSOCIATION)
    if (isset($_POST['existing_images']) && is_array($_POST['existing_images'])) {
        foreach ($_POST['existing_images'] as $ex_idx => $img_data) {
            $img_id = intval($img_data['id']);
            $img_color = isset($img_data['color']) ? $conn->real_escape_string($img_data['color']) : '';
            $is_primary = isset($img_data['is_primary']) ? 1 : 0;

            $conn->query("UPDATE product_images SET color='$img_color', is_primary=$is_primary WHERE id=$img_id AND product_id=$product_id");
        }
    }

    // 6. UPLOAD NEW IMAGES
    if (isset($_POST['new_images']) && is_array($_POST['new_images'])) {
        foreach ($_POST['new_images'] as $new_idx => $img_data) {
            // Check if file was uploaded for this index
            if (
                isset($_FILES['new_images']['name'][$new_idx]['file']) &&
                !empty($_FILES['new_images']['name'][$new_idx]['file'])
            ) {

                $file_name = $_FILES['new_images']['name'][$new_idx]['file'];
                $file_tmp = $_FILES['new_images']['tmp_name'][$new_idx]['file'];
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                // Validate extension
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($ext, $allowed)) {
                    continue;
                }

                $new_name = uniqid() . '.' . $ext;

                if (move_uploaded_file($file_tmp, $target_dir . $new_name)) {
                    $img_color = isset($img_data['color']) ? $conn->real_escape_string($img_data['color']) : '';
                    $is_primary = isset($img_data['is_primary']) ? 1 : 0;

                    $conn->query("INSERT INTO product_images (product_id, image_path, color, is_primary) 
                                  VALUES ('$product_id', '$new_name', '$img_color', $is_primary)");
                }
            }
        }
    }

    // 6.1 UPLOAD BATCH IMAGES (NEW LOGIC)
    if (isset($_FILES['bulk_uploads']) && isset($_POST['bulk_uploads'])) {
        $files = $_FILES['bulk_uploads'];
        $colors = $_POST['bulk_uploads'];
        // Get list of removed uploads: [batch_id => [index1, index2, ...]]
        $removed_uploads = isset($_POST['remove_upload']) ? $_POST['remove_upload'] : [];

        foreach ($files['name'] as $batch_idx => $file_array) {
            // $file_array is an array of filenames for this batch
            if (!isset($file_array['files']) || !is_array($file_array['files']))
                continue;

            $batch_color = isset($colors[$batch_idx]['color']) ? $conn->real_escape_string($colors[$batch_idx]['color']) : '';

            foreach ($file_array['files'] as $f_idx => $f_name) {
                if (empty($f_name))
                    continue;

                // CHECK IF REMOVED in UI
                if (isset($removed_uploads[$batch_idx]) && in_array($f_idx, $removed_uploads[$batch_idx])) {
                    continue; // Skip this file as user removed it
                }

                $f_tmp = $files['tmp_name'][$batch_idx]['files'][$f_idx];
                $ext = strtolower(pathinfo($f_name, PATHINFO_EXTENSION));

                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($ext, $allowed))
                    continue;

                $new_name = uniqid() . '_b.' . $ext; // _b for batch

                if (move_uploaded_file($f_tmp, $target_dir . $new_name)) {
                    // Check is_primary from POST data for this specific file
                    $is_primary = (isset($colors[$batch_idx]['is_primary'][$f_idx]) && $colors[$batch_idx]['is_primary'][$f_idx] == 1) ? 1 : 0;

                    $conn->query("INSERT INTO product_images (product_id, image_path, color, is_primary) 
                                  VALUES ('$product_id', '$new_name', '$batch_color', $is_primary)");
                }
            }
        }
    }

    // 7. ENSURE AT LEAST ONE PRIMARY IMAGE
    $primary_check = $conn->query("SELECT COUNT(*) as cnt FROM product_images WHERE product_id=$product_id AND is_primary=1");
    $has_primary = $primary_check->fetch_assoc()['cnt'] > 0;

    if (!$has_primary) {
        // Set first image as primary
        $conn->query("UPDATE product_images SET is_primary=1 WHERE product_id=$product_id ORDER BY id LIMIT 1");
    }

    // Success - redirect
    header("Location: products.php?msg=saved");
    exit();
}
?>