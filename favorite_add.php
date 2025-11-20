<?php
// /2025/trustpc/favorite_add.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=UTF-8');

$slug  = trim($_POST['slug']  ?? '');
$ram   = trim($_POST['ram']   ?? '');
$ssd   = trim($_POST['ssd']   ?? '');
$qty   = (int)($_POST['qty']  ?? 1);
$price = (int)($_POST['price']?? 0);

if ($slug === '') {
  echo json_encode(['ok'=>false, 'message'=>'slugが空です']); exit;
}

// リストは slug の集合として保持
if (!isset($_SESSION['favorites']) || !is_array($_SESSION['favorites'])) {
  $_SESSION['favorites'] = [];
}
if (!in_array($slug, $_SESSION['favorites'], true)) {
  $_SESSION['favorites'][] = $slug;
}

// ★ 表示オーバーレイ用の詳細（可変価格や選択）を別配列に保存
if (!isset($_SESSION['favorites_detail'])) $_SESSION['favorites_detail'] = [];
$_SESSION['favorites_detail'][$slug] = [
  'ram'   => $ram,
  'ssd'   => $ssd,
  'qty'   => max(1,$qty),
  'price' => max(0,$price),
];

echo json_encode([
  'ok'    => true,
  'count' => count($_SESSION['favorites'])
]);
