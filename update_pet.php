<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: my-pets.php");
    exit();
}

$id     = intval($_POST['id']     ?? 0);
$name   = trim($_POST['name']     ?? '');
$type   = trim($_POST['type']     ?? '');
$breed  = trim($_POST['breed']    ?? '');
$age    = intval($_POST['age']    ?? 0);
$status = trim($_POST['status']   ?? '');
$owner  = $_SESSION['user'];

$allowed_statuses = ['Available', 'Booked'];
if (!in_array($status, $allowed_statuses)) {
    header("Location: my-pets.php?error=" . urlencode("Invalid status"));
    exit();
}

if ($id <= 0 || !$name || !$type || !$breed || $age <= 0) {
    header("Location: my-pets.php?error=" . urlencode("Invalid input"));
    exit();
}

// Only update pets this user owns
$stmt = $conn->prepare(
    "UPDATE pets SET name=?, type=?, breed=?, age=?, status=? WHERE id=? AND owner=?"
);
$stmt->bind_param("sssisis", $name, $type, $breed, $age, $status, $id, $owner);
$stmt->execute();

header("Location: my-pets.php?success=" . urlencode("Pet updated successfully"));
exit();
?>
