<?php
// /2025/TrustPC/logout.php
if (session_status() === PHP_SESSION_NONE) session_start();

/* ① 自分のベースURLを自動判定（例: /2025/TrustPC） */
$BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($BASE === '') $BASE = '/';

/* ② セッションを確実に破棄 */
$_SESSION = [];
if (ini_get('session.use_cookies')) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params['path'], $params['domain'],
    $params['secure'], $params['httponly']
  );
}
session_destroy();

/* ③ ルータ方式なら p=home に戻すのもアリ */
$redirect = $BASE . '/index.php';           // 例: /2025/TrustPC/index.php
// $redirect = $BASE . '/index.php?p=home'; // ルーティングしてるならこちらでもOK

header('Location: ' . $redirect);
exit;
