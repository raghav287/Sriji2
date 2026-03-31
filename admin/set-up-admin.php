<?php

require_once('db.php');

/**
 * STEP 1: Check if `admins` table already exists
 */
$tableCheckSql = "
    SELECT COUNT(*) AS table_exists
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
    AND table_name = 'admins'
";

$result = $conn->query($tableCheckSql);
$row = $result->fetch_assoc();

if ($row['table_exists'] == 0) {

    // STEP 2: Create admins table (only if it does NOT exist)
    $createTableSql = "
        CREATE TABLE admins (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";

    if ($conn->query($createTableSql) === TRUE) {
        echo "Admins table created successfully.<br>";
    } else {
        die("Error creating admins table: " . $conn->error);
    }

} else {
    echo "Admins table already exists. Skipping table creation.<br>";
}

/**
 * STEP 3: Check if default admin already exists
 */
$email = "admin@sriji.com";

$stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {

    // STEP 4: Insert default admin
    $password = password_hash("password123", PASSWORD_DEFAULT);

    $insertStmt = $conn->prepare(
        "INSERT INTO admins (email, password) VALUES (?, ?)"
    );
    $insertStmt->bind_param("ss", $email, $password);

    if ($insertStmt->execute()) {
        echo "Default admin created successfully.<br>";
    } else {
        echo "Error creating admin: " . $insertStmt->error;
    }

    $insertStmt->close();

} else {
    echo "Admin user already exists. Skipping admin creation.<br>";
}

$stmt->close();
$conn->close();

?>