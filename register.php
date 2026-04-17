<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: adopt-now.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | Adoptify</title>
  <link rel="stylesheet" href="css/register.css">
</head>
<body>

<section class="slideshow">
  <figure></figure><figure></figure><figure></figure><figure></figure><figure></figure>
</section>
<section class="overlay"></section>

<main>
  <section class="logo-area">
    <img src="images/logo.png" alt="Adoptify Logo">
  </section>

  <h1>ADOPTIFY</h1>
  <h2>Create your account</h2>

  <?php if (isset($_GET['error'])): ?>
    <p class="msg error"><?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?></p>
  <?php endif; ?>

  <form action="register_process.php" method="POST">
    <input type="text"     name="username" placeholder="Username"          required autocomplete="username">
    <input type="email"    name="email"    placeholder="Email"             required autocomplete="email">
    <input type="password" name="password" placeholder="Password"          required autocomplete="new-password">
    <input type="password" name="confirm"  placeholder="Confirm Password"  required autocomplete="new-password">
    <input type="tel"      name="contact"  placeholder="09xxxxxxxxx"       required>

    <p class="hint">Min 8 chars · 1 uppercase · 1 number · 1 special (!@#$%^&amp;*)</p>

    <button type="submit">REGISTER</button>
    <a href="login-register.php" class="back">← Back</a>
  </form>
</main>

<footer>
  <p>© 2026 Adoptify</p>
</footer>

</body>
</html>
