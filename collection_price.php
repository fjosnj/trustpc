<?php
// /2025/TrustPC/collection_price.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/lib/app.php'; // ← $PRODUCTS / list_products() / yen() / h()

// GETパラメータ（未指定はnull）
$min = isset($_GET['min']) && $_GET['min'] !== '' ? max(0, (int)$_GET['min']) : null;
$max = isset($_GET['max']) && $_GET['max'] !== '' ? max(0, (int)$_GET['max']) : null;

// lib/app.phpの配列を価格で絞り込み
$filters = [];
if ($min !== null) $filters['min'] = $min;
if ($max !== null) $filters['max'] = $max;
//$list = list_products($filters);
require __DIR__ . '/includes/db_connect.php';
if( $min !== null && $max !== null){
  $stmt = $pdo->prepare("SELECT * FROM products WHERE price BETWEEN ? AND ? AND is_active = 1");
  $stmt->execute([$filters['min'], $filters['max']]);
}else{
  $stmt = $pdo->prepare("SELECT * FROM products WHERE is_active = 1");
  $stmt->execute();
}

$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>価格帯から選ぶ | trustPC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- 先頭に / を付けない（= trustpc直下を基準に相対読み込み）-->
  <link rel="stylesheet" href="assets/style.css"/>
  <style>html{scroll-behavior:smooth}</style>
</head>
<body class="bg-gray-50 text-gray-900 has-sticky-offset">

<?php include __DIR__ . '/includes/header.php'; ?>

<main class="max-w-7xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-semibold mb-4">価格帯から選ぶ</h1>

  <form class="mb-4 flex items-center gap-2" method="get" action="">
    <input class="border rounded-lg p-2 w-32" type="number" name="min" placeholder="最小"
           value="<?= h($min ?? '') ?>" min="0" step="1000">
    <span>〜</span>
    <input class="border rounded-lg p-2 w-32" type="number" name="max" placeholder="最大"
           value="<?= h($max ?? '') ?>" min="0" step="1000">
    <button class="px-3 py-2 rounded-lg bg-blue-600 text-white">絞り込む</button>
    <?php if ($min !== null || $max !== null): ?>
      <a class="px-3 py-2 rounded-lg border bg-white" href="collection_price.php">リセット</a>
    <?php endif; ?>
  </form>

  <?php if (!$list): ?>
    <p class="text-gray-600">該当する商品がありません。</p>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($list as $p): ?>
        <article class="rounded-2xl border bg-white overflow-hidden hover:shadow-md transition">
          <div class="w-full aspect-[4/3] bg-gray-100">
            <img src="<?= h($p['image_url']) ?>" alt="<?= h($p['name']) ?>" class="w-full h-full object-cover">
          </div>
          <div class="p-4">
            <h3 class="font-semibold"><?= h($p['name']) ?></h3>
            <p class="text-sm text-gray-600">
              <?= h($p['gpu']) ?><?= ($p['gpu'] && $p['cpu']) ? ' / ' : '' ?><?= h($p['cpu']) ?>
              <?php if (!empty($p['ram'])): ?> / <?= h($p['ram']) ?>GB<?php endif; ?>
            </p>
            <div class="mt-2 flex items-center justify-between">
              <div class="font-semibold"><?= yen($p['price']) ?></div>
              <!-- 詳細はルータ経由に寄せると安全 -->
              <a class="text-blue-600 underline" href="product_detail.php?s=<?= h($p['slug']) ?>">詳細</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="assets/site.usermenu.js"></script>
</body>
</html>
