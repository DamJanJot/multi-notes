<?php
// api/documents.php
declare(strict_types=1);
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/auth.php';

header('Content-Type: application/json; charset=utf-8');

$uid = require_login();
$method = $_SERVER['REQUEST_METHOD'];

// Pomocnicze
function sanitize_text(?string $s): string {
    // przechowujemy czysty tekst (nie HTML). Front sam zamieni \n na <br>
    return $s === null ? '' : str_replace(["\r\n", "\r"], "\n", $s);
}

switch ($method) {
    case 'GET':
        // /api/documents.php          -> lista
        // /api/documents.php?id=123   -> jeden dokument
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

        if ($id) {
            $stmt = db()->prepare('SELECT id, tytul, tresc, updated_at, created_at FROM dokumenty WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $uid]);
            $doc = $stmt->fetch();
            if (!$doc) {
                http_response_code(404);
                echo json_encode(['error' => 'Nie znaleziono']);
                exit;
            }
            echo json_encode(['doc' => $doc]);
            exit;
        } else {
            $q = trim((string)($_GET['q'] ?? ''));
            if ($q !== '') {
                $stmt = db()->prepare('SELECT id, tytul, updated_at FROM dokumenty WHERE user_id = ? AND (tytul LIKE ? OR tresc LIKE ?) ORDER BY updated_at DESC');
                $like = '%' . $q . '%';
                $stmt->execute([$uid, $like, $like]);
            } else {
                $stmt = db()->prepare('SELECT id, tytul, updated_at FROM dokumenty WHERE user_id = ? ORDER BY updated_at DESC');
                $stmt->execute([$uid]);
            }
            echo json_encode(['docs' => $stmt->fetchAll()]);
            exit;
        }

    case 'POST':
        // Tworzenie nowego dokumentu
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $tytul = trim((string)($input['tytul'] ?? 'Nowy dokument'));
        $tresc = sanitize_text($input['tresc'] ?? '');

        $stmt = db()->prepare('INSERT INTO dokumenty(user_id, tytul, tresc) VALUES(?, ?, ?)');
        $stmt->execute([$uid, $tytul, $tresc]);
        $id = (int)db()->lastInsertId();

        $stmt = db()->prepare('SELECT id, tytul, tresc, updated_at, created_at FROM dokumenty WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['doc' => $stmt->fetch()]);
        exit;

    case 'PUT':
        // Aktualizacja istniejącego dokumentu (tytuł i/lub treść)
        parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
        $id = isset($qs['id']) ? (int)$qs['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Brak id dokumentu']);
            exit;
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $tytul = isset($input['tytul']) ? trim((string)$input['tytul']) : null;
        $tresc = array_key_exists('tresc', $input) ? sanitize_text($input['tresc']) : null;

        // weryfikacja właściciela
        $chk = db()->prepare('SELECT id FROM dokumenty WHERE id = ? AND user_id = ?');
        $chk->execute([$id, $uid]);
        if (!$chk->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Nie znaleziono']);
            exit;
        }

        if ($tytul !== null && $tresc !== null) {
            $stmt = db()->prepare('UPDATE dokumenty SET tytul = ?, tresc = ? WHERE id = ?');
            $stmt->execute([$tytul, $tresc, $id]);
        } elseif ($tytul !== null) {
            $stmt = db()->prepare('UPDATE dokumenty SET tytul = ? WHERE id = ?');
            $stmt->execute([$tytul, $id]);
        } elseif ($tresc !== null) {
            $stmt = db()->prepare('UPDATE dokumenty SET tresc = ? WHERE id = ?');
            $stmt->execute([$tresc, $id]);
        }

        $stmt = db()->prepare('SELECT id, tytul, tresc, updated_at, created_at FROM dokumenty WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['doc' => $stmt->fetch()]);
        exit;

    case 'DELETE':
        parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
        $id = isset($qs['id']) ? (int)$qs['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Brak id dokumentu']);
            exit;
        }
        $stmt = db()->prepare('DELETE FROM dokumenty WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $uid]);
        echo json_encode(['ok' => true]);
        exit;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Metoda niedozwolona']);
        exit;
}
