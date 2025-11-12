<?php
// /2025/trustpc/favorite_remove.php
if (session_status() === PHP_SESSION_NONE) session_start();

// 入力
$slug = (string)($_POST['slug'] ?? $_GET['slug'] ?? '');

// セッション初期化
if (!isset($_SESSION['favorites']) || !is_array($_SESSION['favorites'])) {
  $_SESSION['favorites'] = [];
}

// 削除実行
if ($slug !== '') {
  $_SESSION['favorites'] = array_values(array_filter(
    $_SESSION['favorites'],
    fn($s) => (string)$s !== $slug
  ));
}

// ★ 永続化（ログイン時のみ）
require_once __DIR__ . '/lib/persist.php';
if ($uid = current_user_id()) {
  save_favorites_for_user($uid, $_SESSION['favorites']);
}

// JSONで返す（非遷移）
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
  'ok'    => true,
  'count' => count($_SESSION['favorites']),
  'slugs' => array_values($_SESSION['favorites']),
]); exit;
