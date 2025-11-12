<!DOCTYPE html>
<html lang="ja"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="/assets/style.css">
</head><body class="bg-gray-100 text-gray-900">
<?php require __DIR__ . "/includes/admin_header.php"; ?>
<main class="container mx-auto px-4 py-6"><h1 class="text-xl font-bold mb-4">管理者ログイン</h1>
<form method="post" class="max-w-sm space-y-3">
  <label class="block"><span class="text-sm">ID</span>
    <input class="mt-1 border rounded px-3 py-2 w-full" name="login" required></label>
  <label class="block"><span class="text-sm">PW</span>
    <input class="mt-1 border rounded px-3 py-2 w-full" type="password" name="password" required></label>
  <button class="px-4 py-2 rounded bg-blue-600 text-white">ログイン</button>
</form>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($_POST['login']==='admin' && $_POST['password']==='admin') {
    $_SESSION['admin']=true; header('Location: /admin/admin_dashboard.php'); exit;
  } else { echo "<p class='text-red-600 mt-3'>失敗しました。</p>"; }
}
?>
</main>
<?php require __DIR__ . "/includes/admin_footer.php"; ?>
</body></html>
