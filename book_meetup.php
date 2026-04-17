<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: adoption-list.php");
    exit();
}

$pet_id       = intval($_POST['pet_id']       ?? 0);
$adopter_name = trim($_POST['adopter_name']   ?? '');
$adopter_email= trim($_POST['adopter_email']  ?? '');
$meetup_date  = trim($_POST['meetup_date']    ?? '');
$message      = trim($_POST['message']        ?? '');
$adopter_id   = $_SESSION['user_id'];
$adopter_user = $_SESSION['user'];

if (!$pet_id || !$adopter_name || !$adopter_email || !$meetup_date) {
    header("Location: adoption-list.php?error=" . urlencode("All fields are required"));
    exit();
}

// Validate date is not in the past
if (strtotime($meetup_date) < strtotime('today')) {
    header("Location: adoption-list.php?error=" . urlencode("Please pick a future date"));
    exit();
}

// Check if date is already booked for this pet
$check = $conn->prepare("SELECT id FROM meetups WHERE pet_id = ? AND meetup_date = ? AND status != 'Cancelled'");
$check->bind_param("is", $pet_id, $meetup_date);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    header("Location: adoption-list.php?error=" . urlencode("That date is already booked for this pet. Please choose another date."));
    exit();
}

// Get pet info and owner
$pet_stmt = $conn->prepare("SELECT name, owner FROM pets WHERE id = ?");
$pet_stmt->bind_param("i", $pet_id);
$pet_stmt->execute();
$pet = $pet_stmt->get_result()->fetch_assoc();

if (!$pet) {
    header("Location: adoption-list.php?error=" . urlencode("Pet not found"));
    exit();
}

$pet_name  = $pet['name'];
$pet_owner = $pet['owner'];

// Get owner email
$owner_stmt = $conn->prepare("SELECT email FROM users WHERE username = ?");
$owner_stmt->bind_param("s", $pet_owner);
$owner_stmt->execute();
$owner_data  = $owner_stmt->get_result()->fetch_assoc();
$owner_email = $owner_data['email'] ?? '';

// Check if pet is already booked by someone else
$pet_status_check = $conn->prepare("SELECT status FROM pets WHERE id = ?");
$pet_status_check->bind_param("i", $pet_id);
$pet_status_check->execute();
$pet_status_row = $pet_status_check->get_result()->fetch_assoc();

if ($pet_status_row['status'] === 'Booked') {
    header("Location: adoption-list.php?error=" . urlencode("Sorry, $pet_name has already been booked by someone else."));
    exit();
}

// Insert meetup
$stmt = $conn->prepare("INSERT INTO meetups (pet_id, adopter_id, owner, adopter_name, adopter_email, meetup_date, message) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisssss", $pet_id, $adopter_id, $pet_owner, $adopter_name, $adopter_email, $meetup_date, $message);

if (!$stmt->execute()) {
    header("Location: adoption-list.php?error=" . urlencode("Failed to book meetup"));
    exit();
}

// Mark pet as Booked so no one else can book it
$mark = $conn->prepare("UPDATE pets SET status = 'Booked' WHERE id = ?");
$mark->bind_param("i", $pet_id);
$mark->execute();

// ── Send emails ──
$formatted_date = date('F j, Y', strtotime($meetup_date));
$headers = "From: noreply@adoptify.com\r\nContent-Type: text/html; charset=UTF-8";

// Email to adopter
$adopter_subject = "Meetup Booked! - $pet_name | Adoptify";
$adopter_body = "
<html><body style='font-family:sans-serif; color:#111;'>
<div style='max-width:500px; margin:auto; padding:30px; border-radius:12px; border:1px solid #ddd;'>
  <h2 style='color:#0b652a;'>🐾 Meetup Booked!</h2>
  <p>Hi <b>$adopter_name</b>,</p>
  <p>Your meetup with <b>$pet_name</b> has been booked successfully!</p>
  <table style='width:100%; margin:20px 0; border-collapse:collapse;'>
    <tr><td style='padding:8px; color:#555;'>Pet</td><td><b>$pet_name</b></td></tr>
    <tr><td style='padding:8px; color:#555;'>Date</td><td><b>$formatted_date</b></td></tr>
    <tr><td style='padding:8px; color:#555;'>Message</td><td>" . ($message ?: 'None') . "</td></tr>
  </table>
  <p>The pet owner has been notified and will contact you soon.</p>
  <p style='color:#0b652a; font-weight:bold;'>Thank you for choosing to adopt! 🐾</p>
  <p style='font-size:12px; color:#999;'>— Adoptify Team</p>
</div>
</body></html>";

mail($adopter_email, $adopter_subject, $adopter_body, $headers);

// Email to pet owner
if ($owner_email) {
    $owner_subject = "Your pet $pet_name has a meetup booking! | Adoptify";
    $owner_body = "
<html><body style='font-family:sans-serif; color:#111;'>
<div style='max-width:500px; margin:auto; padding:30px; border-radius:12px; border:1px solid #ddd;'>
  <h2 style='color:#0b652a;'>🐾 New Meetup Request!</h2>
  <p>Hi <b>$pet_owner</b>,</p>
  <p>Someone wants to meet your pet <b>$pet_name</b>!</p>
  <table style='width:100%; margin:20px 0; border-collapse:collapse;'>
    <tr><td style='padding:8px; color:#555;'>Adopter</td><td><b>$adopter_name</b></td></tr>
    <tr><td style='padding:8px; color:#555;'>Email</td><td><b>$adopter_email</b></td></tr>
    <tr><td style='padding:8px; color:#555;'>Date</td><td><b>$formatted_date</b></td></tr>
    <tr><td style='padding:8px; color:#555;'>Message</td><td>" . ($message ?: 'None') . "</td></tr>
  </table>
  <p>Please contact the adopter to confirm the meetup details.</p>
  <p style='font-size:12px; color:#999;'>— Adoptify Team</p>
</div>
</body></html>";

    mail($owner_email, $owner_subject, $owner_body, $headers);
}

header("Location: adoption-list.php?success=" . urlencode("Meetup booked for $formatted_date! Check your email for confirmation."));
exit();
?>
