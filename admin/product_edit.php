<!DOCTYPE html>
<html lang="ja"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Product Edit</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="/assets/style.css">
</head><body class="bg-gray-100 text-gray-900">
<?php require __DIR__ . "/includes/admin_header.php"; ?>
<main class="container mx-auto px-4 py-6"><h1 class="text-xl font-bold mb-4">商品編集</h1>
<form method="post" action="/admin/product_insert.php" class="max-w-2xl space-y-3">
  <label class="block"><span class="text-sm">ID</span>
    <input class="mt-1 border rounded px-3 py-2 w-full" name="id" required></label>
  <label class="block"><span class="text-sm">商品名</span>
    <input class="mt-1 border rounded px-3 py-2 w-full" name="name" required></label>
  <label class="block"><span class="text-sm">価格(円)</span>
    <input class="mt-1 border rounded px-3 py-2 w-full" type="number" name="price" required></label>
  <label class="block"><span class="text-sm">仕様(JSON)</span>
    <textarea class="mt-1 border rounded px-3 py-2 w-full" rows="6" name="spec">{ "CPU": "Ryzen 7 5700X", "GPU": "RTX 4060", "RAM": "16GB", "Storage": "1TB SSD" }</textarea></label>
  <button class="px-4 py-2 rounded bg-blue-600 text-white">保存</button>
</form>
</main>
<?php require __DIR__ . "/includes/admin_footer.php"; ?>
</body></html>
