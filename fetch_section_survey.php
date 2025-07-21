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

$class = $_GET['class'] ?? '';
$section = $_GET['section'] ?? '';

if (empty($class) || empty($section)) {
    die("Invalid request");
}

// Get members with their profile photos
$sql = "SELECT s.name, s.register_number, s.profile_photo 
        FROM section_survey ss
        JOIN students s ON ss.register_number = s.register_number
        WHERE ss.class = ? AND ss.section = ?
        ORDER BY s.name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $class, $section);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $profile_photo = !empty($row['profile_photo']) ?
            'https://oursrmap.purlyedit.in/' . ltrim($row['profile_photo'], '/') :
            '';

        $initials = implode('', array_map(function ($n) {
            return strtoupper(substr($n, 0, 1));
        }, explode(' ', $row['name'])));

        echo '<div class="member-item">';
        echo '<div class="member-info">';
        echo '<div class="member-avatar" style="background-image: url(\'' . $profile_photo . '\')">';
        if (empty($profile_photo)) {
            echo $initials;
        }
        echo '</div>';
        echo '<div class="member-details">';
        echo '<div class="member-name">' . htmlspecialchars($row['name']) . '</div>';
        echo '<div class="member-regno">' . htmlspecialchars($row['register_number']) . '</div>';
        echo '</div>';
        echo '</div>';
        echo '<a href="https://oursrmap.purlyedit.in/view_profile?register_number=' .
            urlencode($row['register_number']) . '" class="member-profile" target="_blank">';
        echo '<i class="fas fa-external-link-alt"></i> View';
        echo '</a>';
        echo '</div>';
    }
} else {
    echo '<div class="no-members">';
    echo '<i class="fas fa-user-slash"></i>';
    echo '<p>No members have joined this section yet</p>';
    echo '</div>';
}

$conn->close();
