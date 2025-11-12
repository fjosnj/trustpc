<?php
// /2025/trustpc/cart_remove.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/lib/app.php'; // yen(), list_products(), $OPT_RAM, $OPT_SSD

header('Content-Type: application/json; charset=utf-8');

$idx = isset($_POST['idx']) ? (int)$_POST['idx'] : -1;

// カート初期化
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// 指定行を削除
if ($idx >= 0 && isset($_SESSION['cart'][$idx])) {
  unset($_SESSION['cart'][$idx]);
  $_SESSION['cart'] = array_values($_SESSION['cart']); // 連番詰め
}

// 合計再計算
$cart = $_SESSION['cart'];

$all = list_products();
function find_by_slug_local($slug, $all){
  foreach ($all as $p) if (!empty($p['slug']) && $p['slug'] === $slug) return $p;
  return null;
}
function opt_delta($list, $val){
  if (!is_array($list)) return 0;
  foreach ($list as $o) if ((string)($o['val'] ?? '') === (string)$val) return (int)($o['delta'] ?? 0);
  return 0;
}
global $OPT_RAM, $OPT_SSD;

$total = 0;
$totalCount = 0; // 数量合計（バッジ用）
foreach ($cart as $line) {
  $p = find_by_slug_local((string)($line['slug'] ?? ''), $all);
  if (!$p) continue;
  $base = (int)($p['price'] ?? 0);
  $qty  = max(1, (int)($line['qty'] ?? 1));
  $ram  = (string)($line['ram'] ?? '');
  $ssd  = (string)($line['ssd'] ?? '');
  $unit = $base + opt_delta($OPT_RAM, $ram) + opt_delta($OPT_SSD, $ssd);
  $total += $unit * $qty;
  $totalCount += $qty;
}

// ★ 永続化（ログイン時のみ）
require_once __DIR__ . '/lib/persist.php';
if ($uid = current_user_id()) {
  save_cart_for_user($uid, $_SESSION['cart']);
}

echo json_encode([
  'ok'         => true,
  'count'      => $totalCount,
  'total'      => $total,
  'total_html' => yen($total),
  'items_left' => count($cart),
]); exit;
