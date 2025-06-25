<?php
require __DIR__ . '/vendor/autoload.php';  // ✅ tiyaking nasa tamang path ka

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'abordajejetboy20@gmail.com';          // <-- palitan ng totoong Gmail
    $mail->Password   = 'password';       // <-- dapat Gmail App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('abordajejetboy20@gmail.com', 'Test Mail');
    $mail->addAddress('receiver@example.com');
    $mail->Subject = 'It works!';
    $mail->Body    = 'Hello world from e-Justice system.';
    $mail->send();
    echo '✅ Email sent successfully.';
} catch (Exception $e) {
    echo "❌ Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
