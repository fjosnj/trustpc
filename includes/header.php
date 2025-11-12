<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<header class="bg-white border-b sticky top-0 z-30">
  <div class="container mx-auto px-4 py-3 flex items-center gap-4">
    <a href="index.php" class="text-2xl font-bold">trust<span class="text-blue-600">PC</span></a>
    
    <nav class="ml-auto flex items-center gap-4 text-sm">
      <a class="hover:underline" href="advisor.php">診断する</a>
      <a class="hover:underline" href="collection_cpu.php">CPUから選ぶ</a>
      <a class="hover:underline" href="collection_gpu.php">GPUから選ぶ</a>
      <a class="hover:underline" href="collection_price.php">価格帯から選ぶ</a>
      <a class="hover:underline" href="compare.php">比較</a>
      <a class="hover:underline" href="favorite.php">お気に入り</a>
      <a class="hover:underline" href="cart.php">カート</a>
      <?php if (!empty($_SESSION['customer'])): ?>
        <a class="hover:underline" href="mypage.php">マイページ</a>
        <a class="hover:underline" href="logout.php">ログアウト</a>
      <?php else: ?>
        <a class="hover:underline" href="login.php">ログイン</a>
        <a class="hover:underline" href="register.php">会員登録</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
