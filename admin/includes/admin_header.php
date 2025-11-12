<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<header class="bg-gray-900 text-white">
  <div class="container mx-auto px-4 py-3 flex items-center justify-between">
    <a href="/admin/admin_dashboard.php" class="font-bold">trustPC Admin</a>
    <nav class="flex gap-4 text-sm">
      <a class="hover:underline" href="/admin/product_manage.php">商品</a>
      <a class="hover:underline" href="/admin/order_manage.php">注文</a>
      <a class="hover:underline" href="/admin/user_manage.php">ユーザー</a>
      <a class="hover:underline" href="/admin/stock_manage.php">在庫</a>
      <a class="hover:underline" href="/admin/report_analysis.php">分析</a>
    </nav>
  </div>
</header>
