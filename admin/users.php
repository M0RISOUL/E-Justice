<?php
require_once __DIR__ . '/../config/db.php';
require_role('admin');

// Handle new user submission
if (isset($_POST['add'])) {
    $fn = $_POST['fname'];
    $ln = $_POST['lname'];
    $em = $_POST['email'];
    $role = $_POST['role'];

    $stmt = db()->prepare("SELECT id FROM roles WHERE name=?");
    $stmt->execute([$role]);
    $rid = $stmt->fetchColumn();

    db()->prepare("INSERT INTO users (role_id, first_name, last_name, email, password_hash)
                   VALUES (?, ?, ?, ?, ?)")
       ->execute([$rid, $fn, $ln, $em, password_hash('123456', PASSWORD_DEFAULT)]);

    audit('user_added', 'user', db()->lastInsertId());
    header("Location: users.php?added=1");
    exit;
}

// Fetch all users
$users = db()->query("
    SELECT u.*, r.name AS role
    FROM users u
    JOIN roles r ON r.id = u.role_id
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>User Management</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">

<div class="flex min-h-screen">
  <!-- Sidebar -->
  <?php include __DIR__ . '/partials/admin_nav.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-10">
    <div class="max-w-6xl mx-auto">

      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold">👥 User Management</h1>
          <p class="text-gray-500 text-sm">Manage administrators and client users</p>
        </div>
      </div>

      <?php if (isset($_GET['added'])): ?>
        <div class="mb-6 bg-green-100 text-green-800 border border-green-300 px-4 py-3 rounded">
          ✅ New user added successfully!
        </div>
      <?php endif; ?>

      <!-- User Table -->
      <div class="bg-white shadow rounded-lg overflow-x-auto mb-10">
        <table class="min-w-full text-sm text-left">
          <thead class="bg-blue-100 text-gray-700 uppercase text-xs">
            <tr>
              <th class="px-6 py-3">ID</th>
              <th class="px-6 py-3">Name</th>
              <th class="px-6 py-3">Email</th>
              <th class="px-6 py-3">Role</th>
            </tr>
          </thead>
          <tbody class="text-gray-700">
          <?php foreach ($users as $u): ?>
            <tr class="border-b hover:bg-gray-50">
              <td class="px-6 py-3"><?= $u['id'] ?></td>
              <td class="px-6 py-3"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
              <td class="px-6 py-3"><?= htmlspecialchars($u['email']) ?></td>
              <td class="px-6 py-3 capitalize"><?= $u['role'] ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Add New User -->
      <div class="bg-white shadow-md rounded-lg p-6 max-w-lg mx-auto">
        <h2 class="text-xl font-semibold mb-4">➕ Add New User</h2>
        <form method="post" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">First Name</label>
            <input name="fname" class="w-full border border-gray-300 rounded px-3 py-2" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Last Name</label>
            <input name="lname" class="w-full border border-gray-300 rounded px-3 py-2" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input name="email" type="email" class="w-full border border-gray-300 rounded px-3 py-2" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Role</label>
            <select name="role" class="w-full border border-gray-300 rounded px-3 py-2" required>
              <option value="admin">Admin</option>
              <option value="client">Client</option>
            </select>
          </div>
          <div class="text-right">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded" name="add">
              Create User (PW: 123456)
            </button>
          </div>
        </form>
      </div>

    </div>
  </main>
</div>

</body>
</html>
