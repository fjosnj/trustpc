<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>会員登録 | trustPC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/style.css">
  <style>html{scroll-behavior:smooth}</style>
</head>
<body class="bg-gray-50 text-gray-900">
<?php require __DIR__ . "/includes/header.php"; ?>
<main class="container mx-auto px-4 py-6">
<h1 class="text-xl font-bold mb-4">会員登録</h1>
<form method="post" class="max-w-sm space-y-3">
  <label class="block"><span class="text-sm">名前</span>
    <input class="mt-1 border rounded px-3 py-2 w-full" name="name" required></label>
  <label class="block"><span class="text-sm">住所</span>
    <input class="mt-1 border rounded px-3 py-2 w-full" name="address"></label>
  <label class="block"><span class="text-sm">ログインID</span>
    <input class="mt-1 border rounded px-3 py-2 w-full" name="login" required></label>
  <label class="block"><span class="text-sm">パスワード</span>
    <input class="mt-1 border rounded px-3 py-2 w-full" type="password" name="password" required></label>
  <button class="px-4 py-2 rounded bg-blue-600 text-white">登録</button>
</form>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require __DIR__ . "/includes/db_connect.php";
  $stmt = $pdo->prepare("SELECT 1 FROM customer WHERE login = ?");
  $stmt->execute([$_POST['login']]);
  if ($stmt->fetch()) {
    echo "<p class='text-red-600 mt-3'>すでに使用されています。</p>";
  } else {
    $stmt = $pdo->prepare("INSERT INTO customer(name,address,login,password) VALUES (?,?,?,?)");
    $stmt->execute([$_POST['name'], $_POST['address'], $_POST['login'], password_hash($_POST['password'], PASSWORD_DEFAULT)]);
    echo "<p class='text-green-700 mt-3'>登録しました。ログインしてください。</p>";
  }
}
?>

</main>
<?php require __DIR__ . "/includes/footer.php"; ?>
<script src="/assets/script.js"></script>
</body>
</html>
