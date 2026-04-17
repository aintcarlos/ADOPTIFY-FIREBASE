<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$meetup_id = intval($_GET['id']     ?? 0);
$action    = trim($_GET['action']   ?? ''); // 'cancel' or 'success'
$user      = $_SESSION['user'];

if (!$meetup_id || !in_array($action, ['cancel', 'success'])) {
    header("Location: my-pets.php");
    exit();
}

// Get meetup and verify this user is the pet owner
$stmt = $conn->prepare("SELECT meetups.*, pets.name as pet_name FROM meetups JOIN pets ON pets.id = meetups.pet_id WHERE meetups.id = ? AND meetups.owner = ?");
$stmt->bind_param("is", $meetup_id, $user);
$stmt->execute();
$meetup = $stmt->get_result()->fetch_assoc();

if (!$meetup) {
    header("Location: my-pets.php?error=" . urlencode("Meetup not found"));
    exit();
}

$pet_id = $meetup['pet_id'];

if ($action === 'cancel') {
    // Meetup failed — release pet back to Available
    $stmt2 = $conn->prepare("UPDATE meetups SET status = 'Cancelled' WHERE id = ?");
    $stmt2->bind_param("i", $meetup_id);
    $stmt2->execute();

    $stmt3 = $conn->prepare("UPDATE pets SET status = 'Available' WHERE id = ?");
    $stmt3->bind_param("i", $pet_id);
    $stmt3->execute();

    header("Location: my-meetups.php?success=" . urlencode("Meetup cancelled. {$meetup['pet_name']} is now available again."));

} elseif ($action === 'success') {
    // Meetup successful — mark pet as Adopted
    $stmt2 = $conn->prepare("UPDATE meetups SET status = 'Completed' WHERE id = ?");
    $stmt2->bind_param("i", $meetup_id);
    $stmt2->execute();

    $stmt3 = $conn->prepare("UPDATE pets SET status = 'Adopted' WHERE id = ?");
    $stmt3->bind_param("i", $pet_id);
    $stmt3->execute();

    header("Location: my-meetups.php?success=" . urlencode("Great! {$meetup['pet_name']} is now marked as Adopted."));
}
exit();
?>
