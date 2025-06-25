<?php
require_once __DIR__ . '/../config/db.php';
require_role('admin');

// Save form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caseNo = strtoupper(trim($_POST['case_number']));
    $title  = trim($_POST['title']);
    $desc   = trim($_POST['description']);
    $client = (int)$_POST['client_id'];

    db()->prepare("INSERT INTO cases (case_number, title, description, client_id)
                   VALUES (?,?,?,?)")->execute([$caseNo, $title, $desc, $client]);

    audit('case_created', 'case', db()->lastInsertId());
    header('Location: dashboard.php?msg=added');
    exit;
}

// Fetch client list
$clients = db()->query("SELECT u.id, CONCAT(u.first_name,' ',u.last_name) AS name
                        FROM users u
                        JOIN roles r ON r.id = u.role_id
                        WHERE r.name = 'client'")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add New Case</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">
<div class="flex min-h-screen">
  <!-- Sidebar -->
  <aside class="w-64 bg-white border-r hidden md:block">
    <?php include __DIR__ . '/partials/admin_nav.php'; ?>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-8">
    <div class="max-w-3xl mx-auto">
      <h1 class="text-3xl font-bold text-blue-700 mb-6">Add New Case</h1>

      <form method="post" class="bg-white p-6 rounded-lg shadow space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Case Number</label>
            <input name="case_number" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input name="title" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400" required>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea name="description" rows="4" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400"></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Assign to Client</label>
          <select name="client_id" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400">
            <?php foreach ($clients as $id => $name): ?>
              <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="text-right">
          <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow">
            ➕ Save Case
          </button>
        </div>
      </form>
    </div>
  </main>
</div>
</body>
</html>
