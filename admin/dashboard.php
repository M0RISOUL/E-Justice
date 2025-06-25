<?php
require_once __DIR__ . '/../config/db.php';
require_role('admin');

$stats = db()->query("
  SELECT
    (SELECT COUNT(*) FROM cases) AS cases_total,
    (SELECT COUNT(*) FROM cases WHERE status='Closed') AS cases_closed,
    (SELECT COUNT(*) FROM hearings WHERE hearing_date >= CURDATE()) AS hearings_upcoming,
    (SELECT COUNT(*) FROM users WHERE role_id = (SELECT id FROM roles WHERE name='client')) AS clients
")->fetch(PDO::FETCH_ASSOC);

audit('admin_dashboard');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    #notifDropdown { display: none; }
    #notifDropdown.active { display: block; }
  </style>
</head>
<body class="bg-gray-100 text-gray-800">
<div class="flex min-h-screen">
  <?php include __DIR__ . '/partials/admin_nav.php'; ?>

  <main class="flex-1 p-8">
    <div class="flex justify-between items-start mb-6">
      <div>
        <h2 class="text-2xl font-bold">Admin Dashboard</h2>
        <p class="text-sm text-gray-600">Welcome, <span class="font-semibold"><?= htmlspecialchars(current_user()['name']) ?></span></p>
      </div>

      <!-- 🔔 Notification Button -->
      <div class="relative">
        <button onclick="toggleNotifications()" class="bg-white border rounded px-4 py-2 hover:bg-gray-100">
          🔔 Notifications
        </button>
        <div id="notifDropdown" class="absolute right-0 mt-2 w-80 bg-white shadow-lg rounded border max-h-96 overflow-y-auto z-50">
          <div class="p-4 font-bold border-b">Notifications</div>
          <ul id="notifList" class="divide-y">
            <li class="p-4 text-sm text-gray-500">Loading...</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white shadow rounded p-4 text-center">
        <div class="text-sm text-gray-500">Total Cases</div>
        <div class="text-2xl font-semibold"><?= $stats['cases_total'] ?></div>
      </div>
      <div class="bg-green-100 shadow rounded p-4 text-center">
        <div class="text-sm text-gray-600">Closed Cases</div>
        <div class="text-2xl font-semibold text-green-800"><?= $stats['cases_closed'] ?></div>
      </div>
      <div class="bg-blue-100 shadow rounded p-4 text-center">
        <div class="text-sm text-gray-600">Upcoming Hearings</div>
        <div class="text-2xl font-semibold text-blue-800"><?= $stats['hearings_upcoming'] ?></div>
      </div>
      <div class="bg-yellow-100 shadow rounded p-4 text-center">
        <div class="text-sm text-gray-600">Clients</div>
        <div class="text-2xl font-semibold text-yellow-800"><?= $stats['clients'] ?></div>
      </div>
    </div>

    <h3 class="text-lg font-medium mb-4">Case Distribution</h3>

    <div class="flex justify-between items-center mb-4">
      <select id="timeRange" class="border rounded px-3 py-1">
        <option value="week">This Week</option>
        <option value="month">This Month</option>
        <option value="year">This Year</option>
      </select>
      <select id="chartType" class="border rounded px-3 py-1">
        <option value="bar">Bar Chart</option>
        <option value="line">Line Chart</option>
        <option value="pie">Pie Chart</option>
      </select>
    </div>

    <div class="bg-white rounded shadow p-4 h-80">
      <canvas id="caseChart" class="w-full h-full"></canvas>
    </div>
  </main>
</div>

<script>
function toggleNotifications() {
  document.getElementById('notifDropdown').classList.toggle('active');
}

function loadNotifications() {
  axios.get('api/get_notifications.php')
    .then(res => {
      const notifList = document.getElementById('notifList');
      notifList.innerHTML = '';

      if (!res.data.length) {
        notifList.innerHTML = '<li class="p-4 text-sm text-gray-500">No notifications found.</li>';
        return;
      }

      res.data.forEach(n => {
        const li = document.createElement('li');
        li.className = 'p-4 text-sm hover:bg-blue-50 cursor-pointer';
        li.innerHTML = `<div class="font-semibold text-blue-700 mb-1">• ${n.content}</div>
                        <div class="text-xs text-gray-500">${new Date(n.created_at).toLocaleString()}</div>`;
        notifList.appendChild(li);
      });
    })
    .catch(err => {
      document.getElementById('notifList').innerHTML = '<li class="p-4 text-sm text-red-600">Error loading notifications.</li>';
    });
}

window.addEventListener('DOMContentLoaded', loadNotifications);

const ctx = document.getElementById('caseChart').getContext('2d');
let chart;
const dataValues = [<?= $stats['cases_total'] ?>, <?= $stats['cases_closed'] ?>, <?= $stats['hearings_upcoming'] ?>, <?= $stats['clients'] ?>];
const labels = ['Total Cases', 'Closed Cases', 'Upcoming Hearings', 'Clients'];

function renderChart(type = 'bar') {
  if (chart) chart.destroy();
  chart = new Chart(ctx, {
    type: type,
    data: {
      labels: labels,
      datasets: [{
        label: 'Summary',
        data: dataValues,
        backgroundColor: ['#3b82f6', '#22c55e', '#60a5fa', '#facc15'],
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: type === 'pie' ? {} : { y: { beginAtZero: true } }
    }
  });
}

document.getElementById('chartType').addEventListener('change', e => renderChart(e.target.value));
renderChart();
</script>
</body>
</html>
