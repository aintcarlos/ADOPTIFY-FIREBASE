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
  <title>My Pets | Adoptify</title>
  <link rel="stylesheet" href="css/my-pets.css">
</head>
<body>

<!-- TOAST -->
<?php if (isset($_GET['success'])): ?>
<div class="toast success" id="toast">✅ <?php echo htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8'); ?></div>
<?php elseif (isset($_GET['error'])): ?>
<div class="toast error" id="toast">❌ <?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<header class="main-nav">
  <section class="branding">
    <img src="images/logo.png" alt="Adoptify Logo">
    <h1>ADOPTIFY</h1>
  </section>
  <nav class="topnav">
    <a href="adopt-now.php" class="btn-outline">HOME</a>
    <a href="adopt-now.php" class="btn-outline">← BACK</a>
  </nav>
</header>

<h1 class="page-title">My Posted Pets</h1>

<div class="table-wrapper">
  <table>
    <thead>
      <tr>
        <th>Photo</th><th>Name</th><th>Species</th><th>Breed</th><th>Age</th><th>Status</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
<?php
$stmt = $conn->prepare("SELECT * FROM pets WHERE owner = ? AND status != 'Adopted'");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
        $safeId     = intval($row['id']);
        $safeName   = htmlspecialchars($row['name'],   ENT_QUOTES, 'UTF-8');
        $safeType   = htmlspecialchars($row['type'],   ENT_QUOTES, 'UTF-8');
        $safeBreed  = htmlspecialchars($row['breed'],  ENT_QUOTES, 'UTF-8');
        $safeAge    = intval($row['age']);
        $safeStatus = htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8');
        $safeImage  = htmlspecialchars($row['image'],  ENT_QUOTES, 'UTF-8');
?>
      <tr>
        <td><img src="<?php echo (strpos($safeImage, 'http') === 0) ? $safeImage : 'images/' . $safeImage; ?>" alt="<?php echo $safeName; ?>" width="70"></td>
        <td><?php echo $safeName; ?></td>
        <td><?php echo $safeType; ?></td>
        <td><?php echo $safeBreed; ?></td>
        <td><?php echo $safeAge; ?></td>
        <td><?php echo $safeStatus; ?></td>
        <td>
          <!-- Edit: uses data attributes, no inline JS injection -->
          <button class="action-btn edit-btn"
            data-id="<?php     echo $safeId; ?>"
            data-name="<?php   echo $safeName; ?>"
            data-type="<?php   echo $safeType; ?>"
            data-breed="<?php  echo $safeBreed; ?>"
            data-age="<?php    echo $safeAge; ?>"
            data-status="<?php echo $safeStatus; ?>"
          >Edit</button>

          <!-- Delete: POST form instead of GET link (prevents CSRF via image tags) -->
          <form method="POST" action="delete_pet.php" style="display:inline;"
                onsubmit="return confirm('Delete <?php echo $safeName; ?>? This cannot be undone.')">
            <input type="hidden" name="id" value="<?php echo $safeId; ?>">
            <button type="submit" class="action-btn delete-btn">Delete</button>
          </form>
        </td>
      </tr>
<?php
    endwhile;
else:
?>
      <tr><td colspan="7" style="text-align:center; padding:20px;">You have not posted any pets yet.</td></tr>
<?php endif; ?>
    </tbody>
  </table>
</div>

<!-- EDIT MODAL -->
<div id="editModal" style="display:none;" class="modal-overlay">
  <div class="modal-box">
    <h2>Edit Pet</h2>
    <form method="POST" action="update_pet.php">
      <input type="hidden" name="id" id="editId">

      <label>Name</label>
      <input name="name"  id="editName"  maxlength="100" required>

      <label>Type</label>
      <select name="type" id="editType" required>
        <option value="Dog">Dog</option>
        <option value="Cat">Cat</option>
      </select>

      <label>Breed</label>
      <input name="breed" id="editBreed" maxlength="100" required>

      <label>Age</label>
      <input name="age"   id="editAge"   type="number" min="0" max="30" required>

      <label>Status</label>
      <select name="status" id="editStatus">
        <option value="Available">Available</option>
        <option value="Booked">Booked</option>
      </select>

      <div class="modal-actions">
        <button type="submit" class="modal-save">Save Changes</button>
        <button type="button" class="modal-cancel" onclick="closeEdit()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<footer class="page-footer"><small>© 2026 Adoptify</small></footer>

<script>
document.querySelectorAll('.edit-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('editId').value     = btn.dataset.id;
    document.getElementById('editName').value   = btn.dataset.name;
    document.getElementById('editType').value   = btn.dataset.type;
    document.getElementById('editBreed').value  = btn.dataset.breed;
    document.getElementById('editAge').value    = btn.dataset.age;
    document.getElementById('editStatus').value = btn.dataset.status;
    document.getElementById('editModal').style.display = 'flex';
  });
});

function closeEdit() {
  document.getElementById('editModal').style.display = 'none';
}

// Close on backdrop click
document.getElementById('editModal').addEventListener('click', function(e) {
  if (e.target === this) closeEdit();
});

const toast = document.getElementById('toast');
if (toast) setTimeout(() => toast.classList.add('hide'), 3500);
</script>

</body>
</html>
