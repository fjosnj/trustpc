<!DOCTYPE html>
<html lang="ja"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Product Manage</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="/assets/style.css">
</head><body class="bg-gray-100 text-gray-900">
<?php require __DIR__ . "/includes/admin_header.php"; ?>
<main class="container mx-auto px-4 py-6"><h1 class="text-xl font-bold mb-4">商品管理</h1>
<p><a class="underline" href="/admin/product_edit.php">新規作成</a></p>
<table class="w-full mt-3 text-sm">
  <thead><tr class="text-left border-b"><th>ID</th><th>商品名</th><th>価格</th><th></th></tr></thead>
  <tbody>
    <tr class="border-b"><td>TP-G-LITE</td><td>LITE</td><td>129800</td><td><a class="underline" href="/admin/product_edit.php?id=TP-G-LITE">編集</a></td></tr>
  </tbody>
</table>
</main>
<?php require __DIR__ . "/includes/admin_footer.php"; ?>
</body></html>
