<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email']    ?? '');
$password = $_POST['password']      ?? '';
$confirm  = $_POST['confirm']       ?? '';
$contact  = trim($_POST['contact']  ?? '');

// ── Basic presence check ──
if (!$username || !$email || !$password || !$confirm || !$contact) {
    header("Location: register.php?error=" . urlencode("All fields are required"));
    exit();
}

// ── Password match ──
if ($password !== $confirm) {
    header("Location: register.php?error=" . urlencode("Passwords do not match"));
    exit();
}

// ── Contact format ──
if (!preg_match('/^[0-9]{11}$/', $contact)) {
    header("Location: register.php?error=" . urlencode("Contact must be 11 digits"));
    exit();
}

// ── Password strength: min 8 chars, 1 uppercase, 1 digit, 1 special ──
if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/', $password)) {
    header("Location: register.php?error=" . urlencode("Password must be at least 8 characters with 1 uppercase, 1 number, and 1 special character (!@#\$%^&*)"));
    exit();
}

// ── Duplicate email ──
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    header("Location: register.php?error=" . urlencode("Email is already registered"));
    exit();
}

// ── Duplicate username ──
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    header("Location: register.php?error=" . urlencode("Username is already taken"));
    exit();
}

// ── Insert ──
$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt   = $conn->prepare("INSERT INTO users (username, email, password, contact) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $email, $hashed, $contact);

if ($stmt->execute()) {
    header("Location: login.php?success=" . urlencode("Account created! You can now log in."));
} else {
    header("Location: register.php?error=" . urlencode("Something went wrong. Please try again."));
}
exit();
?>
