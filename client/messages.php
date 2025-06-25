<?php
require_once __DIR__ . '/../config/db.php';
require_role('client');

$uid = current_user()['id'];

// Send new message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = trim($_POST['body']);
    db()->prepare("INSERT INTO messages (sender_id, receiver_id, body)
                 VALUES (?, ?, ?)")->execute([$uid, 1, $body]); // 1 = admin
    audit('client_message_sent');
    header("Location: messages.php"); // Prevent resubmit on refresh
    exit;
}

// Fetch messages in ascending order (oldest first)
$msgs = db()->prepare("
    SELECT m.*, IF(m.sender_id = ?, 'You', 'Admin') sender
    FROM messages m
    WHERE sender_id = ? OR receiver_id = ?
    ORDER BY sent_at ASC LIMIT 100
");
$msgs->execute([$uid, $uid, $uid]);
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Messages</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    .chat-scroll {
      max-height: 500px;
      overflow-y: auto;
    }
  </style>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">

<?php include 'partials/client_nav.php'; ?>

<div class="max-w-4xl mx-auto p-6">

  <!-- Back Button -->
  <a href="dashboard.php" class="inline-flex items-center text-sm text-blue-600 hover:underline mb-4">
    ← Back to Dashboard
  </a>

  <h2 class="text-2xl font-semibold mb-6">Messages with Admin</h2>

  <!-- Message List -->
  <div id="chatBox" class="chat-scroll bg-white p-4 rounded shadow mb-6 space-y-4">
    <?php foreach ($msgs as $m): ?>
      <div class="<?= $m['sender_id'] == $uid ? 'text-right' : 'text-left' ?>">
        <div class="inline-block max-w-xl px-4 py-2 rounded-lg 
                    <?= $m['sender_id'] == $uid ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700' ?>">
          <div class="text-sm font-semibold mb-1">
            <?= htmlspecialchars($m['sender']) ?> • <?= date('M d, H:i', strtotime($m['sent_at'])) ?>
          </div>
          <div class="whitespace-pre-wrap"><?= htmlspecialchars($m['body']) ?></div>
          <?php if ($m['sender_id'] == $uid && $m['is_read']): ?>
            <div class="text-xs text-green-600 mt-1">✓ Seen by Admin</div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Send Form -->
  <form method="post" class="flex gap-3">
    <input name="body" required
           class="flex-1 p-3 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring focus:border-blue-400"
           placeholder="Type a message...">
    <button class="px-5 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Send</button>
  </form>

</div>

<!-- Scroll to bottom -->
<script>
  window.onload = function () {
    const chatBox = document.getElementById('chatBox');
    chatBox.scrollTop = chatBox.scrollHeight;
  };
</script>

</body>
</html>
