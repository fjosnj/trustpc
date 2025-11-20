<?php
// /2025/trustpc/cart_add.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=UTF-8');

$slug  = trim($_POST['slug']  ?? '');
$qty   = (int)($_POST['qty']  ?? 1);
$ram   = trim($_POST['ram']   ?? '');
$ssd   = trim($_POST['ssd']   ?? '');
$price = (int)($_POST['price']?? 0);

if ($slug === '') {
  echo json_encode(['ok'=>false, 'message'=>'slugが空です']); exit;
}
$qty = max(1, $qty);

// カート初期化
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// 既存の同一構成（slug+ram+ssd）があれば数量加算、なければ新規行
$found = false;
foreach ($_SESSION['cart'] as &$line) {
  if (is_array($line)
      && ($line['slug'] ?? '') === $slug
      && (string)($line['ram'] ?? '') === $ram
      && (string)($line['ssd'] ?? '') === $ssd) {
    $line['qty'] = max(1, (int)($line['qty'] ?? 1) + $qty);
    // ★ 現在価格が送られてきたら最新で上書き（同じ構成でも価格改定に備える）
    if ($price > 0) $line['price'] = $price;
    $found = true;
    break;
  }
}
unset($line);

if (!$found) {
  $_SESSION['cart'][] = [
    'slug'  => $slug,
    'qty'   => $qty,
    'ram'   => $ram,
    'ssd'   => $ssd,
    // ★ ここが超重要：現在価格を保存（後で cart.php がこの単価を優先採用）
    'price' => max(0, $price),
  ];
}

echo json_encode([
  'ok'    => true,
  'count' => count($_SESSION['cart'])
]);
