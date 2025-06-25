<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/notify.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caseId = (int)$_POST['case_id'];
    $date   = $_POST['date'];
    $room   = trim($_POST['room']);
    $notes  = trim($_POST['notes']);

    db()->prepare("INSERT INTO hearings (case_id, hearing_date, courtroom, notes, created_by)
                   VALUES (?, ?, ?, ?, ?)")
       ->execute([$caseId, $date, $room, $notes, current_user()['id']]);

    $stmt = db()->prepare("SELECT client_id FROM cases WHERE id = ?");
    $stmt->execute([$caseId]);
    $clientId = $stmt->fetchColumn();

    if ($clientId) {
        notify($clientId, "New hearing scheduled on $date for your case.");
    }

    audit('hearing_created', 'case', $caseId);
    header("Location: schedule.php?success=1");
    exit;
}

$cases = db()->query("
    SELECT id, case_number
    FROM cases
    WHERE status != 'Closed'
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Schedule Hearing</title>
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
    <div class="max-w-3xl mx-auto">
      <h1 class="text-3xl font-bold mb-2">🧾 Schedule Hearing</h1>
      <p class="text-gray-500 mb-6">Fill in the form to assign a hearing to an open case.</p>

      <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded mb-6">
          ✅ Hearing successfully scheduled.
        </div>
      <?php endif; ?>

      <div class="bg-white shadow-lg rounded-xl p-8 space-y-6">
        <form method="post" class="space-y-6">

          <!-- Case Selection -->
          <div>
            <label class="block text-sm font-semibold mb-1 text-gray-700">🔍 Select Case</label>
            <select name="case_id" required class="w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-400">
              <option value="" disabled selected>Choose an open case</option>
              <?php foreach ($cases as $id => $num): ?>
                <option value="<?= $id ?>"><?= htmlspecialchars($num) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Hearing Date -->
          <div>
            <label class="block text-sm font-semibold mb-1 text-gray-700">📆 Hearing Date & Time</label>
            <input type="datetime-local" name="date" required autofocus class="w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-400">
          </div>

          <!-- Courtroom/Link -->
          <div>
            <label class="block text-sm font-semibold mb-1 text-gray-700">🏛️ Courtroom / Virtual Link</label>
            <input type="text" name="room" placeholder="e.g. Courtroom A-1 or Zoom link" class="w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-400">
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-semibold mb-1 text-gray-700">📝 Additional Notes</label>
            <textarea name="notes" rows="4" class="w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-400" placeholder="Optional instructions or remarks..."></textarea>
          </div>

          <!-- Submit -->
          <div class="text-right">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">📤 Save Hearing</button>
          </div>

        </form>
      </div>
    </div>
  </main>
</div>
</body>
</html>
