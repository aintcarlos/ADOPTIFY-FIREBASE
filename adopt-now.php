<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['redirect'] = "adopt-now.php";
    header("Location: login.php");
    exit();
}

// ── Auto-seed demo pets if table is empty ──
$check = $conn->query("SELECT COUNT(*) as total FROM pets");
$row   = $check->fetch_assoc();

if ($row['total'] == 0) {
    $demo_pets = [
        ['Buddy',   'Dog', 2, 'Labrador Retriever', 'Buddy is a playful and energetic Labrador who loves to fetch and swim. Great with kids!',                'https://images.unsplash.com/photo-1560807707-8cc77767d783?w=400'],
        ['Max',     'Dog', 4, 'German Shepherd',    'Max is a loyal and intelligent dog. He is well-trained and very protective of his family.',              'https://images.unsplash.com/photo-1589941013453-ec89f33b5e95?w=400'],
        ['Charlie', 'Dog', 1, 'Golden Retriever',   'Charlie is a sweet and gentle puppy who loves cuddles and playing in the park.',                         'https://images.unsplash.com/photo-1633722715463-d30f4f325e24?w=400'],
        ['Rocky',   'Dog', 3, 'Beagle',             'Rocky is a curious and friendly Beagle who loves going on walks and sniffing everything.',               'https://images.unsplash.com/photo-1505628346881-b72b27e84530?w=400'],
        ['Cooper',  'Dog', 5, 'Siberian Husky',     'Cooper is a beautiful Husky who loves cold weather and long outdoor adventures.',                        'https://images.unsplash.com/photo-1617895153857-82fe0c4f8def?w=400'],
        ['Luna',    'Cat', 2, 'Persian',            'Luna is a calm and elegant Persian cat who loves quiet environments and gentle cuddles.',                 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400'],
        ['Milo',    'Cat', 1, 'Siamese',            'Milo is a talkative and social Siamese kitten. He loves to follow you around the house.',                'https://images.unsplash.com/photo-1513360371489-6b4e2b3e6826?w=400'],
        ['Bella',   'Cat', 3, 'Maine Coon',         'Bella is a fluffy and friendly Maine Coon who gets along well with other pets.',                         'https://images.unsplash.com/photo-1568572933382-74d440642117?w=400'],
        ['Oliver',  'Cat', 4, 'British Shorthair',  'Oliver is a laid-back and gentle cat. Perfect for apartment living.',                                    'https://images.unsplash.com/photo-1533743983669-94fa5c4338ec?w=400'],
        ['Cleo',    'Cat', 2, 'Ragdoll',            'Cleo is an affectionate Ragdoll who loves being held and will follow you everywhere.',                    'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=400'],
    ];

    // Use a system owner so demo pets don't appear in any real user's My Pets page
    $owner = 'adoptify_system';
    $stmt  = $conn->prepare("INSERT INTO pets (name, type, age, breed, bio, image, status, owner) VALUES (?, ?, ?, ?, ?, ?, 'Available', ?)");

    foreach ($demo_pets as $pet) {
        $stmt->bind_param("ssissss", $pet[0], $pet[1], $pet[2], $pet[3], $pet[4], $pet[5], $owner);
        $stmt->execute();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Adoptify — Adopt Now</title>
  <link rel="stylesheet" href="css/adopt-now.css">
</head>
<body>

<!-- TOAST -->
<?php if (isset($_GET['success'])): ?>
<div class="toast success" id="toast">✅ <?php echo htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8'); ?></div>
<?php elseif (isset($_GET['error'])): ?>
<div class="toast error" id="toast">❌ <?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<!-- NAVBAR -->
<header class="top-header">
  <div class="brand"><h1>ADOPTIFY</h1></div>
  <nav class="nav-actions">
    <a href="adopt-now.php">Home</a>
    <a href="add-pet.php">Add Pet</a>
    <a href="adoption-list.php">Adoption List</a>
    <a href="my-pets.php">My Pets</a>
    <a href="my-meetups.php">My Meetups</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<main class="page-grid">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="paw-decor">🐾</div>
    <form method="GET" class="filter-card">
      <h3>Refine Search</h3>

      <label>Pet Type</label>
      <select name="type">
        <option value="">All</option>
        <option value="Dog"  <?php echo (($_GET['type'] ?? '') === 'Dog'  ? 'selected' : ''); ?>>Dog</option>
        <option value="Cat"  <?php echo (($_GET['type'] ?? '') === 'Cat'  ? 'selected' : ''); ?>>Cat</option>
      </select>

      <label>Max Age</label>
      <input type="number" name="age" value="<?php echo htmlspecialchars($_GET['age'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" min="0" max="30">

      <button class="btn green full" type="submit">Apply Filters</button>
      <a href="adopt-now.php" class="btn clear">Clear</a>
    </form>
  </aside>

  <!-- CONTENT -->
  <section class="content">
    <h2>Find Your Pet Companion</h2>

    <div class="pets-grid">
<?php
$type = $_GET['type'] ?? '';
$age  = intval($_GET['age'] ?? 0);

$sql    = "SELECT * FROM pets WHERE status = 'Available'";
$params = [];
$types  = "";

if ($type) {
    $sql    .= " AND type = ?";
    $params[] = $type;
    $types   .= "s";
}
if ($age > 0) {
    $sql    .= " AND age <= ?";
    $params[] = $age;
    $types   .= "i";
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
        $safeName  = htmlspecialchars($row['name'],  ENT_QUOTES, 'UTF-8');
        $safeType  = htmlspecialchars($row['type'],  ENT_QUOTES, 'UTF-8');
        $safeAge   = intval($row['age']);
        $safeBio   = htmlspecialchars($row['bio'],   ENT_QUOTES, 'UTF-8');
        $safeImage = htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8');
        $safeId    = intval($row['id']);
?>
      <div class="pet-card">
        <div class="pet-media" style="background-image:url('<?php echo (strpos($safeImage, 'http') === 0) ? $safeImage : 'images/' . $safeImage; ?>')"></div>
        <div class="pet-body">
          <h4><?php echo $safeName; ?></h4>
          <p><?php echo $safeType; ?> · <?php echo $safeAge; ?>y</p>
          <p><?php echo $safeBio; ?></p>
          <button class="view-btn"
            data-id="<?php echo $safeId; ?>"
            data-name="<?php echo $safeName; ?>"
            data-type="<?php echo $safeType; ?>"
            data-age="<?php echo $safeAge; ?>"
            data-bio="<?php echo $safeBio; ?>"
            data-img="<?php echo (strpos($safeImage, 'http') === 0) ? $safeImage : 'images/' . $safeImage; ?>"
          >View Details</button>
        </div>
      </div>
<?php
    endwhile;
else:
    echo "<p>No pets available.</p>";
endif;
?>
    </div>
  </section>
</main>

<!-- MODAL -->
<div class="modal" id="petModal" aria-hidden="true">
  <div class="modal-card">
    <div class="modal-left">
      <img id="modalImg" alt="Pet photo">
    </div>
    <div class="modal-right">
      <h2 id="modalName"></h2>
      <p><strong>Type:</strong> <span id="modalType"></span></p>
      <p><strong>Age:</strong>  <span id="modalAge"></span></p>
      <p id="modalBio"></p>
      <div class="modal-actions">
        <button onclick="addPet()" class="add-btn">+ Add to List</button>
        <button onclick="closeModal()" class="close-btn">Close</button>
      </div>
    </div>
  </div>
</div>

<footer class="page-footer"><small>© 2026 Adoptify</small></footer>

<script>
let selectedPetId = null;

// Open modal via data attributes (safe — no inline JS injection)
document.querySelectorAll('.view-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    selectedPetId = btn.dataset.id;
    document.getElementById('modalName').textContent = btn.dataset.name;
    document.getElementById('modalType').textContent = btn.dataset.type;
    document.getElementById('modalAge').textContent  = btn.dataset.age + ' years old';
    document.getElementById('modalBio').textContent  = btn.dataset.bio;
    document.getElementById('modalImg').src          = btn.dataset.img;
    document.getElementById('petModal').classList.add('active');
  });
});

function closeModal() {
  document.getElementById('petModal').classList.remove('active');
  selectedPetId = null;
}

function addPet() {
  if (!selectedPetId) return;
  window.location.href = 'add-to-list.php?id=' + encodeURIComponent(selectedPetId);
}

// Close on backdrop click
document.getElementById('petModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

// Auto-dismiss toast
const toast = document.getElementById('toast');
if (toast) setTimeout(() => toast.classList.add('hide'), 3500);
</script>

</body>
</html>
