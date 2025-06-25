<?php
require_once __DIR__ . '/../config/db.php';
require_role('admin');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Search Cases | e-Justice Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex">
<!-- Sidebar -->
<div class="w-64 bg-white shadow">
  <?php include __DIR__ . '/partials/admin_nav.php'; ?>
</div>

<!-- Main Content -->
<main class="flex-1 p-10">
  <div class="max-w-5xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">🔍 Search Cases</h1>

    <div class="mb-6">
      <input id="searchBox"
             class="w-full px-5 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none shadow-sm"
             placeholder="Enter case number, title, or client…" autofocus>
    </div>

    <!-- Table container -->
    <div class="bg-white rounded shadow overflow-auto">
      <table class="min-w-full text-sm text-left text-gray-700">
        <thead class="bg-blue-100 text-xs font-semibold uppercase text-gray-700">
          <tr>
            <th class="px-6 py-3">#</th>
            <th class="px-6 py-3">Case No.</th>
            <th class="px-6 py-3">Title</th>
            <th class="px-6 py-3">Client</th>
            <th class="px-6 py-3">Status</th>
            <th class="px-6 py-3">Opened</th>
          </tr>
        </thead>
        <tbody id="resultBody" class="divide-y divide-gray-200">
          <!-- Results will be inserted here -->
        </tbody>
      </table>
      <p id="emptyMsg" class="text-center text-gray-500 py-4 hidden">No matching cases found.</p>
    </div>
  </div>
</main>

<script>
const searchBox = document.getElementById('searchBox');
const resultBody = document.getElementById('resultBody');
const emptyMsg = document.getElementById('emptyMsg');

function render(results) {
  resultBody.innerHTML = '';
  if (results.length === 0) {
    emptyMsg.classList.remove('hidden');
    return;
  }
  emptyMsg.classList.add('hidden');

  results.forEach(r => {
    const row = document.createElement('tr');
    row.className = 'hover:bg-blue-50 cursor-pointer';
    row.onclick = () => location.href = 'update_status.php?id=' + r.id;

    row.innerHTML = `
      <td class="px-6 py-3">${r.id}</td>
      <td class="px-6 py-3">${r.case_number}</td>
      <td class="px-6 py-3">${r.title}</td>
      <td class="px-6 py-3">${r.client_name}</td>
      <td class="px-6 py-3">${r.status}</td>
      <td class="px-6 py-3">${r.created_at.substr(0, 10)}</td>
    `;
    resultBody.appendChild(row);
  });
}

async function doSearch(q) {
  if (q.trim() === '') {
    resultBody.innerHTML = '';
    emptyMsg.classList.add('hidden');
    return;
  }

  const res = await fetch('search_api.php?q=' + encodeURIComponent(q));
  const data = await res.json();
  render(data);
}

let debounce;
searchBox.addEventListener('input', () => {
  clearTimeout(debounce);
  debounce = setTimeout(() => doSearch(searchBox.value), 300);
});
</script>
</body>
</html>
