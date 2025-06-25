<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/notify.php';
require_role('admin');

$id = (int)($_GET['id'] ?? 0);
$c = db()->prepare("SELECT * FROM cases WHERE id = ?");
$c->execute([$id]);
$case = $c->fetch(PDO::FETCH_ASSOC);

if (!$case) {
    http_response_code(404);
    echo "<div style='padding:2rem; font-family:sans-serif;'>
            <h1 style='color:#e11d48;'>❌ Case not found</h1>
            <p>Please check the URL or case ID.</p>
          </div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s = $_POST['status'];
    $notes = trim($_POST['notes']);
    db()->prepare("UPDATE cases SET status = ? WHERE id = ?")->execute([$s, $id]);
    notify($case['client_id'], "Case {$case['case_number']} status updated to $s.");
    audit('case_status_updated', 'case', $id);
    header("Location: case_files.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Update Case Status</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
<div class="flex min-h-screen">

  <!-- Sidebar -->
  <aside class="w-64 bg-white border-r hidden md:block">
    <?php include __DIR__ . '/partials/admin_nav.php'; ?>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-10">
    <div class="max-w-2xl mx-auto">
      <div class="bg-white shadow-xl rounded-lg p-8">
        <h1 class="text-3xl font-bold mb-2">🗂️ Update Case Status</h1>
        <p class="text-gray-500 mb-6">Case Number: <span class="font-semibold text-blue-600"><?= htmlspecialchars($case['case_number']) ?></span></p>

        <form method="post" class="space-y-6">
          <!-- Status Dropdown -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">🔖 Status</label>
            <select name="status" required class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-400">
              <?php foreach (['Filed', 'Under Review', 'Scheduled', 'Closed'] as $s): ?>
                <option value="<?= $s ?>" <?= $case['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
            <p class="text-xs text-gray-400 mt-1">Choose the current progress of the case.</p>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">📝 Notes</label>
            <textarea name="notes" rows="4" class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-400" placeholder="Optional internal notes or case remarks"><?= htmlspecialchars($case['description']) ?></textarea>
            <p class="text-xs text-gray-400 mt-1">Provide internal context for the status update (optional).</p>
          </div>

          <!-- Submit Button -->
          <div class="text-right">
            <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded transition">💾 Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
</body>
</html>
