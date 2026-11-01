<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/policy_access.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');

$token = trim((string) ($_GET['key'] ?? ''));
$tokenHash = hash('sha256', $token);

if ($token === '' || !hash_equals(POLICY_GUEST_TOKEN_HASH, $tokenHash)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'ลิงก์ไม่ถูกต้องหรือถูกยกเลิกแล้ว กรุณาขอลิงก์ใหม่จากผู้ดูแลระบบ';
    exit;
}

session_regenerate_id(true);
$_SESSION['policy_guest'] = true;
$_SESSION['policy_guest_started_at'] = time();

require __DIR__ . '/dashboard.php';
exit;

