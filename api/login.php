<?php
// api/login.php
declare(strict_types=1);
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/auth.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$email = trim((string)($input['email'] ?? ''));
$haslo = (string)($input['haslo'] ?? '');

if ($email === '' || $haslo === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Brak emaila lub hasła']);
    exit;
}

$stmt = db()->prepare('SELECT id, haslo FROM uzytkownicy WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(401);
    echo json_encode(['error' => 'Nieprawidłowe dane logowania']);
    exit;
}

$hash = (string)($row['haslo'] ?? '');

// Wspieramy dwa tryby: hasło hashowane (password_hash) LUB czysty tekst (dev/demo).
// Dla produkcji usuń porównanie czystego tekstu.
$ok = false;
if (password_verify($haslo, $hash)) {
    $ok = true;
} else {
    // bezpieczne porównanie; jeśli długość inna, hash_equals zwróci false
    $ok = hash_equals($hash, $haslo);
}

if (!$ok) {
    http_response_code(401);
    echo json_encode(['error' => 'Nieprawidłowe dane logowania']);
    exit;
}

login_user((int)$row['id']);
echo json_encode(['ok' => true]);
