<?php
require_once __DIR__ . '/../config/db.php';
require_role('client');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = trim($_POST['body']);
    if ($body !== '') {
        db()->prepare("INSERT INTO messages (sender_id, receiver_id, body)
                      VALUES (?, ?, ?)")
            ->execute([current_user()['id'], 1, $body]); // 1 = super admin
        audit('feedback_sent');
        $msg = '✅ Thank you for your feedback!';
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Feedback</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen text-gray-800">

<?php include 'partials/client_nav.php'; ?>

<div class="max-w-xl mx-auto p-6 mt-8 bg-white shadow rounded">
  <!-- Back button -->
  <a href="dashboard.php" class="inline-flex items-center text-sm text-blue-600 hover:underline mb-4">
    ← Back to Dashboard
  </a>

  <h2 class="text-2xl font-semibold mb-4">Send Feedback</h2>

  <?php if (isset($msg)): ?>
    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded border border-green-300">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <form method="post" class="space-y-4">
    <textarea name="body" rows="5" required
              class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring focus:border-blue-500"
              placeholder="Write your feedback here..."></textarea>

    <button type="submit"
            class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition">
      Submit Feedback
    </button>
  </form>
</div>

</body>
</html>
