<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
require_once __DIR__ . '/../php/config.php';
$email = $argv[1] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php tools/promote-admin.php registered-email\n"); exit(1);
}
$db = getDB();
$query = $db->prepare('SELECT id FROM users WHERE email = ? AND status = ?');
$query->execute([$email, 'active']);
$id = $query->fetchColumn();
if (!$id) { fwrite(STDERR, "Register an active local account first.\n"); exit(1); }
$query = $db->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
$query->execute([$id]);
echo "Administrator role assigned. Log out and log in again.\n";
