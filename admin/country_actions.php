<?php
require_once 'check_session.php';
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    if ($action == "add") {
        $name = $_POST['name'];
        $code = strtoupper($_POST['code']);
        $pricing_type_id = intval($_POST['pricing_type_id']); // New input
        $status = $_POST['status'];

        // Auto-generate legacy columns
        $multiplier = 1.00;
        // Ensure pricing_model string is robustly generated
        $pricing_model = 'formula_' . $pricing_type_id;

        $sql = "INSERT INTO countries (name, code, multiplier, pricing_model, pricing_type_id, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // "ssdsis" -> string, string, double, string, int, string
        $stmt->bind_param("ssdsis", $name, $code, $multiplier, $pricing_model, $pricing_type_id, $status);

        if ($stmt->execute()) {
            header("Location: countries.php?msg=added");
        } else {
            echo "Error: " . $stmt->error;
        }

    } elseif ($action == "edit") {
        $id = intval($_POST['id']);
        $name = $_POST['name'];
        $code = strtoupper($_POST['code']);
        $pricing_type_id = intval($_POST['pricing_type_id']);
        $status = $_POST['status'];

        $multiplier = 1.00;
        $pricing_model = 'formula_' . $pricing_type_id;

        $sql = "UPDATE countries SET name=?, code=?, multiplier=?, pricing_model=?, pricing_type_id=?, status=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsisi", $name, $code, $multiplier, $pricing_model, $pricing_type_id, $status, $id);

        if ($stmt->execute()) {
            header("Location: countries.php?msg=updated");
        } else {
            echo "Error: " . $stmt->error;
        }
    }

} elseif (isset($_GET['action']) && $_GET['action'] == "delete") {
    $id = intval($_GET['id']);

    $sql = "DELETE FROM countries WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: countries.php?msg=deleted");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>