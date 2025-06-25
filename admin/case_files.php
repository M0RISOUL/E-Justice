<?php
require_once __DIR__ . '/../config/db.php';
require_role('admin');

$cases = db()->query("
  SELECT c.*, CONCAT(u.first_name,' ',u.last_name) AS client
  FROM cases c 
  JOIN users u ON u.id = c.client_id 
  ORDER BY c.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Case Files</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-800">
<div class="flex min-h-screen">
  <!-- Sidebar -->
  <aside class="w-64 bg-white border-r hidden md:block">
    <?php include __DIR__ . '/partials/admin_nav.php'; ?>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-6 md:p-10">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-blue-700">Case Files</h1>
    </div>

    <div class="bg-white shadow rounded-lg overflow-x-auto">
      <table class="min-w-full text-sm text-left">
        <thead class="bg-blue-100 text-gray-700 font-semibold">
          <tr>
            <th class="px-6 py-3">#</th>
            <th class="px-6 py-3">Case No.</th>
            <th class="px-6 py-3">Title</th>
            <th class="px-6 py-3">Client</th>
            <th class="px-6 py-3">Status</th>
            <th class="px-6 py-3">Opened</th>
            <th class="px-6 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
        <?php foreach ($cases as $case): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-3"><?= $case['id'] ?></td>
            <td class="px-6 py-3"><?= htmlspecialchars($case['case_number']) ?></td>
            <td class="px-6 py-3"><?= htmlspecialchars($case['title']) ?></td>
            <td class="px-6 py-3"><?= htmlspecialchars($case['client']) ?></td>
            <td class="px-6 py-3">
              <span class="inline-block px-2 py-1 text-xs rounded-full 
                <?= $case['status'] === 'Closed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                <?= htmlspecialchars($case['status']) ?>
              </span>
            </td>
            <td class="px-6 py-3"><?= date('Y-m-d', strtotime($case['created_at'])) ?></td>
            <td class="px-6 py-3 text-right">
              <a href="update_status.php?id=<?= $case['id'] ?>" class="text-blue-600 hover:underline">Update</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
</body>
</html>
