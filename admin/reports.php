<?php
require_once __DIR__ . '/../config/db.php';
require_role('admin');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Reports & Analytics | e-Justice</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800">
<div class="flex min-h-screen">
  <!-- Sidebar -->
  <div class="w-64">
    <?php include __DIR__ . '/partials/admin_nav.php'; ?>
  </div>

  <!-- Main Content -->
  <main class="flex-1 p-8">
    <h2 class="text-2xl font-bold mb-6">📊 Reports & Analytics</h2>

    <div id="kpiRow" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- KPIs inserted by JS -->
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <!-- Bar Chart -->
      <div class="bg-white rounded shadow p-4">
        <h3 class="font-semibold mb-2 text-gray-700">Case Overview</h3>
        <div class="relative h-56">
          <canvas id="barChart" class="absolute top-0 left-0 w-full h-full"></canvas>
        </div>
      </div>

      <!-- Pie Chart -->
      <div class="bg-white rounded shadow p-4">
        <h3 class="font-semibold mb-2 text-gray-700">Active vs Closed</h3>
        <div class="relative h-56">
          <canvas id="pieChart" class="absolute top-0 left-0 w-full h-full"></canvas>
        </div>
      </div>

      <!-- Line Chart -->
      <div class="bg-white rounded shadow p-4">
        <h3 class="font-semibold mb-2 text-gray-700">Monthly Trend</h3>
        <div class="relative h-56">
          <canvas id="lineChart" class="absolute top-0 left-0 w-full h-full"></canvas>
        </div>
      </div>

      <!-- Doughnut Chart -->
      <div class="bg-white rounded shadow p-4">
        <h3 class="font-semibold mb-2 text-gray-700">Clients vs Hearings</h3>
        <div class="relative h-56">
          <canvas id="doughnutChart" class="absolute top-0 left-0 w-full h-full"></canvas>
        </div>
      </div>

      <!-- Radar Chart -->
      <div class="bg-white rounded shadow p-4">
        <h3 class="font-semibold mb-2 text-gray-700">Case Types</h3>
        <div class="relative h-56">
          <canvas id="radarChart" class="absolute top-0 left-0 w-full h-full"></canvas>
        </div>
      </div>

      <!-- Polar Area Chart -->
      <div class="bg-white rounded shadow p-4">
        <h3 class="font-semibold mb-2 text-gray-700">Status Distribution</h3>
        <div class="relative h-56">
          <canvas id="polarChart" class="absolute top-0 left-0 w-full h-full"></canvas>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
fetch('report.php')
  .then(r => r.json())
  .then(stats => {
    // Populate KPIs
    const kpiRow = document.getElementById('kpiRow');
    const tiles = [
      { label: 'Total Cases', value: stats.total_cases },
      { label: 'Closed Cases', value: stats.closed },
      { label: 'Clients', value: stats.total_clients },
      { label: 'Upcoming Hearings', value: stats.upcoming_hearings }
    ];
    tiles.forEach(t => {
      const el = document.createElement('div');
      el.className = 'bg-white rounded shadow p-4 text-center';
      el.innerHTML = `<div class='text-sm text-gray-500'>${t.label}</div>
                      <div class='text-2xl font-semibold mt-1'>${t.value}</div>`;
      kpiRow.appendChild(el);
    });

    // Bar Chart
    new Chart(barChart, {
      type: 'bar',
      data: {
        labels: ['Total', 'Closed', 'Upcoming'],
        datasets: [{
          label: 'Cases',
          data: [stats.total_cases, stats.closed, stats.upcoming_hearings],
          backgroundColor: ['#3B82F6', '#22C55E', '#FACC15']
        }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    });

    // Pie Chart
    new Chart(pieChart, {
      type: 'pie',
      data: {
        labels: ['Active', 'Closed'],
        datasets: [{
          data: [stats.total_cases - stats.closed, stats.closed],
          backgroundColor: ['#60A5FA', '#4ADE80']
        }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    });

    // Line Chart
    new Chart(lineChart, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Total Cases',
          data: Array(6).fill(stats.total_cases),
          fill: true,
          backgroundColor: 'rgba(59,130,246,0.1)',
          borderColor: '#3B82F6'
        }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    });

    // Doughnut Chart
    new Chart(doughnutChart, {
      type: 'doughnut',
      data: {
        labels: ['Clients', 'Hearings'],
        datasets: [{
          data: [stats.total_clients, stats.upcoming_hearings],
          backgroundColor: ['#f472b6', '#818cf8']
        }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    });

    // Radar Chart (sample static)
    new Chart(radarChart, {
      type: 'radar',
      data: {
        labels: ['Criminal', 'Civil', 'Labor', 'Family', 'Others'],
        datasets: [{
          label: 'Case Types',
          data: [12, 19, 6, 8, 3],
          backgroundColor: 'rgba(255,99,132,0.2)',
          borderColor: 'rgba(255,99,132,1)'
        }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    });

    // Polar Area Chart (sample static)
    new Chart(polarChart, {
      type: 'polarArea',
      data: {
        labels: ['Filed', 'Under Review', 'Scheduled', 'Closed'],
        datasets: [{
          data: [5, 7, 10, 15],
          backgroundColor: ['#e879f9', '#fcd34d', '#f87171', '#34d399']
        }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    });
  });
</script>
</body>
</html>
