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
  <title>Login | Adoptify</title>
  <link rel="stylesheet" href="css/login.css">
</head>
<body>

<section class="slideshow">
  <figure></figure><figure></figure><figure></figure><figure></figure><figure></figure>
</section>
<section class="overlay"></section>

<main id="loginScreen">
  <section class="logo-area">
    <img src="images/logo.png" alt="Adoptify Logo">
  </section>

  <h1>ADOPTIFY</h1>
  <h2>Welcome back</h2>

  <?php if (isset($_GET['error'])): ?>
    <p class="msg error"><?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?></p>
  <?php endif; ?>
  <?php if (isset($_GET['success'])): ?>
    <p class="msg success"><?php echo htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8'); ?></p>
  <?php endif; ?>

  <form action="login_process.php" method="POST">
    <input type="email"    name="email"    placeholder="Email"    required autocomplete="email">
    <input type="password" name="password" placeholder="Password" required autocomplete="current-password">

    <div class="actions">
      <button type="submit">LOGIN</button>
      <a href="login-register.php" class="back">BACK</a>
    </div>
  </form>
</main>

<footer>
  <p>© 2026 Adoptify</p>
</footer>

</body>
</html>
