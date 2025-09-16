<?php
// lib/auth.php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

function start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
        ]);
    }
}

function require_login(): int {
    start_session();
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    return (int)$_SESSION['user_id'];
}

function current_user_id(): ?int {
    start_session();
    return $_SESSION['user_id'] ?? null;
}

function login_user(int $userId): void {
    start_session();
    $_SESSION['user_id'] = $userId;
}

function logout_user(): void {
    start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
