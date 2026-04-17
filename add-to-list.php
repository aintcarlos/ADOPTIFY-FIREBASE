<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$pet_id  = intval($_GET['id'] ?? 0);

if ($pet_id <= 0) {
    header("Location: adopt-now.php");
    exit();
}

// Prevent duplicate
$stmt = $conn->prepare("SELECT id FROM adoption_list WHERE user_id = ? AND pet_id = ?");
$stmt->bind_param("ii", $user_id, $pet_id);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO adoption_list (user_id, pet_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $pet_id);
    $stmt->execute();
}

header("Location: adopt-now.php?success=" . urlencode("Pet added to your adoption list!"));
exit();
?>
