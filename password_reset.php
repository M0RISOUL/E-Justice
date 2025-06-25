<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/config.php';

use PHPMailer\PHPMailer\PHPMailer;

start_session();

$token = $_GET['token'] ?? '';
$error = '';
$info  = '';

/* ---------- 1. REQUEST RESET LINK ---------- */
if (!$token && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $uidStmt = db()->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
    $uidStmt->execute([$email]);
    $uid = $uidStmt->fetchColumn();

    if (!$uid) {
        $error = "Email not found.";
    } else {
        $tk = bin2hex(random_bytes(16));
        $hash = password_hash($tk, PASSWORD_DEFAULT);
        db()->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at)
                       VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))")->execute([$uid, $hash]);

        // Send email
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_USER, 'e-Justice');
        $mail->addAddress($email);
        $link = BASE_URL . "/password_reset.php?token=$tk";
        $mail->Subject = 'Password Reset Request';
        $mail->Body = "Click to reset your password: $link";
        $mail->send();

        $info = "Reset link sent. Check your email.";
        audit('password_reset_requested', 'user', $uid);
    }

/* ---------- 2. HANDLE PASSWORD RESET ---------- */
} elseif ($token && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw  = $_POST['password'];
    $pw2 = $_POST['password2'];

    if ($pw !== $pw2 || strlen($pw) < 6) {
        $error = 'Passwords must match and be at least 6 characters.';
    } else {
        $row = db()->prepare("SELECT * FROM password_resets WHERE expires_at > NOW() ORDER BY id DESC");
        $row->execute();
        $rows = $row->fetchAll(PDO::FETCH_ASSOC);
        $valid = null;
        foreach ($rows as $r) {
            if (password_verify($token, $r['token_hash'])) {
                $valid = $r;
                break;
            }
        }

        if (!$valid) {
            $error = 'Token is invalid or expired.';
        } else {
            db()->prepare("UPDATE users SET password_hash = ?, is_active = 1 WHERE id = ?")
               ->execute([password_hash($pw, PASSWORD_DEFAULT), $valid['user_id']]);
            db()->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$valid['user_id']]);
            audit('password_reset', 'user', $valid['user_id']);
            $info = "✅ Password updated! <a href='login.php' class='underline text-blue-600'>Log in</a>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Password Reset | e-Justice</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background-image: url('https://images.unsplash.com/photo-1589820296159-d1e4173d235b?auto=format&fit=crop&w=1600&q=80'); /* Courtroom image */
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }
    .animate-slide-fade {
      animation: slideFade 0.5s ease-out both;
    }
    @keyframes slideFade {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center relative bg-black bg-opacity-50">

  <!-- Overlay -->
  <div class="absolute inset-0 bg-black bg-opacity-60 z-0"></div>

  <!-- Reset Panel -->
  <div class="relative z-10 w-full max-w-md p-8 bg-white/10 backdrop-blur-md border border-white/30 rounded-2xl shadow-xl animate-slide-fade text-white">
    <h2 class="text-2xl font-bold text-center mb-5">
      <?= $token ? '🔐 Set New Password' : '🔁 Forgot Password' ?>
    </h2>

    <?php if ($error): ?>
      <div class="bg-red-200 text-red-800 px-4 py-2 rounded mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($info): ?>
      <div class="bg-green-200 text-green-800 px-4 py-2 rounded mb-4"><?= $info ?></div>
    <?php endif; ?>

    <?php if (!$token): ?>
      <form method="POST" class="space-y-4">
        <input name="email" type="email" placeholder="Enter your email"
               class="w-full px-4 py-2 rounded bg-white/80 text-black border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded transition">
          Send Reset Link
        </button>
      </form>
    <?php elseif (!$info): ?>
      <form method="POST" class="space-y-4">
        <input type="password" name="password" placeholder="New Password"
               class="w-full px-4 py-2 rounded bg-white/80 text-black border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <input type="password" name="password2" placeholder="Confirm Password"
               class="w-full px-4 py-2 rounded bg-white/80 text-black border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded transition">
          Change Password
        </button>
      </form>
    <?php endif; ?>

    <div class="text-center mt-4 text-sm">
      <a href="login.php" class="text-blue-300 hover:underline">← Back to login</a>
    </div>
  </div>

</body>
</html>
