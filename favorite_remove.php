<?php
// /2025/trustpc/favorite_remove.php
if (session_status() === PHP_SESSION_NONE) session_start();

/* 余計な出力で JSON が壊れないように */
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', '0');           // 画面出力の警告を抑止
ob_start();                                // もし何か出ても最後に捨てる

try {
  // POST専用（GETで来たらエラー）
  if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false, 'message'=>'Method Not Allowed']);
    exit;
  }

  $slug = trim((string)($_POST['slug'] ?? ''));
  if ($slug === '') {
    http_response_code(400);
    echo json_encode(['ok'=>false, 'message'=>'slugが空です']);
    exit;
  }

  // セッション初期化
  if (!isset($_SESSION['favorites']) || !is_array($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
  }

  // 文字列以外が混ざっていても安全に除去
  $clean = [];
  foreach ($_SESSION['favorites'] as $v) {
    if (is_array($v) && isset($v['slug'])) $v = (string)$v['slug'];
    if (is_scalar($v)) {
      $s = (string)$v;
      if ($s !== '' && $s !== $slug) $clean[] = $s;
    }
  }
  $_SESSION['favorites'] = array_values(array_unique($clean));

  // 併せて詳細情報も削除
  if (isset($_SESSION['favorites_detail'][$slug])) {
    unset($_SESSION['favorites_detail'][$slug]);
  }

  $count = count($_SESSION['favorites']);

  // ここまでで出たかもしれないゴミ出力は捨てる
  ob_end_clean();
  echo json_encode(['ok'=>true, 'count'=>$count]);
} catch (Throwable $e) {
  // 例外時も JSON を返す
  ob_end_clean();
  http_response_code(500);
  echo json_encode(['ok'=>false, 'message'=>'server error']);
}
