<?php
require_once __DIR__.'/lib/app.php'; 
$title = 'CPUから選ぶ';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?=h($title)?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/style.css" />
  <style>html{scroll-behavior:smooth}</style>
</head>
<body class="bg-gray-50 text-gray-900 has-sticky-offset">

<?php require __DIR__.'/includes/header.php'; ?>

<main class="max-w-7xl mx-auto px-4 py-8">
  <?php
    $q = $_GET['q'] ?? '';
    $list = list_products(['cpu'=>$q]);
  ?>
  <h1 class="text-2xl font-semibold mb-4">CPUから選ぶ</h1>

  <form class="mb-4" method="get" action="">
    <input class="border rounded-lg p-2 w-64" name="q" placeholder="例: Ryzen 7 / Core i7" value="<?=h($q)?>">
    <button class="ml-2 px-3 py-2 rounded-lg bg-blue-600 text-white">検索</button>
  </form>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach($list as $p): ?>
      <article class="rounded-2xl border bg-white p-4">
        <div class="badge mb-2"><?=h($p['cpu'])?></div>
        <h3 class="font-semibold"><?=h($p['name'])?></h3>
        <p class="text-sm text-gray-600"><?=h($p['gpu'])?> / <?=h($p['ram'])?>GB</p>
        <div class="mt-2 flex items-center justify-between">
          <div class="font-semibold"><?=yen($p['price'])?></div>
          <a class="text-blue-600 underline" href="product_detail.php?s=<?= h($p['slug']) ?>">詳細</a>
        </div>
      </article>
    <?php endforeach; ?>
    <?php if(!$list): ?>
      <p class="text-gray-600">該当する商品がありません。</p>
    <?php endif; ?>
  </div>
</main>

<?php include __DIR__.'/includes/footer.php'; ?>
<script src="assets/site.usermenu.js"></script>
</body>
</html>
