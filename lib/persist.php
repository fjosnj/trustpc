<?php
// /2025/TrustPC/lib/persist.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db_connect.php';

/** ログイン中ユーザーIDを取得（未ログインはnull） */
function current_user_id(){
  return isset($_SESSION['customer']['id']) ? (int)$_SESSION['customer']['id'] : null;
}

/** 汎用：UPSERT */
function upsert_json($table, $col, $userId, $data){
  global $pdo;
  $sql = "INSERT INTO {$table} (user_id, {$col}) VALUES (?, ?)
          ON DUPLICATE KEY UPDATE {$col}=VALUES({$col})";
  $stmt = $pdo->prepare($sql);
  return $stmt->execute([$userId, json_encode($data, JSON_UNESCAPED_UNICODE)]);
}

/** カート */
function save_cart_for_user($userId, $cart){
  return upsert_json('user_cart', 'payload', $userId, array_values($cart ?: []));
}
function load_cart_for_user($userId){
  global $pdo;
  $stmt = $pdo->prepare("SELECT payload FROM user_cart WHERE user_id=?");
  $stmt->execute([$userId]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row ? (json_decode($row['payload'], true) ?: []) : [];
}

/** お気に入り */
function save_favorites_for_user($userId, $slugs){
  // 重複除去＆整形
  $uniq = array_values(array_unique(array_filter(array_map('strval', $slugs ?: []))));
  return upsert_json('user_favorites', 'slugs', $userId, $uniq);
}
function load_favorites_for_user($userId){
  global $pdo;
  $stmt = $pdo->prepare("SELECT slugs FROM user_favorites WHERE user_id=?");
  $stmt->execute([$userId]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row ? (json_decode($row['slugs'], true) ?: []) : [];
}

/** 比較 */
function save_compare_for_user($userId, $slots){
  // slotsは長さ3・slug or null
  $norm = [ $slots[0] ?? null, $slots[1] ?? null, $slots[2] ?? null ];
  return upsert_json('user_compare', 'slots', $userId, $norm);
}
function load_compare_for_user($userId){
  global $pdo;
  $stmt = $pdo->prepare("SELECT slots FROM user_compare WHERE user_id=?");
  $stmt->execute([$userId]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $slots = $row ? (json_decode($row['slots'], true) ?: [null,null,null]) : [null,null,null];
  // 正規化
  return [ $slots[0] ?? null, $slots[1] ?? null, $slots[2] ?? null ];
}
