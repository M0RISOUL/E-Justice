<?php
use PHPMailer\PHPMailer\PHPMailer;

/** Send an in-app + email notification. */
function notify(int $userId, string $text): void
{
    // 1) store notification
    db()->prepare("INSERT INTO notifications (user_id, content)
                   VALUES (?, ?)")->execute([$userId, $text]);

    // 2) look up recipient email
    $stmt  = db()->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $email = $stmt->fetchColumn();   // ← fixed

    if (!$email) return;             // user has no email on record

    // 3) send email (optional)
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = 'tls';       // or 'ssl'
    $mail->Port       = SMTP_PORT;   // 587 or 465

    $mail->setFrom(SMTP_USER, 'e-Justice');
    $mail->addAddress($email);
    $mail->Subject = 'e-Justice Notification';
    $mail->Body    = $text;
    $mail->send();
}
