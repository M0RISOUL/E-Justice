<?php
require_once __DIR__ . '/../config/db.php';
require_role('client');

function notify($userId, $message) {
    db()->prepare("INSERT INTO notifications (user_id, content, is_read, created_at)
                  VALUES (?, ?, 0, NOW())")->execute([$userId, $message]);
}

$cid = (int)($_GET['case'] ?? 0);
$case = db()->prepare("SELECT * FROM cases WHERE id=? AND client_id=?");
$case->execute([$cid, current_user()['id']]);
$case = $case->fetch(PDO::FETCH_ASSOC);

if (!$case) {
    http_response_code(404);
    exit('Case not found');
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['file']['name'])) {
    $f = $_FILES['file'];
    $uploadDir = __DIR__ . '/../uploads/evidence/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
    $safeName = uniqid('evidence_', true) . '.' . $ext;
    $dest = $uploadDir . $safeName;

    if (move_uploaded_file($f['tmp_name'], $dest)) {
        db()->prepare("INSERT INTO documents (case_id, filename, mime, uploaded_by)
                      VALUES (?, ?, ?, ?)")->execute([$cid, $safeName, $f['type'], current_user()['id']]);
        notify(current_user()['id'], 'Evidence uploaded for case ' . $case['case_number']);
        audit('evidence_uploaded', 'case', $cid);
        $msg = '✅ Evidence uploaded successfully.';
    } else {
        $msg = '❌ Upload failed.';
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Upload Evidence</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'partials/client_nav.php'; ?>
<div class="max-w-xl mx-auto mt-10 px-4">

  <a href="view_case.php?id=<?= $cid ?>" class="inline-block mb-4 text-blue-600 hover:underline">&larr; Back to Case</a>

  <div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Upload Evidence – <?= htmlspecialchars($case['case_number']) ?></h2>

    <?php if ($msg): ?>
      <div class="mb-4 p-3 rounded <?= str_contains($msg, '✅') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block text-gray-700 mb-1">Select a file</label>
        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.docx" required
               class="w-full border border-gray-300 p-2 rounded-md">
      </div>
      <button type="submit"
              class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition">
        Upload Evidence
      </button>
    </form>
  </div>
</div>
</body>
</html>
