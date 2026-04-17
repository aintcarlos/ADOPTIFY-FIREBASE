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

$stmt = $conn->prepare("UPDATE pets SET status='Pending Adoption' WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: adopt-now.php?success=" . urlencode("Pet marked as Pending Adoption!"));
exit();
?>
