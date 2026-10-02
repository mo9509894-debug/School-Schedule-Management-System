<?php
// Shared helpers for the temporary file-based admin demo.
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: ../index.php?start=adminLogin');
    exit;
}
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

function demo_store_path(): string { return dirname(__DIR__) . '/data/demo_sessions.json'; }
function demo_store_read(): array {
    $default = ['added' => [], 'overrides' => [], 'deleted' => []];
    $path = demo_store_path();
    if (!is_file($path)) return $default;
    $data = json_decode((string)file_get_contents($path), true);
    return is_array($data) ? array_replace($default, $data) : $default;
}
function demo_store_write(array $data): void {
    $path = demo_store_path();
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false || file_put_contents($path, $json, LOCK_EX) === false) {
        http_response_code(500);
        exit('Could not save demo session data. Check write permissions for data/demo_sessions.json.');
    }
}
function require_valid_post(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('POST required'); }
    $posted = (string)($_POST['_csrf'] ?? '');
    if ($posted === '' || !hash_equals((string)($_SESSION['csrf_token'] ?? ''), $posted)) {
        http_response_code(403); exit('Invalid request token. Go back and try again.');
    }
}
function posted_session(): array {
    return [
        'id' => trim((string)($_POST['session_id'] ?? '')),
        'grade' => trim((string)($_POST['grade'] ?? '')),
        'class_name' => trim((string)($_POST['class_name'] ?? '')),
        'day' => trim((string)($_POST['day'] ?? '')),
        'subject' => trim((string)($_POST['subject'] ?? '')),
        'start_time' => trim((string)($_POST['start_time'] ?? '')),
        'end_time' => trim((string)($_POST['end_time'] ?? '')),
        'type' => trim((string)($_POST['type'] ?? 'class')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'teacher' => trim((string)($_POST['teacher'] ?? '')),
    ];
}
function validate_session(array $d): void {
    $grades = ['10', '11', '12']; $classes = ['A', 'B', 'C', 'D'];
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
    if (!in_array($d['grade'], $grades, true) || !in_array($d['class_name'], $classes, true) || !in_array($d['day'], $days, true)) {
        http_response_code(400); exit('Invalid grade, class, or day.');
    }
    if ($d['subject'] === '' || $d['start_time'] === '' || $d['end_time'] === '' || $d['type'] === '') {
        http_response_code(400); exit('Subject, start, end, and type are required.');
    }
}
function redirect_to_schedule(array $d): void {
    $q = http_build_query(['grade' => $d['grade'], 'class' => $d['class_name'], 'day' => $d['day']]);
    header('Location: ../index.php?' . $q); exit;
}
