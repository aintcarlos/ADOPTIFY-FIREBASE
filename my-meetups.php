<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Meetups | Adoptify</title>
  <link rel="stylesheet" href="css/my-pets.css">
  <style>
    .status-badge {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 700;
      display: inline-block;
    }
    .badge-pending   { background: #fff3cd; color: #856404; }
    .badge-completed { background: #d4edda; color: #155724; }
    .badge-cancelled { background: #f8d7da; color: #721c24; }

    .action-link {
      padding: 6px 12px;
      border-radius: 16px;
      font-size: 12px;
      font-weight: 600;
      text-decoration: none;
      border: none;
      cursor: pointer;
      display: inline-block;
      margin: 2px;
    }
    .btn-success-meetup { background: #0b652a; color: white; }
    .btn-success-meetup:hover { background: #084d20; }
    .btn-fail-meetup    { background: white; color: #a10000; border: 2px solid #a10000; }
    .btn-fail-meetup:hover { background: #a10000; color: white; }

    .empty-msg { text-align: center; padding: 40px; color: #777; font-size: 15px; }
  </style>
</head>
<body>

<?php if (isset($_GET['success'])): ?>
<div class="toast success" id="toast">✅ <?php echo htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8'); ?></div>
<?php elseif (isset($_GET['error'])): ?>
<div class="toast error" id="toast">❌ <?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<header class="main-nav">
  <section class="branding">
    <img src="images/logo.png" alt="Adoptify">
    <h1>ADOPTIFY</h1>
  </section>
  <nav class="topnav">
    <a href="adopt-now.php" class="btn-outline">HOME</a>
    <a href="my-pets.php"   class="btn-outline">MY PETS</a>
    <a href="logout.php"    class="btn-outline">LOGOUT</a>
  </nav>
</header>

<h1 class="page-title">Meetup Bookings for My Pets</h1>

<div class="table-wrapper">
  <table>
    <thead>
      <tr>
        <th>Pet</th>
        <th>Adopter Name</th>
        <th>Adopter Email</th>
        <th>Date</th>
        <th>Message</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
<?php
$stmt = $conn->prepare("
    SELECT meetups.*, pets.name as pet_name, pets.image as pet_image
    FROM meetups
    JOIN pets ON pets.id = meetups.pet_id
    WHERE meetups.owner = ?
    ORDER BY meetups.meetup_date DESC
");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
        $badgeClass = match($row['status']) {
            'Completed' => 'badge-completed',
            'Cancelled' => 'badge-cancelled',
            default     => 'badge-pending'
        };
        $formattedDate = date('M j, Y', strtotime($row['meetup_date']));
?>
      <tr>
        <td><strong><?php echo htmlspecialchars($row['pet_name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
        <td><?php echo htmlspecialchars($row['adopter_name'],  ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars($row['adopter_email'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo $formattedDate; ?></td>
        <td><?php echo htmlspecialchars($row['message'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></td>
        <td><span class="status-badge <?php echo $badgeClass; ?>"><?php echo $row['status']; ?></span></td>
        <td>
          <?php if ($row['status'] === 'Pending'): ?>
            <a href="cancel_meetup.php?id=<?php echo $row['id']; ?>&action=success"
               class="action-link btn-success-meetup"
               onclick="return confirm('Mark this meetup as successful? The pet will be marked as Adopted.')">
               ✅ Meetup Success
            </a>
            <a href="cancel_meetup.php?id=<?php echo $row['id']; ?>&action=cancel"
               class="action-link btn-fail-meetup"
               onclick="return confirm('Mark meetup as failed? The pet will be available for booking again.')">
               ❌ Meetup Failed
            </a>
          <?php else: ?>
            <span style="color:#aaa; font-size:13px;">No actions</span>
          <?php endif; ?>
        </td>
      </tr>
<?php
    endwhile;
else:
?>
      <tr><td colspan="7" class="empty-msg">No meetup bookings yet.</td></tr>
<?php endif; ?>
    </tbody>
  </table>
</div>

<footer class="page-footer"><small>© 2026 Adoptify</small></footer>

<script>
const toast = document.getElementById('toast');
if (toast) setTimeout(() => toast.classList.add('hide'), 4000);
</script>

</body>
</html>
