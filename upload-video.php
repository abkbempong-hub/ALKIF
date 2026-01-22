<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.php');
    exit;
}
// Simple video upload handler for the template (XAMPP/local use).
$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$maxBytes = 50 * 1024 * 1024;
$allowedExt = array('mp4', 'webm', 'ogg');
$uploadLog = __DIR__ . DIRECTORY_SEPARATOR . 'upload-activity.log';
$error = '';
$uploadedPath = '';

function append_upload_log($path, $line)
{
    $timestamp = date('c');
    $entry = $timestamp . ' ' . $line . PHP_EOL;
    @file_put_contents($path, $entry, FILE_APPEND | LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '-';
    if (!isset($_FILES['video_file']) || $_FILES['video_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed. Please select a video file and try again.';
        append_upload_log($uploadLog, "UPLOAD failed ip={$ip} reason=missing_file");
    } else {
        $file = $_FILES['video_file'];
        if ($file['size'] > $maxBytes) {
            $error = 'File is too large. Max size is 50 MB.';
            append_upload_log($uploadLog, "UPLOAD rejected ip={$ip} reason=too_large size={$file['size']}");
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) {
                $error = 'Invalid file type. Allowed: MP4, WebM, OGG.';
                append_upload_log($uploadLog, "UPLOAD rejected ip={$ip} reason=invalid_ext ext={$ext}");
            } else {
                $safeBase = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
                $targetName = $safeBase . '-' . date('Ymd-His') . '.' . $ext;
                $targetPath = $uploadDir . $targetName;
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $uploadedPath = 'uploads/' . $targetName;
                    append_upload_log($uploadLog, "UPLOAD success ip={$ip} file={$uploadedPath} size={$file['size']}");
                } else {
                    $error = 'Could not save the uploaded file.';
                    append_upload_log($uploadLog, "UPLOAD failed ip={$ip} reason=move_failed");
                }
            }
        }
    }
}
?>
<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload Video - Abundant Love Kids Foundation</title>
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
        <?php if ($error): ?>
          <p class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <?php if ($uploadedPath): ?>
          <p class="alert alert-success">Upload complete: <a href="<?php echo htmlspecialchars($uploadedPath, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($uploadedPath, ENT_QUOTES, 'UTF-8'); ?></a></p>
          <div class="embed-responsive embed-responsive-16by9">
            <video class="embed-responsive-item" src="<?php echo htmlspecialchars($uploadedPath, ENT_QUOTES, 'UTF-8'); ?>" controls></video>
          </div>
        <?php endif; ?>
        <div class="text-center" style="margin-top: 20px;">
          <a href="admin-upload.php" class="t-btn t-btn-small">Back to Admin</a>
        </div>
        <form action="upload-video.php" method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label for="video_file">Choose video</label>
            <input type="file" id="video_file" name="video_file" accept=".mp4,.webm,.ogg" required>
          </div>
          <button type="submit" class="t-btn">Upload</button>
        </form>
      </div>
    </section>
  </body>
</html>
