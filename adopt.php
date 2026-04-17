<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: adopt-now.php");
    exit();
}

$stmt = $conn->prepare("UPDATE pets SET status='Adopted' WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: adopt-now.php?success=" . urlencode("Congratulations on your adoption!"));
} else {
    header("Location: adopt-now.php?error=" . urlencode("Error processing adoption"));
}
exit();
?>
