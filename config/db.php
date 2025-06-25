<?php
require_once 'config.php';

start_session();

/**
 * Returns array of the logged-in user or null if guest.
 */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Guard route for admins or clients.
 * @param string ...$roles allowed roles
 */
function require_role(string ...$roles): void
{
    $user = current_user();
    if (!$user || !in_array($user['role'], $roles, true)) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Log an action to audit_logs.
 */
function audit(string $action, ?string $objectType = null, ?int $objectId = null): void
{
    $u = current_user();
    $stmt = db()->prepare(
        "INSERT INTO audit_logs (user_id, action, object_type, object_id, ip, user_agent)
         VALUES (?,?,?,?,?,?)"
    );
    $stmt->execute([
        $u['id'] ?? null,
        $action,
        $objectType,
        $objectId,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}
