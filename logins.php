<?php
require_once __DIR__ . '/../config/db.php';

start_session();
if (current_user()) {
    header('Location: index.php'); // already logged in
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    // CAPTCHA check (server-side)
    if (hash_hmac('sha256', $_POST['captcha'] ?? '', CAPTCHA_SECRET) !== ($_SESSION['captcha_hash'] ?? ''))
        $error = 'Invalid CAPTCHA';
    else {
        $stmt = db()->prepare("SELECT u.*, r.name AS role FROM users u JOIN roles r ON r.id = u.role_id
                               WHERE email = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($pass, $user['password_hash'])) {
            $_SESSION['user'] = [
                'id'    => (int)$user['id'],
                'name'  => $user['first_name'] . ' ' . $user['last_name'],
                'role'  => $user['role']
            ];
            audit('login_success');
            header('Location: index.php');
            exit;
        }
        $error = 'Wrong credentials.';
        audit('login_failed');
    }
}

// generate captcha token
$token = bin2hex(random_bytes(3));
$_SESSION['captcha_hash'] = hash_hmac('sha256', $token, CAPTCHA_SECRET);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><link rel="stylesheet" href="assets/css/styles.css">
  <title>Login | e-Justice</title>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
  <form class="card p-4" method="post" style="min-width:320px;">
      <h3 class="mb-3 text-center">e-Justice Login</h3>
      <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <div class="mb-3"><input name="email" class="form-control" placeholder="Email" required></div>
      <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
      <!-- simple captcha -->
      <div class="mb-3 d-flex align-items-center">
          <span class="me-2 fw-bold"><?= $token ?></span>
          <input maxlength="6" name="captcha" class="form-control" placeholder="Enter text">
      </div>
      <button class="btn btn-primary w-100">Login</button>
      <div class="text-center mt-2"><a href="password_reset.php">Forgot password?</a></div>
  </form>
</body>
</html>
