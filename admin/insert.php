<?php
require __DIR__ . '/common.php';
require_valid_post();
$d = posted_session();
validate_session($d);
$store = demo_store_read();
$d['id'] = 'demo-' . bin2hex(random_bytes(6));
$store['added'][] = $d;
$store['deleted'] = array_values(array_filter($store['deleted'], fn($id) => (string)$id !== $d['id']));
demo_store_write($store);
redirect_to_schedule($d);
