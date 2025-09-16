<?php
// api/logout.php
declare(strict_types=1);
require_once __DIR__ . '/../lib/auth.php';
header('Content-Type: application/json; charset=utf-8');
logout_user();
echo json_encode(['ok' => true]);
