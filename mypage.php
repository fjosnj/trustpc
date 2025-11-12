<?php
// /2025/trustpc/mypage.php
if (session_status() === PHP_SESSION_NONE) session_start();

// 既存のログイン必須ヘルパーがある前提（なければ簡易版を使う）
if (is_file(__DIR__ . '/includes/functions.php')) {
  require __DIR__ . '/includes/functions.php';
  if (function_exists('requireLogin')) requireLogin();
} else {
  if (empty($_SESSION['customer'])) { header('Location: login.php'); exit; }
}

// 表示用の安全な取り出し
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// アカウント情報（セッション想定）
$cu   = $_SESSION['customer'] ?? [];
$name = $cu['name']  ?? '';
$mail = $cu['email'] ?? ($cu['mail'] ?? '');   // どちらでも対応
$addr = $cu['address'] ?? '';

// 購入履歴（セッション想定: $_SESSION['orders']）
// 例）orders[] = ['date'=>'2025-07-01','items'=>[['name'=>'TrustPC Model 1','qty'=>1,'price'=>250000]], 'total'=>250000]
$orders = [];
if (!empty($_SESSION['orders']) && is_array($_SESSION['orders'])) {
  $orders = $_SESSION['orders'];
}
// 新しい順に並べる（dateがあれば）
usort($orders, function($a,$b){
  $da = $a['date'] ?? '';
  $db = $b['date'] ?? '';
  if ($da == $db) return 0;
  return ($da > $db) ? -1 : 1;
});
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>マイページ | trustPC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/style.css">
  <style>
    .panel{background:#fff;border:1px solid rgba(17,18,20,.1);border-radius:16px}
    .row{display:flex;gap:18px;align-items:flex-start}
    @media (max-width: 1024px){ .row{flex-direction:column} }
  </style>
</head>
<body class="bg-gray-50 text-gray-900">
<?php require __DIR__ . '/includes/header.php'; ?>

<main class="max-w-7xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-semibold mb-6">アカウント情報・購入履歴</h1>

  <div class="row">
    <!-- 左：アカウント情報 -->
    <section class="panel p-5 flex-1 min-w-[320px]">
      <h2 class="text-lg font-semibold mb-4">👤 アカウント情報</h2>

      <dl class="grid grid-cols-[120px_1fr] gap-y-3 text-sm">
        <dt class="text-gray-600">氏名</dt>
        <dd><?= h($name) ?></dd>

        <dt class="text-gray-600">メール</dt>
        <dd><?= h($mail) ?></dd>

        <dt class="text-gray-600">住所</dt>
        <dd><?= h($addr) ?></dd>
      </dl>

      <p class="mt-4 text-xs text-gray-500">※ダミー表示です。実運用ではDBの会員情報を表示してください。</p>
    </section>

    <!-- 右：購入履歴 -->
    <section class="panel p-5 flex-1 min-w-[320px]">
      <h2 class="text-lg font-semibold mb-4">🧾 購入履歴</h2>

      <?php if (!$orders): ?>
        <p class="text-sm text-gray-600">購入履歴はありません。</p>
      <?php else: ?>
        <div class="divide-y">
          <?php foreach ($orders as $od): ?>
            <?php
              $date  = $od['date'] ?? '';
              $items = is_array($od['items'] ?? null) ? $od['items'] : [];
              $total = (int)($od['total'] ?? 0);

              // 表示は1行1注文：最初の商品の名前 + 必要なら「ほかN点」
              $firstName = $items[0]['name'] ?? 'ご注文';
              $more = max(0, count($items) - 1);
              $label = $firstName . ($more>0 ? " ほか{$more}点" : '');
              // 合計が無ければ概算
              if (!$total) {
                foreach ($items as $it) {
                  $qty = (int)($it['qty'] ?? 1);
                  $price = (int)($it['price'] ?? 0);
                  $total += $qty * $price;
                }
              }
            ?>
            <div class="flex items-center justify-between py-3 text-sm">
              <div class="flex items-center gap-4">
                <span class="text-gray-600 w-28"><?= h($date) ?></span>
                <span class="font-medium"><?= h($label) ?></span>
              </div>
              <div class="font-semibold">¥<?= number_format($total) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
<script src="/assets/script.js"></script>
</body>
</html>
