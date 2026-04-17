<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Pet | Adoptify</title>
  <link rel="stylesheet" href="css/add-pet.css">
</head>
<body>

<header>
  <a href="adopt-now.php" id="backBtn">← Back</a>
</header>

<h2>Add a Pet</h2>

<?php if (isset($_GET['error'])): ?>
  <p class="msg error" style="text-align:center;color:red;margin:10px;"><?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<form method="POST" action="add_pet_process.php" enctype="multipart/form-data">

  <label>Pet Name</label>
  <input type="text" name="name" required maxlength="100">

  <label>Pet Type</label>
  <select name="type" required>
    <option value="">Select type</option>
    <option value="Dog">Dog</option>
    <option value="Cat">Cat</option>
  </select>

  <label>Age (years)</label>
  <input type="number" name="age" required min="0" max="30">

  <label>Breed</label>
  <input type="text" name="breed" required maxlength="100">

  <label>Bio</label>
  <textarea name="bio" required maxlength="500"></textarea>

  <div id="dropzone" class="dropzone">Click or Drag Image Here</div>
  <input type="file" name="image" id="petImageFile" accept="image/*" required style="display:none;">
  <img id="preview" style="display:none; width:200px; margin-top:10px; border-radius:10px;">

  <button type="submit">Add Pet</button>
</form>

<script>
const dropzone  = document.getElementById("dropzone");
const fileInput = document.getElementById("petImageFile");
const preview   = document.getElementById("preview");

function showPreview(file) {
  if (!file) return;
  preview.src          = URL.createObjectURL(file);
  preview.style.display = "block";
  dropzone.textContent  = "✅ " + file.name;
}

dropzone.addEventListener("click",     () => fileInput.click());
fileInput.addEventListener("change",   () => showPreview(fileInput.files[0]));
dropzone.addEventListener("dragover",  e  => { e.preventDefault(); dropzone.style.background = "#cde8ce"; });
dropzone.addEventListener("dragleave", ()  => { dropzone.style.background = "#eef6ee"; });
dropzone.addEventListener("drop",      e  => {
  e.preventDefault();
  const dt = new DataTransfer();
  dt.items.add(e.dataTransfer.files[0]);
  fileInput.files = dt.files;
  showPreview(e.dataTransfer.files[0]);
});
</script>

</body>
</html>
