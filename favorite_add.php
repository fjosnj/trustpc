<?php
// /2025/trustpc/favorite_add.php
if (session_status() === PHP_SESSION_NONE) session_start();

$isAjax = (isset($_GET['ajax']) && $_GET['ajax'] === '1')
       || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

$slug = (string)($_POST['slug'] ?? $_GET['slug'] ?? '');
if ($slug === '') {
  if ($isAjax) { header('Content-Type: application/json; charset=utf-8'); echo json_encode(['ok'=>false,'message'=>'slug is empty']); exit; }
  header('Location: favorites.php'); exit;
}

// セッション初期化
if (empty($_SESSION['favorites']) || !is_array($_SESSION['favorites'])) {
  $_SESSION['favorites'] = [];
}

// 重複なしで追加（上限は任意：50）
if (!in_array($slug, $_SESSION['favorites'], true)) {
  $_SESSION['favorites'][] = $slug;
  if (count($_SESSION['favorites']) > 50) {
    $_SESSION['favorites'] = array_slice($_SESSION['favorites'], -50);
  }
}

// ★ 永続化（ログイン時のみ）
require_once __DIR__ . '/lib/persist.php';
if ($uid = current_user_id()) {
  save_favorites_for_user($uid, $_SESSION['favorites']);
}

if ($isAjax) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok'=>true, 'count'=>count($_SESSION['favorites']), 'slugs'=>array_values($_SESSION['favorites'])]); exit;
}
header('Location: favorites.php'); exit;
