<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add-pet.php");
    exit();
}

$name  = trim($_POST['name']  ?? '');
$type  = trim($_POST['type']  ?? '');
$age   = intval($_POST['age'] ?? 0);
$breed = trim($_POST['breed'] ?? '');
$bio   = trim($_POST['bio']   ?? '');
$owner = $_SESSION['user'];

if (!$name || !$type || !$breed || !$bio || $age <= 0) {
    header("Location: add-pet.php?error=" . urlencode("All fields are required"));
    exit();
}

// ── Validate type ──
$allowed_types = ['Dog', 'Cat'];
if (!in_array($type, $allowed_types)) {
    header("Location: add-pet.php?error=" . urlencode("Invalid pet type"));
    exit();
}

// ── File upload validation ──
if (empty($_FILES['image']['name'])) {
    header("Location: add-pet.php?error=" . urlencode("Please upload an image"));
    exit();
}

$allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo        = finfo_open(FILEINFO_MIME_TYPE);
$mime         = finfo_file($finfo, $_FILES['image']['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowed_mime)) {
    header("Location: add-pet.php?error=" . urlencode("Only JPG, PNG, GIF, and WEBP images are allowed"));
    exit();
}

$ext     = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$newName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
$folder  = "images/";

if (!move_uploaded_file($_FILES['image']['tmp_name'], $folder . $newName)) {
    header("Location: add-pet.php?error=" . urlencode("Failed to upload image"));
    exit();
}

$stmt = $conn->prepare(
    "INSERT INTO pets (name, type, age, breed, bio, image, status, owner)
     VALUES (?, ?, ?, ?, ?, ?, 'Available', ?)"
);
$stmt->bind_param("ssissss", $name, $type, $age, $breed, $bio, $newName, $owner);

if ($stmt->execute()) {
    header("Location: adopt-now.php");
} else {
    header("Location: add-pet.php?error=" . urlencode("Failed to add pet"));
}
exit();
?>
