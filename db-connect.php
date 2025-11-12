<?php
    const SERVER = 'mysql327.phy.lolipop.lan';
    const DBNAME = 'LAA1683996-trustpc';
    const USER = 'LAA1683996';
    const PASS = '1219';

    $connect = 'mysql:host='. SERVER . ';dbname='. DBNAME . ';charset=utf8';
?>
<?php
// ロリポの例：あなたの環境値で書き換え可
$DB_HOST = 'mysql327.phy.lolipop.lan';   // ← ロリポの「サーバー」
$DB_NAME = 'LAA1683996-trustpc';         // ← データベース名
$DB_USER = 'LAA1683996';                 // ← ユーザー名
$DB_PASS = '1219';                       // ← パスワード

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";

$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
  // 本番では詳細は出さず、デバッグ時だけ表示
  http_response_code(500);
  exit('DB接続に失敗しました（設定を確認してください）。');
}
