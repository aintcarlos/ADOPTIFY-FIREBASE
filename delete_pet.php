<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Accept both POST (CSRF-safe form) and GET (legacy)
$id    = intval($_POST['id'] ?? $_GET['id'] ?? 0);
$owner = $_SESSION['user'];

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM pets WHERE id = ? AND owner = ?");
    $stmt->bind_param("is", $id, $owner);
    $stmt->execute();
}

header("Location: my-pets.php?success=" . urlencode("Pet deleted successfully"));
exit();
?>
