<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.php');
    exit;
}
?>
<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Upload Video</title>
    <link rel="icon" href="img/favicon.png">
    <link rel="stylesheet" href="css/plugins.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/custom.css">
  </head>
  <body>
    <section class="section">
      <div class="container">
        <div class="section-header">
          <h2>Upload Background Video</h2>
          <p>Accepted formats: MP4, WebM, OGG. Max size: 50 MB.</p>
        </div>
        <form action="upload-video.php" method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label for="video_file">Choose video</label>
            <input type="file" id="video_file" name="video_file" accept=".mp4,.webm,.ogg" required>
          </div>
          <button type="submit" class="t-btn">Upload</button>
        </form>
        <div class="text-center" style="margin-top: 20px;">
          <a href="admin-logout.php" class="t-btn t-btn-small">Sign Out</a>
        </div>
      </div>
    </section>
  </body>
</html>
