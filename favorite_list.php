<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>お気に入り | trustPC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/style.css">
  <style>html{scroll-behavior:smooth}</style>
</head>
<body class="bg-gray-50 text-gray-900">
<?php require __DIR__ . "/includes/header.php"; ?>
<main class="container mx-auto px-4 py-6">
<h1 class="text-xl font-bold mb-4">お気に入り</h1>
<?php
$fav = $_SESSION['fav'] ?? [];
if (!$fav) { echo "<p>お気に入りは空です。</p>"; return; }
require __DIR__ . "/includes/db_connect.php";
$in = implode(',', array_fill(0, count($fav), '?'));
$stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($in)");
$stmt->execute($fav);
$items = $stmt->fetchAll();
?>
<ul class="space-y-2">
<?php foreach ($items as $i): ?>
  <li class="bg-white p-4 rounded-lg shadow flex justify-between">
    <span><?=h($i['name'])?></span>
    <span class="font-bold">¥<?=number_format($i['price'])?></span>
  </li>
<?php endforeach; ?>
</ul>

</main>
<?php require __DIR__ . "/includes/footer.php"; ?>
<script src="/assets/script.js"></script>
</body>
</html>
