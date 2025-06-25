<?php
// config/notify.php
require_once __DIR__ . '/config.php';   // contains SMTP_* constants
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Store an in-app notification and (optionally) send an e-mail.
 */
function notify(int $userId, string $text): void
{
    /* 1)  Save to notifications table ------------------------- */
    db()->prepare(
        "INSERT INTO notifications (user_id, content) VALUES (?, ?)"
    )->execute([$userId, $text]);


    /* 2)  Look-up the recipient’s e-mail ---------------------- */
    $stmt  = db()->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $email = $stmt->fetchColumn();         // ← CORRECT: only 0 or no arg

    if (!$email) {
        // user has no e-mail on record → we’re done
        return;
    }

    /* 3)  Send the e-mail via PHPMailer ----------------------- */
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = 'tls';         // or 'ssl'
        $mail->Port       = SMTP_PORT;     // 587 for TLS, 465 for SSL

        $mail->setFrom(SMTP_USER, 'e-Justice');
        $mail->addAddress($email);
        $mail->Subject = 'e-Justice Notification';
        $mail->Body    = $text;
        $mail->send();
    } catch (\Throwable $e) {
        // Silently ignore mail errors so the app keeps running
        // You can log $e->getMessage() if desired
    }
}
