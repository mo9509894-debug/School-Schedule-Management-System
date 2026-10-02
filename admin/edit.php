<?php
require __DIR__ . '/common.php';
require_valid_post();
$d = posted_session();
validate_session($d);
if ($d['id'] === '') { http_response_code(400); exit('Missing session ID.'); }
$store = demo_store_read();
$foundAdded = false;
foreach ($store['added'] as $i => $item) {
    if ((string)($item['id'] ?? '') === $d['id']) { $store['added'][$i] = $d; $foundAdded = true; break; }
}
if (!$foundAdded) $store['overrides'][$d['id']] = $d;
$store['deleted'] = array_values(array_filter($store['deleted'], fn($id) => (string)$id !== $d['id']));
demo_store_write($store);
redirect_to_schedule($d);
