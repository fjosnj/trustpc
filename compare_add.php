<?php
// /2025/trustpc/compare_add.php
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

// 比較スロット（0..2）
if (!isset($_SESSION['compare']) || !is_array($_SESSION['compare'])) {
  $_SESSION['compare'] = [null, null, null];
}

// 既に入っていれば何もしない（同一slugを重複させない）
$exists = false;
foreach ($_SESSION['compare'] as $s) if ($s === $slug) { $exists = true; break; }

if (!$exists) {
  // 空きスロット探す
  $placed = false;
  foreach ([0,1,2] as $i) {
    if (empty($_SESSION['compare'][$i])) {
      $_SESSION['compare'][$i] = $slug;
      $placed = true;
      break;
    }
  }
  // すべて埋まってたら先頭に詰め替え（FIFO）
  if (!$placed) {
    $_SESSION['compare'] = [$slug, $_SESSION['compare'][0], $_SESSION['compare'][1]];
  }
}

// ★ オプション・現在価格を上書き保存（表示用）
if (!isset($_SESSION['compare_detail'])) $_SESSION['compare_detail'] = [];
$_SESSION['compare_detail'][$slug] = [
  'ram'   => $ram,
  'ssd'   => $ssd,
  'qty'   => max(1,$qty),
  'price' => max(0,$price),
];

echo json_encode(['ok'=>true]);
