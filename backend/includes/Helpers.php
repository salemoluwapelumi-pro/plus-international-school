<?php
declare(strict_types=1);

/** Escapes a value for safe HTML output. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money($amount): string
{
    return '₦' . number_format((float) $amount, 2);
}

function pretty_date(?string $value, string $format = 'd M Y'): string
{
    return $value ? date($format, strtotime($value)) : '—';
}

/** Sends a JSON response and stops execution. */
function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

/** Decodes a JSON request body, falling back to form-encoded input. */
function request_input(): array
{
    $raw = file_get_contents('php://input');
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }
    return array_merge($_GET, $_POST);
}

function current_session_name(): string
{
    $row = Database::one('SELECT name FROM academic_sessions WHERE is_current = 1 LIMIT 1');
    return $row['name'] ?? (date('Y') . '/' . (date('Y') + 1));
}

function current_term(): string
{
    $row = Database::one('SELECT term FROM academic_sessions WHERE is_current = 1 LIMIT 1');
    return $row['term'] ?? 'First';
}

function classes_list(): array
{
    return Database::all('SELECT * FROM school_classes ORDER BY level_order, name');
}

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
}

function take_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function redirect(string $path): void
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}

/** Stores an uploaded file and returns its web path, or null. */
function store_upload(string $field, string $folder): ?string
{
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $dir = BASE_PATH . '/assets/uploads/' . $folder;
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx'];
    if (!in_array($ext, $allowed, true)) {
        return null;
    }
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    move_uploaded_file($_FILES[$field]['tmp_name'], $dir . '/' . $name);
    return 'assets/uploads/' . $folder . '/' . $name;
}
