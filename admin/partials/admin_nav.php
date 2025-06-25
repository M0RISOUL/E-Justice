<?php
require_once __DIR__ . '/../../config/db.php';
require_role('admin');

$stats = db()->query("
  SELECT
    (SELECT COUNT(*) FROM cases) AS cases_total,
    (SELECT COUNT(*) FROM cases WHERE status='Closed') AS cases_closed,
    (SELECT COUNT(*) FROM hearings WHERE hearing_date >= CURDATE()) AS hearings_upcoming,
    (SELECT COUNT(*) FROM users
        WHERE role_id = (SELECT id FROM roles WHERE name='client')) AS clients
")->fetch(PDO::FETCH_ASSOC);

audit('admin_dashboard');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">
<div class="flex min-h-screen">
  <!-- Sidebar -->
  <aside class="w-64 bg-white border-r hidden md:block">
    <div class="p-4">
      <!-- <img src="../assets/user.jpg" alt="Logo" class="h-12 mx-auto"> -->
      <div class="text-center mt-4">
        <img src="../assets/user.jpg" class="w-20 h-20 rounded-full mx-auto border" alt="Admin">
        <h3 class="mt-2 font-semibold text-lg">Morisoul</h3>
        <p class="text-sm text-green-500">Administrator</p>
      </div>
    </div>
    <nav class="mt-6 px-4 space-y-1">
      <a href="dashboard.php" class="flex items-center px-2 py-2 text-sm font-medium text-gray-700 hover:bg-blue-50 rounded">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9 2v6m0 0v6m0-6h6"></path>
        </svg>
        Dashboard
      </a>

      <a href="add_case.php" class="flex items-center px-2 py-2 text-sm text-gray-700 hover:bg-blue-50 rounded">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add Case
      </a>

      <a href="case_files.php" class="flex items-center px-2 py-2 text-sm text-gray-700 hover:bg-blue-50 rounded">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h13v6M9 12H4m0 0V5a2 2 0 012-2h11a2 2 0 012 2v7"></path>
        </svg>
        Case Files
      </a>

      <a href="search.php" class="flex items-center px-2 py-2 text-sm text-gray-700 hover:bg-blue-50 rounded">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"></path>
        </svg>
        Search
      </a>

      <!-- ✅ New Link for Document Uploads -->
      <a href="admin_upload.php" class="flex items-center px-2 py-2 text-sm text-gray-700 hover:bg-blue-50 rounded">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16V4H4zm4 4h8v2H8V8zm0 4h8v2H8v-2zm0 4h6v2H8v-2z" />
        </svg>
        Document Uploads
      </a>

      <a href="reports.php" class="flex items-center px-2 py-2 text-sm text-gray-700 hover:bg-blue-50 rounded">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18"></path>
        </svg>
        Report
      </a>

      <a href="schedule.php" class="flex items-center px-2 py-2 text-sm text-gray-700 hover:bg-blue-50 rounded">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        Schedule
      </a>

      <a href="update_status.php" class="flex items-center px-2 py-2 text-sm text-gray-700 hover:bg-blue-50 rounded">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h10M11 9h10M5 13h10M5 17h10"></path>
        </svg>
        Update Status
      </a>

      <a href="users.php" class="flex items-center px-2 py-2 text-sm text-gray-700 hover:bg-blue-50 rounded">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m3-6a4 4 0 110-8 4 4 0 010 8zM17 11a4 4 0 100-8 4 4 0 000 8z"></path>
        </svg>
        Users
      </a>

      <a href="<?= BASE_URL ?>/logout.php" class="flex items-center px-2 py-2 text-sm text-red-600 hover:bg-red-50 rounded">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        Logout
      </a>
    </nav>
  </aside>
</div>
</body>
</html>
