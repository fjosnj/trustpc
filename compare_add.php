<?php
// /2025/trustpc/compare_add.php
if (session_status() === PHP_SESSION_NONE) session_start();

$isAjax = (isset($_GET['ajax']) && $_GET['ajax'] === '1')
       || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

$slug = (string)($_POST['slug'] ?? $_GET['slug'] ?? '');

// slugなし → エラー or 比較画面へ
if ($slug === '') {
  if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok'=>false,'message'=>'slug is empty']); exit;
  }
  header('Location: compare.php'); exit;
}

// セッション配列を準備（3スロット: 0,1,2）
if (empty($_SESSION['compare']) || !is_array($_SESSION['compare'])) {
  $_SESSION['compare'] = [null, null, null];
} else {
  // 足りない分をnullで埋める（安全側）
  for ($i=count($_SESSION['compare']); $i<3; $i++) $_SESSION['compare'][$i] = null;
}

// すでに入っていれば何もしない
if (in_array($slug, $_SESSION['compare'], true)) {
  // ★ 永続化（ログイン時のみ）
  require_once __DIR__ . '/lib/persist.php';
  if ($uid = current_user_id()) save_compare_for_user($uid, $_SESSION['compare']);

  if ($isAjax) { header('Content-Type: application/json; charset=utf-8'); echo json_encode(['ok'=>true,'slots'=>$_SESSION['compare']]); exit; }
  header('Location: compare.php'); exit;
}

// 空きスロットに順番に入れる。満杯ならFIFO
$placed = false;
for ($i=0; $i<3; $i++) {
  if (empty($_SESSION['compare'][$i])) {
    $_SESSION['compare'][$i] = $slug;
    $placed = true; break;
  }
}
if (!$placed) {
  $_SESSION['compare'] = [ $_SESSION['compare'][1], $_SESSION['compare'][2], $slug ];
}

// ★ 永続化（ログイン時のみ）
require_once __DIR__ . '/lib/persist.php';
if ($uid = current_user_id()) {
  save_compare_for_user($uid, $_SESSION['compare']);
}

if ($isAjax) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok'=>true,'slots'=>array_values($_SESSION['compare'])]); exit;
}
header('Location: compare.php'); exit;
