<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    echo json_encode([]);
    exit();
}

$pet_id = intval($_GET['pet_id'] ?? 0);

if (!$pet_id) {
    echo json_encode([]);
    exit();
}

$stmt = $conn->prepare("SELECT meetup_date FROM meetups WHERE pet_id = ? AND status != 'Cancelled' AND meetup_date >= CURDATE()");
$stmt->bind_param("i", $pet_id);
$stmt->execute();
$result = $stmt->get_result();

$dates = [];
while ($row = $result->fetch_assoc()) {
    $dates[] = $row['meetup_date'];
}

header('Content-Type: application/json');
echo json_encode($dates);
?>
