<?php
// PayPal Configuration
// Get these from paypal developer dashboard
define('PAYPAL_CLIENT_ID', 'AcmHYEVLK6XLMILbMj0AkGYUWHV7EdmXNtCRIfP9UfnWd47n3q7HNJTNLcF0Gs9LJxfEyF1QBWIyrg5C'); // Replace with actual Client ID
define('PAYPAL_SECRET', 'EKJ_7zpNyF0IeixOrf9zG088j2zkyUgwzsgaWABvYvW1-7sajGD95AcR7XCCWzrRycmADMmGi6GjzfGA'); // Replace with actual Secret (if needed for server-side)

// Currency Conversion Settings
// function to get dynamic rate
function getUsdRate($conn)
{
    if (!$conn)
        return 85;

    $current_time = time();
    $rate = 85; // Default

    // Check DB for cached rate
    $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'usd_rate'");
    $stmt->execute();
    $res = $stmt->get_result();
    $val_row = $res->fetch_assoc();

    $stmt2 = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'usd_rate_time'");
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    $time_row = $res2->fetch_assoc();

    $cached_rate = $val_row ? floatval($val_row['setting_value']) : null;
    $last_check = $time_row ? intval($time_row['setting_value']) : 0;

    // Use cache if less than 24 hours (86400 seconds)
    if ($cached_rate && ($current_time - $last_check) < 86400) {
        return $cached_rate;
    }

    // Fetch from API
    $api_url = "https://open.er-api.com/v6/latest/USD";
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $json = @file_get_contents($api_url, false, $ctx);

    if ($json) {
        $data = json_decode($json, true);
        if (isset($data['rates']['INR'])) {
            $new_rate = $data['rates']['INR'];

            // Update DB
            // We use INSERT ... ON DUPLICATE KEY UPDATE logic logic mimicking or just simple specific queries depends on if keys exist.
            // Simplified: DELETE then INSERT to avoid complexity if keys missing
            $conn->query("DELETE FROM site_settings WHERE setting_key IN ('usd_rate', 'usd_rate_time')");

            $stmt_ins = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?), (?, ?)");
            $t = (string) $current_time;
            $r_str = (string) $new_rate;
            $k1 = 'usd_rate';
            $k2 = 'usd_rate_time';
            $stmt_ins->bind_param("ssss", $k1, $r_str, $k2, $t);
            $stmt_ins->execute();

            return $new_rate;
        }
    }

    return $cached_rate ?: 85;
}
?>