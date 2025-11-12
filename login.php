<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/* 基準URL（/2025/TrustPC/ を自動判定） */
$BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); // 例: /2025/TrustPC
if ($BASE === '') $BASE = '/';

/* POST処理は出力前に！ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require __DIR__ . '/includes/db_connect.php';

  $stmt = $pdo->prepare("SELECT id, name, login, password FROM customer WHERE login = ?");
  $stmt->execute([$_POST['login'] ?? '']);
  $u = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
    // セッション再生成（セッション固定攻撃対策）
    session_regenerate_id(true);

    // ログイン情報をセッションへ
    $_SESSION['customer'] = [
      'id'    => $u['id'],
      'name'  => $u['name'],
      'login' => $u['login']
    ];

    // ★ ここでユーザーごとの永続データをDB→セッションへ復元
    //    lib/persist.php（current_user_id/ load_* 系）を使います
    require_once __DIR__ . '/lib/persist.php';

    // ゲスト時カートは上書きでOK（明示的に消すなら下行のコメントを外す）
    // unset($_SESSION['cart']);
    $_SESSION['cart']       = load_cart_for_user($u['id']);
    $_SESSION['favorites']  = load_favorites_for_user($u['id']);
    $_SESSION['compare']    = load_compare_for_user($u['id']);

    // ルータに寄せてマイページへ（?p=mypage）
    header('Location: ' . $BASE . '/index.php?p=mypage');
    exit;
  } else {
    $error = 'ログインに失敗しました。';
  }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ログイン | trustPC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- 先頭スラッシュを外す（= 相対/基準パスで読む）-->
  <link rel="stylesheet" href="<?= htmlspecialchars($BASE) ?>/assets/style.css">
  <style>html{scroll-behavior:smooth}</style>
</head>
<body class="bg-gray-50 text-gray-900">
<?php require __DIR__ . '/includes/header.php'; ?>

<main class="container mx-auto px-4 py-6">
  <h1 class="text-xl font-bold mb-4">ログイン</h1>

  <?php if (!empty($error)): ?>
    <p class="text-red-600 mb-3"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <!-- action は自分自身へ（絶対パス禁止） -->
  <form method="post" action="" class="max-w-sm space-y-3">
    <label class="block">
      <span class="text-sm">ログインID</span>
      <input class="mt-1 border rounded px-3 py-2 w-full" name="login" required>
    </label>
    <label class="block">
      <span class="text-sm">パスワード</span>
      <input class="mt-1 border rounded px-3 py-2 w-full" type="password" name="password" required>
    </label>
    <button class="px-4 py-2 rounded bg-blue-600 text-white">ログイン</button>
  </form>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
<script src="<?= htmlspecialchars($BASE) ?>/assets/script.js"></script>
</body>
</html>
