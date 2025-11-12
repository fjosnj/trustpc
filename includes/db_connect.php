<?php
// ==== ロリポップ(MySQL 8.0) 向け PDO 接続 ====
// 必須：あなたのコントロールパネルに出ている実値で更新
$DB_HOST    = 'mysql327.phy.lolipop.lan';   // 例：mysql327.phy.lolipop.lan
$DB_NAME    = 'LAA1683996-trustpc';         // 例：LAA1683996-trustpc
$DB_USER    = 'LAA1683996';                 // 例：LAA1683996
$DB_PASS    = '1219';                       // 例：パスワード
$DB_CHARSET = 'utf8mb4';

// ポイント：'localhost' だとソケットを探して [2002] が出やすいので、必ずホスト名を使う
$dsn = "mysql:host={$DB_HOST};port=3306;dbname={$DB_NAME};charset={$DB_CHARSET}";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
  // 文字化け/Unknown character set 対策：DSNのcharsetは 'utf8mb4' のみ。照合順序はINIT_COMMANDで必要なら指定
  PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
  // もし照合順序も固定したい場合は↓に変更：
  // PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_0900_ai_ci"
];

try {
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
  http_response_code(500);
  echo '<pre>DB接続に失敗しました: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</pre>";
  exit;
}
