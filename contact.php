<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>お問い合わせ | trustPC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/style.css">
  <style>html{scroll-behavior:smooth}</style>
</head>
<body class="bg-gray-50 text-gray-900">
<?php require __DIR__ . "/includes/header.php"; ?>
<main class="container mx-auto px-4 py-6">
<h1 class="text-xl font-bold mb-4">お問い合わせ</h1>
<form class="max-w-lg space-y-3">
  <label class="block"><span class="text-sm">お名前</span>
    <input class="mt-1 border rounded px-3 py-2 w-full"></label>
  <label class="block"><span class="text-sm">メール</span>
    <input class="mt-1 border rounded px-3 py-2 w-full" type="email"></label>
  <label class="block"><span class="text-sm">内容</span>
    <textarea class="mt-1 border rounded px-3 py-2 w-full" rows="5"></textarea></label>
  <button class="px-4 py-2 rounded bg-blue-600 text-white" type="button">送信（ダミー）</button>
</form>

</main>
<?php require __DIR__ . "/includes/footer.php"; ?>
<script src="/assets/script.js"></script>
</body>
</html>
