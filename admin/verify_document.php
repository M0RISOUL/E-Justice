<?php
require_once __DIR__ . '/../config/db.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['doc_id'])) {
    $docId = (int)$_POST['doc_id'];

    $stmt = db()->prepare("UPDATE documents SET is_verified = 1 WHERE id = ?");
    $stmt->execute([$docId]);

    audit('document_verified', 'document', $docId);

    header("Location: admin_upload.php?verified=1");
    exit;
} else {
    header("Location: admin_upload.php");
    exit;
}
