<?php
/*
 | client/partials/client_nav.php
 | Works no matter how deep you include it (client/, client/sub/, etc.)
*/
if (!function_exists('current_user')) {
    require_once __DIR__ . '/../../config/db.php';
}

// Build absolute path using BASE_URL so it never breaks
$logoutLink = BASE_URL . '/logout.php';
?>
<nav style="background:#0d6efd;color:#fff;padding:.7rem 1.5rem;">
  <strong>e-Justice</strong>
  <span style="float:right;">
    Logged in as <?= htmlspecialchars(current_user()['name']) ?> |
    <a href="<?= $logoutLink ?>" style="color:#fff;text-decoration:underline;">Logout</a>
  </span>
</nav>
