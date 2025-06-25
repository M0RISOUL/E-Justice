<?php
require_once __DIR__ . '/../config/db.php';
require_role('client');

$id = (int)($_GET['id'] ?? 0);
$uid = current_user()['id'];

$stmt = db()->prepare("SELECT * FROM cases WHERE id=? AND client_id=?");
$stmt->execute([$id, $uid]);
$case = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$case) {
    http_response_code(404);
    exit('Case not found');
}

// Timeline: hearing notes + status changes
$history = db()->prepare("
   SELECT 'Hearing' AS type, hearing_date AS dt, notes FROM hearings WHERE case_id = ?
   UNION ALL
   SELECT 'Status' AS type, updated_at AS dt, CONCAT('Status changed to → ', status) FROM cases WHERE id = ?
   ORDER BY dt DESC");
$history->execute([$id, $id]);

// Upload handler
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['evidence'])) {
    $f = $_FILES['evidence'];
    $uploadDir = __DIR__ . '/../uploads/evidence/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
    $safeName = uniqid('evidence_', true) . '.' . $ext;
    $dest = $uploadDir . $safeName;

    if (move_uploaded_file($f['tmp_name'], $dest)) {
        db()->prepare("INSERT INTO documents (case_id, filename, mime, uploaded_by)
                      VALUES (?, ?, ?, ?)")
           ->execute([$id, $safeName, $f['type'], $uid]);
        db()->prepare("INSERT INTO notifications (user_id, content, is_read, created_at)
                      VALUES (?, ?, 0, NOW())")
           ->execute([$uid, 'Evidence uploaded for case ' . $case['case_number']]);
        $msg = '✅ Evidence uploaded successfully.';
    } else {
        $msg = '❌ Failed to upload file.';
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Case <?= htmlspecialchars($case['case_number']) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">

<?php include 'partials/client_nav.php'; ?>

<div class="max-w-4xl mx-auto p-6 mt-6 bg-white shadow rounded">

  <a href="dashboard.php" class="text-sm text-blue-600 hover:underline mb-3 inline-block">← Back to Dashboard</a>

  <h2 class="text-2xl font-bold mb-2">
    Case <?= htmlspecialchars($case['case_number']) ?> – <?= htmlspecialchars($case['title']) ?>
  </h2>

  <p class="mb-6 whitespace-pre-line"><?= htmlspecialchars($case['description']) ?></p>

  <h4 class="text-lg font-semibold mb-3">Timeline</h4>
  <ul class="space-y-3 mb-6">
    <?php foreach($history as $h): ?>
      <li class="bg-gray-50 p-3 rounded border-l-4 <?= $h['type'] === 'Hearing' ? 'border-blue-400' : 'border-green-400' ?>">
        <div class="text-sm text-gray-700"><strong><?= $h['type'] ?>:</strong> <?= htmlspecialchars($h['notes']) ?></div>
        <div class="text-xs text-gray-500"><?= date('M d, Y • h:i A', strtotime($h['dt'])) ?></div>
      </li>
    <?php endforeach; ?>
  </ul>

  <?php if ($msg): ?>
    <div class="mb-4 p-3 rounded <?= str_contains($msg, '✅') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <div class="flex gap-3">
    <button onclick="document.getElementById('evidenceModal').classList.remove('hidden')" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded">
      📎 Upload Evidence
    </button>
    <a href="download_summary.php?case=<?= $id ?>" class="border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white px-4 py-2 rounded">
      📄 Download PDF Summary
    </a>
  </div>
</div>

<!-- Modal -->
<div id="evidenceModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
  <div class="bg-white p-6 rounded shadow-md w-full max-w-md relative">
    <h3 class="text-lg font-semibold mb-4">Upload Evidence</h3>
    <form method="post" enctype="multipart/form-data" class="space-y-4">
      <input type="file" name="evidence" accept=".pdf,.jpg,.jpeg,.png,.docx"
             class="w-full border border-gray-300 p-2 rounded" required>
      <div class="flex justify-end space-x-2">
        <button type="button" onclick="document.getElementById('evidenceModal').classList.add('hidden')"
                class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
        <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Upload</button>
      </div>
    </form>
    <button onclick="document.getElementById('evidenceModal').classList.add('hidden')"
            class="absolute top-2 right-2 text-gray-500 hover:text-black">&times;</button>
  </div>
</div>

</body>
</html>
