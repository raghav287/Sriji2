<?php
$host = "localhost";
$user = "u586615155_sriji_admin";
$pass = "5bv3u:uB&#Z";
$dbname = "u586615155_sriji";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>