<?php
declare(strict_types=1);

define('DB_HOST', 'localhost');
define('DB_NAME', 'ejustice');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', '/e-justice');        // adjust if in sub-folder
define('CAPTCHA_SECRET', 'CHANGE_ME');          // random 32-char string
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'abordajejetboy20@gmail.com'); // no-reply@example.com
define('SMTP_PASS', 'maew jboq bywb cqnc'); //email-password
define('SMTP_PORT', 587);

// Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// PDO instance helper
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }
    return $pdo;
}

// Secure session start
function start_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name('ejustice_sid');
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
        session_start();
    }
}
