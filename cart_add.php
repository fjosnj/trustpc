<?php
// /2025/trustpc/cart_add.php
if (session_status() === PHP_SESSION_NONE) session_start();

$isAjax = (isset($_GET['ajax']) && $_GET['ajax'] === '1')
       || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

$slug = (string)($_POST['slug'] ?? $_GET['slug'] ?? '');
$qty  = (int)($_POST['qty']  ?? $_GET['qty']  ?? 1);
$ram  = (string)($_POST['ram'] ?? '');
$ssd  = (string)($_POST['ssd'] ?? '');

if ($qty < 1) $qty = 1;

if ($slug === '') {
  if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok'=>false,'message'=>'slug is empty']); exit;
  }
  header('Location: cart.php'); exit;
}

// カート配列を初期化
if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// 同一構成（slug+ram+ssd）が既にあれば数量だけ加算
$merged = false;
foreach ($_SESSION['cart'] as &$line) {
  if (($line['slug'] ?? '') === $slug
      && ($line['ram'] ?? '') === $ram
      && ($line['ssd'] ?? '') === $ssd) {
    $line['qty'] = max(1, (int)($line['qty'] ?? 0)) + $qty;
    $merged = true; break;
  }
}
unset($line);

if (!$merged) {
  $_SESSION['cart'][] = [
    'slug' => $slug,
    'qty'  => $qty,
    'ram'  => $ram,
    'ssd'  => $ssd,
  ];
}

// 数量合計（バッジ用）
$totalCount = 0;
foreach ($_SESSION['cart'] as $line) $totalCount += (int)($line['qty'] ?? 0);

// ★ 永続化（ログイン時のみ）
require_once __DIR__ . '/lib/persist.php';
if ($uid = current_user_id()) {
  save_cart_for_user($uid, $_SESSION['cart']);
}

// 応答
if ($isAjax) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok'=>true, 'count'=>$totalCount, 'items'=>$_SESSION['cart']]); exit;
}
header('Location: cart.php'); exit;
