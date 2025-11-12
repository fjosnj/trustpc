<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>商品一覧 | trustPC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/style.css">
  <style>html{scroll-behavior:smooth}</style>
</head>
<body class="bg-gray-50 text-gray-900">
<?php require __DIR__ . "/includes/header.php"; ?>
<main class="container mx-auto px-4 py-6">
<h1 class="text-xl font-bold mb-4">商品一覧</h1>
<?php require __DIR__ . "/includes/db_connect.php"; ?>
<?php
$q = trim($_GET['q'] ?? '');
$sql = "SELECT id, name, price, json_extract(spec, '$.CPU') AS cpu, json_extract(spec, '$.GPU') AS gpu
        FROM products";
$params = [];
if ($q !== '') {
  $sql .= " WHERE name LIKE :q OR json_extract(spec, '$.CPU') LIKE :q OR json_extract(spec, '$.GPU') LIKE :q";
  $params[':q'] = "%{$q}%";
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<div class="grid gap-4 md:grid-cols-3">
<?php foreach ($products as $p): ?>
  <a class="block p-4 bg-white rounded-xl shadow hover:shadow-md transition" href="/product_detail.php?id=<?=h($p['id'])?>">
    <div class="font-semibold"><?=h($p['name'])?></div>
    <div class="text-gray-600 text-sm">CPU: <?=h($p['cpu'])?> / GPU: <?=h($p['gpu'])?></div>
    <div class="mt-2 font-bold">¥<?=number_format($p['price'])?></div>
  </a>
<?php endforeach; ?>
</div>

</main>
<?php require __DIR__ . "/includes/footer.php"; ?>
<script src="/assets/script.js"></script>
</body>
</html>
