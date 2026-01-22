<?php
require_once __DIR__ . '/contact-config.php';
require_once __DIR__ . '/vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$submissionLog = __DIR__ . '/contact-submissions.log';
function append_log_line($path, $line)
{
    $timestamp = date('c');
    $entry = $timestamp . ' ' . $line . PHP_EOL;
    @file_put_contents($path, $entry, FILE_APPEND | LOCK_EX);
}

$success = false;
$error = '';
$logFile = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '-';
    append_log_line(
        $submissionLog,
        "CONTACT attempt ip={$ip} email=" . ($email ?: '-') . " subject=" . ($subject ?: '-')
    );

    if ($name === '' || $email === '' || $subject === '' || $message === '') {
        $error = 'Please complete all required fields.';
        append_log_line($submissionLog, "CONTACT rejected ip={$ip} reason=missing_fields");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
        append_log_line($submissionLog, "CONTACT rejected ip={$ip} reason=invalid_email email={$email}");
    } elseif ($smtpUser === 'your-gmail-address@gmail.com' || $smtpPass === 'your-app-password') {
        $error = 'Email is not configured. Please update contact-config.php.';
        append_log_line($submissionLog, "CONTACT rejected ip={$ip} reason=not_configured");
    } else {
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $safePhone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
        $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
        $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        $mailSubject = "Website Contact: {$safeSubject}";
        $bodyLines = array(
            "Name: {$safeName}",
            "Email: {$safeEmail}",
            "Phone/WhatsApp: {$safePhone}",
            "Subject: {$safeSubject}",
            "",
            "Message:",
            $safeMessage
        );
        $body = implode("\r\n", $bodyLines);

        $mailer = new PHPMailer(true);
        try {
            $mailer->isSMTP();
            $mailer->Host = $smtpHost;
            $mailer->SMTPAuth = true;
            $mailer->Username = $smtpUser;
            $mailer->Password = $smtpPass;
            $mailer->SMTPSecure = $smtpSecure;
            $mailer->Port = $smtpPort;
            $mailer->SMTPDebug = $smtpDebug;
            if ($smtpDebug > 0) {
                $logFile = $smtpDebugLog ?: (__DIR__ . '/smtp-debug.log');
                $mailer->Debugoutput = function ($str, $level) use ($logFile) {
                    file_put_contents($logFile, date('c') . " [{$level}] {$str}\r\n", FILE_APPEND);
                };
            }

            $mailer->setFrom($smtpUser, $mailFromName);
            $mailer->addAddress($mailTo);
            $mailer->addReplyTo($safeEmail, $safeName);
            $mailer->Subject = $mailSubject;
            $mailer->Body = $body;

            $success = $mailer->send();
            if ($success) {
                append_log_line($submissionLog, "CONTACT sent ip={$ip} email={$safeEmail} subject={$safeSubject}");
            } else {
                append_log_line($submissionLog, "CONTACT failed ip={$ip} reason=send_failed");
            }
        } catch (Exception $e) {
            $error = 'Message could not be sent. Please try again later.';
            if (!empty($logFile)) {
                file_put_contents($logFile, date('c') . " [ERROR] {$e->getMessage()}\r\n", FILE_APPEND);
            }
            append_log_line($submissionLog, "CONTACT failed ip={$ip} reason=exception");
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
    <title>Contact - ALKIF</title>
    <link rel="icon" href="img/favicon.png">
    <link rel="stylesheet" href="css/plugins.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/custom.css">
  </head>
  <body>
    <section class="section">
      <div class="container">
        <div class="section-header">
          <h2>Contact Result</h2>
          <p>Thank you for reaching out to ALKIF.</p>
        </div>
        <?php if ($success): ?>
          <p class="alert alert-success">Your message has been sent successfully. We will respond within two business days.</p>
        <?php else: ?>
          <p class="alert alert-danger"><?php echo htmlspecialchars($error ?: 'Please use the form to contact us.', ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <a href="index.html#contact" class="t-btn t-btn-small">Back to Contact</a>
      </div>
    </section>
  </body>
</html>

