<?php
session_start();
if (!isset($_SESSION['register_number'])) {
    die("Unauthorized access");
}

$servername = "";
$username = "";
$password = "";
$dbname = "";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $register_number = $_SESSION['register_number'];

    $sql = "DELETE FROM section_survey WHERE register_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $register_number);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}

$conn->close();
