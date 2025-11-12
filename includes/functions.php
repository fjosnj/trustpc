<?php
// セッション開始（出力前）
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * ログイン必須：未ログインならログイン画面へ
 */
function requireLogin(): void {
  if (empty($_SESSION['customer'])) {
    header('Location: login.php');
    exit;
  }
}
