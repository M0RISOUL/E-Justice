<?php
require_once __DIR__ . '/../config/db.php';
require_role('admin');

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $file = $_FILES['document'];
    $caseId = (int)$_POST['case_id'];

    $original = $file['name'];
    $ext = pathinfo($original, PATHINFO_EXTENSION);
    $safeName = uniqid('doc_', true) . '.' . $ext;
    $mime = $file['type'];

    if (!is_dir(__DIR__ . '/../uploads')) {
        mkdir(__DIR__ . '/../uploads', 0755, true);
    }

    move_uploaded_file($file['tmp_name'], __DIR__ . '/../uploads/' . $safeName);

    db()->prepare("INSERT INTO documents (case_id, filename, mime, uploaded_by, is_verified)
                   VALUES (?, ?, ?, ?, 0)")
       ->execute([$caseId, $safeName, $mime, current_user()['id']]);

    audit('document_uploaded', 'case', $caseId);
    header("Location: admin_upload.php?success=1");
    exit;
}

// Get all documents
$docs = db()->query("
    SELECT d.*, CONCAT(u.first_name, ' ', u.last_name) AS uploader
    FROM documents d
    JOIN users u ON u.id = d.uploaded_by
    ORDER BY uploaded_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Get case list for dropdown
$cases = db()->query("SELECT id, case_number FROM cases ORDER BY created_at DESC")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin - Upload Documents</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
<div class="min-h-screen flex flex-col md:flex-row">

  <!-- Sidebar -->
  <aside class="w-full md:w-64 bg-white shadow-md">
    <?php include __DIR__ . '/partials/admin_nav.php'; ?>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-6 md:p-10">
    <div class="max-w-4xl mx-auto">
      <h1 class="text-3xl font-bold mb-6 text-blue-800">📤 Upload Case Document</h1>

      <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded mb-6">
          ✅ File uploaded successfully!
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['verified'])): ?>
        <div class="bg-blue-100 border border-blue-300 text-blue-800 px-4 py-3 rounded mb-6">
          ✅ Document has been verified successfully!
        </div>
      <?php endif; ?>

      <!-- Upload Form -->
      <div class="bg-white p-6 rounded-lg shadow mb-10">
        <form method="post" enctype="multipart/form-data">
          <div class="mb-4">
            <label class="block font-medium mb-1">Attach to Case</label>
            <select name="case_id" required class="w-full border-gray-300 rounded p-2">
              <option value="" disabled selected>Select a case</option>
              <?php foreach ($cases as $id => $num): ?>
                <option value="<?= $id ?>"><?= htmlspecialchars($num) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-4">
            <label class="block font-medium mb-1">Upload File</label>
            <input type="file" name="document" required class="w-full border-gray-300 rounded p-2">
          </div>

          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-semibold">
            Upload Document
          </button>
        </form>
      </div>

      <!-- Uploaded Files -->
      <h2 class="text-2xl font-semibold mb-4">📄 Uploaded Documents</h2>
      <div class="grid md:grid-cols-2 gap-6">
        <?php foreach ($docs as $doc): ?>
          <div class="bg-white rounded-lg shadow p-4">
            <p class="font-semibold text-lg break-all"><?= htmlspecialchars($doc['filename']) ?></p>
            <p class="text-sm text-gray-600">📁 Case ID: <?= $doc['case_id'] ?></p>
            <p class="text-sm text-gray-600">👤 Uploaded by: <?= htmlspecialchars($doc['uploader']) ?></p>
            <p class="text-sm text-gray-600">📄 MIME: <?= $doc['mime'] ?></p>
            <p class="text-sm mt-2">
              Status:
              <span class="<?= $doc['is_verified'] ? 'text-green-600' : 'text-yellow-600' ?>">
                <?= $doc['is_verified'] ? '✅ Verified' : '⏳ Pending' ?>
              </span>
            </p>
            <div class="mt-3 flex items-center space-x-4">
              <a href="../uploads/<?= urlencode($doc['filename']) ?>" target="_blank" class="text-blue-600 hover:underline">
                🔍 View Document
              </a>

              <?php if (!$doc['is_verified']): ?>
                <button onclick="openVerifyModal(<?= $doc['id'] ?>)" class="text-green-600 hover:underline font-semibold">
                  ✅ Verify
                </button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </main>
</div>

<!-- Modal -->
<div id="verifyModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm text-center">
    <div class="text-red-600 text-4xl mb-2">❗</div>
    <h2 class="text-xl font-semibold mb-2">Confirm</h2>
    <p class="mb-4">Are you sure you want to mark this document as <strong>Verified</strong>?</p>

    <form id="verifyForm" method="post" action="verify_document.php">
      <input type="hidden" name="doc_id" id="docIdInput">
      <div class="flex justify-center gap-4">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Yes, Verify!</button>
        <button type="button" onclick="closeVerifyModal()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Scripts -->
<script>
  function openVerifyModal(docId) {
    document.getElementById('docIdInput').value = docId;
    document.getElementById('verifyModal').classList.remove('hidden');
    document.getElementById('verifyModal').classList.add('flex');
  }

  function closeVerifyModal() {
    document.getElementById('verifyModal').classList.add('hidden');
    document.getElementById('verifyModal').classList.remove('flex');
  }
</script>

</body>
</html>
