<?php
require_once __DIR__ . '/config/db.php';

$hash = password_hash('secretpass', PASSWORD_DEFAULT);
$stmt = db()->prepare("INSERT INTO users (role_id, first_name, last_name, email, password_hash)
                       VALUES (1, 'Super', 'Admin', 'admin@gmail.com', ?)");
$stmt->execute([$hash]);

echo "✅ Admin user created with email: admin@gmail.com and password: secretpass\n";
