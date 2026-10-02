<?php
require __DIR__ . '/common.php';
require_valid_post();
$id = trim((string)($_POST['session_id'] ?? ''));
$d = [
  'grade' => trim((string)($_POST['grade'] ?? '')),
  'class_name' => trim((string)($_POST['class_name'] ?? '')),
  'day' => trim((string)($_POST['day'] ?? '')),
];
validate_session(array_merge($d, ['subject' => 'placeholder', 'start_time' => '8:00 AM', 'end_time' => '9:00 AM', 'type' => 'class']));
if ($id === '') { http_response_code(400); exit('Missing session ID.'); }
$store = demo_store_read();
$store['added'] = array_values(array_filter($store['added'], fn($item) => (string)($item['id'] ?? '') !== $id));
unset($store['overrides'][$id]);
if (!str_starts_with($id, 'demo-') && !in_array($id, array_map('strval', $store['deleted']), true)) $store['deleted'][] = $id;
demo_store_write($store);
redirect_to_schedule($d);
