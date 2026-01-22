<?php
session_start();
require_once __DIR__ . '/admin-config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === $adminUser && password_verify($password, $adminPassHash)) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin-upload.php');
        exit;
    }

    $error = 'Invalid username or password.';
}
?>
<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - ALKIF</title>
    <link rel="icon" href="img/favicon.png">
    <link rel="stylesheet" href="css/plugins.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/custom.css">
  </head>
  <body>
    <section class="section">
      <div class="container">
        <div class="section-header">
          <h2>Admin Login</h2>
          <p>Sign in to manage the background video.</p>
        </div>
        <?php if ($error): ?>
          <p class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <form action="admin-login.php" method="post">
          <div class="row">
            <div class="col-sm-6">
              <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="col-sm-6">
              <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="col-sm-12 text-center">
              <button type="submit" class="t-btn submit-btn">Sign In</button>
            </div>
          </div>
        </form>
      </div>
    </section>
  </body>
</html>

