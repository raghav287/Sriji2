<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function get_currency_multiplier()
{
    if (isset($_SESSION['selected_country_multiplier'])) {
        return (float) $_SESSION['selected_country_multiplier'];
    }
    return 1.00; // Default
}

function get_selected_country_code()
{
    return $_SESSION['selected_country_code'] ?? 'IN'; // Default India? Or generic.
}

function get_pricing_model()
{
    return $_SESSION['selected_country_pricing_model'] ?? 'multiplier';
}

function get_dynamic_rules($formula_id)
{
    // Check if rules are cached in session to avoid DB hit on every calculation
    if (isset($_SESSION['pricing_rules_' . $formula_id])) {
        return $_SESSION['pricing_rules_' . $formula_id];
    }

    // Lazy load: We need DB connection. 
    // Assuming db.php is included or global $conn exists? 
    // price_helper.php is usually included where db is present, or we need to require it.
    // Ideally, cache these rules when country is SELECTED (in POST handler).
    return [];
}

function calculate_price($base_price, $size_name = null)
{
    if (!$size_name)
        return $base_price * get_currency_multiplier();

    $multiplier = get_currency_multiplier();
    $model = get_pricing_model(); // e.g., 'formula_1'

    if (strpos($model, 'formula_') === 0) {
        $fid = substr($model, 8);

        // Retrieve rules from SESSION (populated during country select)
        $rules = $_SESSION['current_formula_rules'] ?? [];

        $size_clean = trim($size_name);

        // Find rule for this size
        // Rules structure: [ size_label => [factor, constant] ]
        if (isset($rules[$size_clean])) {
            $factor = $rules[$size_clean]['factor'];
            $constant = $rules[$size_clean]['constant'];
            return ($base_price * $factor) + $constant;
        }

        // Fallback to standard multiplier
        return $base_price * $multiplier;
    }

    return $base_price * $multiplier;
}

function format_price($price, $size_name = null)
{
    $final_price = calculate_price($price, $size_name);
    return "₹" . number_format($final_price, 2);
}


function refresh_session_rules($conn)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // specific check: if no country selected, nothing to refresh
    if (!isset($_SESSION['selected_country_id'])) {
        return [];
    }

    $cid = (int) $_SESSION['selected_country_id'];
    // Re-fetch country to get latest Zone assignment
    $sql = "SELECT pricing_model, pricing_type_id FROM countries WHERE id = $cid";
    $res = $conn->query($sql);

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();

        // Update session with potentially new model/zone
        $pm = $row['pricing_model'];
        if ((empty($pm) || trim($pm) === '') && !empty($row['pricing_type_id'])) {
            $pm = 'formula_' . $row['pricing_type_id'];
        }
        $_SESSION['selected_country_pricing_model'] = $pm;

        if (strpos($pm, 'formula_') === 0) {
            $fid = (int) substr($pm, 8); // Use $pm (the fixed var) not $row['pricing_model']

            // Re-fetch Rules
            $rset = $conn->query("SELECT * FROM pricing_rules WHERE pricing_type_id = $fid");
            $rules = [];
            while ($r = $rset->fetch_assoc()) {
                $rules[$r['size_label']] = [
                    'factor' => $r['multiplier_factor'],
                    'constant' => $r['constant_amount']
                ];
            }
            $_SESSION['current_formula_rules'] = $rules;

            // Re-fetch Default Multiplier
            $zset = $conn->query("SELECT default_multiplier FROM pricing_types WHERE id=$fid");
            if ($zset && $zset->num_rows > 0) {
                $_SESSION['selected_country_multiplier'] = (float) $zset->fetch_assoc()['default_multiplier'];
            } else {
                $_SESSION['selected_country_multiplier'] = 0.0;
            }

            return $rules;
        }
    }
    return [];
}

// Handle Country Switch via AJAX or POST
if (isset($_POST['action']) && $_POST['action'] == 'change_country') {
    require_once '../admin/db.php'; // Adjust path if ensuring this file is called from different places

    $country_id = intval($_POST['country_id']);
    $sql = "SELECT * FROM countries WHERE id = $country_id AND status='active'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['selected_country_id'] = $row['id'];
        $_SESSION['selected_country_name'] = $row['name'];
        $_SESSION['selected_country_code'] = $row['code'];
        $_SESSION['selected_country_code'] = $row['code'];

        // Fix for missing pricing_model string (e.g. Australia case)
        $pm = $row['pricing_model'];
        if ((empty($pm) || trim($pm) === '') && !empty($row['pricing_type_id'])) {
            $pm = 'formula_' . $row['pricing_type_id'];
        }
        $_SESSION['selected_country_pricing_model'] = $pm;

        // Cache Rules & Default Multiplier
        unset($_SESSION['current_formula_rules']);
        // Default to dummy value; relying on Zone to validly provide multiplier.
        // User requested removal of implicit 1.0 fallback.
        $zone_default_mult = 0.0;

        if (strpos($pm, 'formula_') === 0) {
            $fid = intval(substr($pm, 8));

            // Fetch Rules
            $rules_sql = "SELECT * FROM pricing_rules WHERE pricing_type_id = $fid";
            $r_res = $conn->query($rules_sql);
            $rules_cache = [];
            while ($rule = $r_res->fetch_assoc()) {
                $rules_cache[$rule['size_label']] = [
                    'factor' => $rule['multiplier_factor'],
                    'constant' => $rule['constant_amount']
                ];
            }
            $_SESSION['current_formula_rules'] = $rules_cache;

            // Fetch Zone Default Multiplier
            // Note: DB fetch inside loop is okay for single country select event
            $z_res = $conn->query("SELECT default_multiplier FROM pricing_types WHERE id=$fid");
            if ($z_res->num_rows > 0) {
                $zone_default_mult = floatval($z_res->fetch_assoc()['default_multiplier']);
            }
        }

        // Store Zone Multiplier as the "selected_country_multiplier" for backward compatibility
        $_SESSION['selected_country_multiplier'] = $zone_default_mult;

    } else {
        // Reset to default
        unset($_SESSION['selected_country_id']);
        unset($_SESSION['selected_country_multiplier']);
        unset($_SESSION['selected_country_pricing_model']);
        unset($_SESSION['current_formula_rules']);
    }

    // Redirect back
    if (isset($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        header("Location: ../index.php");
    }
    exit;
}
?>