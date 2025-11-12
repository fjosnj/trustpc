<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php require_once __DIR__.'/lib/app.php'; ?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>trustPC | トップ</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/style.css">
  <style>html{scroll-behavior:smooth}</style>
</head>
<body class="bg-gray-50 text-gray-900">
<?php require __DIR__ . "/includes/header.php"; ?>

<main class="container mx-auto px-4 py-6">
  <h1 class="text-2xl font-bold mb-4">ようこそ、trustPCへ</h1>
  <p class="text-gray-700 mb-6">学生・新社会人向けおすすめゲーミングPCをピックアップ。</p>

  <!-- ★ 1→2→3列のレスポンシブグリッドに変更 -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <a class="block p-4 bg-white rounded-xl shadow hover:shadow-md transition" href="product_detail.php?id=TP-S-VAL">
      <!-- ★ 画像の比率を固定 -->
      <div class="aspect-[4/3] overflow-hidden rounded-lg mb-2">
        <img src="./img/products/valorant-main.png" alt="TP-G-LITEの外観画像" class="w-full h-full object-cover">
      </div>
      <div class="text-sm site-badge inline-block mb-2">trustPC×VALORANTコラボ商品</div>
      <div class="font-semibold">TP-G-LITE</div>
      <div class="text-gray-600 text-sm">RTX 4060 / Ryzen 5</div>
    </a>

    <a class="block p-4 bg-white rounded-xl shadow hover:shadow-md transition" href="product_detail.php?id=TP-S-MC">
      <div class="aspect-[4/3] overflow-hidden rounded-lg mb-2">
        <img src="./img/products/minecraft-main.png" alt="TP-G-STDの外観画像" class="w-full h-full object-cover">
      </div>
      <div class="text-sm site-badge inline-block mb-2">trustPC×minecraftコラボ商品</div>
      <div class="font-semibold">TP-G-STD</div>
      <div class="text-gray-600 text-sm">RTX 4060 / Ryzen 7</div>
    </a>

    <a class="block p-4 bg-white rounded-xl shadow hover:shadow-md transition" href="product_detail.php?id=TP-G-UP">
      <div class="aspect-[4/3] overflow-hidden rounded-lg mb-2">
        <img src="./img/products/ultimate-pro-main.png" alt="TP-G-ADVの外観画像" class="w-full h-full object-cover">
      </div>
      <div class="text-sm site-badge inline-block mb-2">TrustPC Ultimate Pro</div>
      <div class="font-semibold">TP-G-ADV</div>
      <div class="text-gray-600 text-sm">RTX 4090 Core i9</div>
    </a>
  </div>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
<script src="/assets/script.js"></script>
</body>
</html>
