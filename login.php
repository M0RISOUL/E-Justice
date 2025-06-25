<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

const ADMIN_EMAIL    = 'admin.gov@gmail.com';
const ADMIN_PLAIN_PW = 'password123';

function seed_admin(): void {
    $pdo = db();
    $roleId = $pdo->query("SELECT id FROM roles WHERE name = 'admin'")->fetchColumn();
    if (!$roleId) {
        $pdo->exec("INSERT INTO roles (name) VALUES ('admin')");
        $roleId = $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = ?");
    $stmt->execute([strtolower(ADMIN_EMAIL)]);
    if (!$stmt->fetchColumn()) {
        $hash = password_hash(ADMIN_PLAIN_PW, PASSWORD_DEFAULT);
        $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, password_hash, role_id, is_active)
            VALUES ('Mike', 'Seo', ?, ?, ?, 1)
        ")->execute([ADMIN_EMAIL, $hash, $roleId]);
    }
}
seed_admin();

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';
    $cap   = $_POST['captcha'] ?? '';

    if (hash_hmac('sha256', $cap, CAPTCHA_SECRET) !== ($_SESSION['captcha_hash'] ?? '')) {
        $error = 'Invalid CAPTCHA';
    } else {
        $stmt = db()->prepare("
            SELECT u.*, r.name AS role
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE LOWER(u.email) = ? AND u.is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($u && password_verify($pass, $u['password_hash'])) {
            $_SESSION['user'] = [
                'id'   => $u['id'],
                'name' => $u['first_name'] . ' ' . $u['last_name'],
                'role' => $u['role']
            ];
            audit('login_success');
            header('Location: index.php');
            exit;
        }

        $error = 'Wrong email or password';
        audit('login_failed');
    }
}

$token = bin2hex(random_bytes(3));
$_SESSION['captcha_hash'] = hash_hmac('sha256', $token, CAPTCHA_SECRET);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | e-Justice</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background-image: url('https://images.unsplash.com/photo-1570129477492-45c003edd2be?auto=format&fit=crop&w=1600&q=80'); /* Justice courtroom */
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }
    @keyframes slideFade {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-slide-fade {
      animation: slideFade 0.6s ease-out both;
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-black bg-opacity-50 relative">

  <!-- Dark overlay -->
  <div class="absolute inset-0 bg-black bg-opacity-60"></div>

  <!-- Login Form -->
  <div class="relative z-10 w-full max-w-md p-8 bg-white/10 backdrop-blur-md border border-white/30 rounded-2xl shadow-xl animate-slide-fade">
    <h2 class="text-3xl font-bold text-white text-center mb-6">⚖️ e-Justice Login</h2>

    <?php if ($error): ?>
      <div class="bg-red-100 text-red-800 text-sm px-4 py-2 rounded mb-4">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <input type="email" name="email" placeholder="Email"
             class="w-full px-4 py-2 rounded bg-white/80 text-black border border-gray-300 placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500" required>

      <input type="password" name="password" placeholder="Password"
             class="w-full px-4 py-2 rounded bg-white/80 text-black border border-gray-300 placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500" required>

      <div class="flex items-center gap-2">
        <span class="px-3 py-1 bg-white/90 text-sm font-mono rounded"><?= $token ?></span>
        <input name="captcha" maxlength="6" placeholder="Enter CAPTCHA"
               class="flex-1 px-3 py-2 rounded bg-white/80 text-black border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>

      <button type="submit"
              class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded transition">
        Log In
      </button>
    </form>

    <div class="text-sm text-gray-300 text-center mt-4 space-x-2">
      <a href="password_reset.php" class="hover:underline">Forgot password?</a>
      <span>|</span>
      <a href="registration.php" class="hover:underline">Create account</a>
    </div>
  </div>

</body>
</html>
