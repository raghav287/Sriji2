<?php
require_once 'check_session.php';
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $keys = [
        'shipping_cap',
        'shipping_charge_below',
        'shipping_charge_above',
        'cod_active',
        'cod_limit',
        'cod_charge_below',
        'cod_charge_above'
    ];

    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            $val = $_POST[$key];
            // Update or Insert
            // Simple approach: try update, if nothing affected (which happens if value same, or not exists), try insert logic.
            // But better is INSERT ON DUPLICATE KEY UPDATE logic or just simple update loop because we initiated DB.

            $check = $conn->query("SELECT id FROM site_settings WHERE setting_key = '$key'");
            if ($check->num_rows > 0) {
                $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->bind_param("ss", $val, $key);
                $stmt->execute();
            } else {
                $stmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
                $stmt->bind_param("ss", $key, $val);
                $stmt->execute();
            }
        }
    }

    header("Location: shipping_settings.php?msg=updated");
}
?>