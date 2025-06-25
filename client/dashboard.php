<?php
require_once __DIR__ . '/../config/db.php';
require_role('client');
$userId = current_user()['id'];

// Stats
$stats = db()->prepare("
    SELECT
      (SELECT COUNT(*) FROM cases WHERE client_id = ? AND status != 'Closed') AS active_cases,
      (SELECT COUNT(*) FROM hearings h
         JOIN cases c ON c.id = h.case_id
         WHERE c.client_id = ? AND h.hearing_date >= NOW()) AS upcoming_hearings
");
$stats->execute([$userId, $userId]);
$summary = $stats->fetch(PDO::FETCH_ASSOC);

// Recent cases
$cases = db()->prepare("
    SELECT id, case_number, title, status, updated_at
    FROM cases
    WHERE client_id = ?
    ORDER BY updated_at DESC LIMIT 3
");
$cases->execute([$userId]);

// Notifications
$notifications = db()->prepare("
    SELECT content, created_at FROM notifications
    WHERE user_id = ? ORDER BY created_at DESC LIMIT 5
");
$notifications->execute([$userId]);

audit('client_dashboard');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Client Dashboard | e-Justice</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">

<?php include 'partials/client_nav.php'; ?>

<div class="max-w-6xl mx-auto p-6">
  <h2 class="text-2xl font-semibold mb-4">Welcome, <?= htmlspecialchars(current_user()['name']) ?> 👋</h2>

  <!-- Summary Cards -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-5 rounded shadow text-center">
      <div class="text-lg font-bold text-blue-600"><?= $summary['active_cases'] ?></div>
      <div class="text-sm text-gray-600">Active Cases</div>
    </div>
    <div class="bg-white p-5 rounded shadow text-center">
      <div class="text-lg font-bold text-green-600"><?= $summary['upcoming_hearings'] ?></div>
      <div class="text-sm text-gray-600">Upcoming Hearings</div>
    </div>
    <div class="bg-white p-5 rounded shadow text-center">
      <a href="feedback.php" class="text-blue-500 font-semibold">Send Feedback</a>
    </div>
    <div class="bg-white p-5 rounded shadow text-center">
      <a href="messages.php" class="text-blue-500 font-semibold">Messages</a>
    </div>
  </div>

  <!-- Recent Case Updates -->
  <div class="bg-white p-6 rounded shadow mb-6">
    <h3 class="text-xl font-semibold mb-4">Recent Case Updates</h3>
    <table class="min-w-full table-auto">
      <thead class="bg-blue-50 text-gray-600">
        <tr>
          <th class="px-4 py-2 text-left">Case No.</th>
          <th class="px-4 py-2 text-left">Title</th>
          <th class="px-4 py-2 text-left">Status</th>
          <th class="px-4 py-2 text-left">Updated</th>
          <th class="px-4 py-2 text-left">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cases as $c): ?>
          <tr class="border-t">
            <td class="px-4 py-2"><?= htmlspecialchars($c['case_number']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($c['title']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($c['status']) ?></td>
            <td class="px-4 py-2"><?= date('M d, Y', strtotime($c['updated_at'])) ?></td>
            <td class="px-4 py-2">
              <a href="view_case.php?id=<?= $c['id'] ?>" class="text-blue-600 hover:underline">View</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Notifications -->
  <div class="bg-white p-6 rounded shadow">
    <h3 class="text-xl font-semibold mb-4">Recent Notifications</h3>
    <ul class="space-y-3">
      <?php foreach ($notifications as $n): ?>
        <li class="flex justify-between border-b pb-2">
          <span><?= htmlspecialchars($n['content']) ?></span>
          <span class="text-sm text-gray-500"><?= date('M d H:i', strtotime($n['created_at'])) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

</body>
</html>
