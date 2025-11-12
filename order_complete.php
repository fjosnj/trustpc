<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>注文完了 | trustPC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/style.css">
  <style>html{scroll-behavior:smooth}</style>
</head>
<body class="bg-gray-50 text-gray-900">
<?php require __DIR__ . "/includes/header.php"; ?>
<main class="container mx-auto px-4 py-6">
<h1 class='text-xl font-bold mb-4'>注文完了</h1><p>ご注文ありがとうございました！</p>

</main>
<?php require __DIR__ . "/includes/footer.php"; ?>
<script src="/assets/script.js"></script>
</body>
</html>
