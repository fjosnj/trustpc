<?php
// /2025/trustpc/checkout.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/lib/app.php'; // h(), yen(), list_products(), $OPT_RAM, $OPT_SSD

// ---- カート読み込み & 料金計算（cart.phpと同ロジック） ----
$cart = $_SESSION['cart'] ?? [];
if (!is_array($cart)) $cart = [];

$all = list_products();
function find_by_slug_local($slug, $all){
  foreach ($all as $p) if (!empty($p['slug']) && $p['slug'] === $slug) return $p;
  return null;
}
function opt_delta($list, $val){
  if (!is_array($list)) return 0;
  foreach ($list as $o) if ((string)($o['val'] ?? '') === (string)$val) return (int)($o['delta'] ?? 0);
  return 0;
}
function opt_label($k, $v){
  if ($v === '' || $v === null) return null;
  $labels = [
    'ram' => ['16'=>'16GB','32'=>'32GB','64'=>'64GB'],
    'ssd' => ['512'=>'SSD 512GB','1tb'=>'SSD 1TB','2tb'=>'SSD 2TB'],
  ];
  return $labels[$k][$v] ?? null;
}
global $OPT_RAM, $OPT_SSD;

$items = [];
$total = 0;
foreach ($cart as $line) {
  $slug = (string)($line['slug'] ?? '');
  if ($slug === '') continue;

  $p = find_by_slug_local($slug, $all);
  if (!$p) continue;

  $base = (int)($p['price'] ?? 0);
  $qty  = max(1, (int)($line['qty'] ?? 1));
  $ram  = (string)($line['ram'] ?? '');
  $ssd  = (string)($line['ssd'] ?? '');

  $unit = $base + opt_delta($OPT_RAM, $ram) + opt_delta($OPT_SSD, $ssd);
  $sub  = $unit * $qty;
  $total += $sub;

  $optTexts = [];
  foreach (['ram','ssd'] as $k) {
    $label = opt_label($k, ($k==='ram' ? $ram : $ssd));
    if ($label) $optTexts[] = $label;
  }

  $items[] = [
    'name' => $p['name'] ?? $slug,
    'qty'  => $qty,
    'sub'  => $sub,
    'opts' => implode(' / ', $optTexts),
  ];
}

// 都道府県
$PREFS = ['北海道','青森県','岩手県','宮城県','秋田県','山形県','福島県','茨城県','栃木県','群馬県','埼玉県','千葉県','東京都','神奈川県','新潟県','富山県','石川県','福井県','山梨県','長野県','岐阜県','静岡県','愛知県','三重県','滋賀県','京都府','大阪府','兵庫県','奈良県','和歌山県','鳥取県','島根県','岡山県','広島県','山口県','徳島県','香川県','愛媛県','高知県','福岡県','佐賀県','長崎県','熊本県','大分県','宮崎県','鹿児島県','沖縄県'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ご購入手続き | trustPC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
  <style>html{scroll-behavior:smooth}</style>
</head>
<body class="bg-gray-50 text-gray-900 has-sticky-offset">
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="max-w-7xl mx-auto px-4 py-6">
  <h1 class="text-2xl font-bold mb-6">ご購入手続き</h1>

  <?php if (!$items): ?>
    <div class="rounded-2xl border bg-white p-6">
      <p>カートが空です。まずは商品をお選びください。</p>
      <a class="mt-4 inline-flex px-4 py-2 rounded bg-blue-600 text-white" href="index.php">トップへ戻る</a>
    </div>
  <?php else: ?>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- 左：入力フォーム -->
    <section class="lg:col-span-2 rounded-2xl border bg-white p-6">
      <form method="post" action="order_confirm.php" class="space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <label class="block">
            <span class="text-sm">氏名</span>
            <input name="name" class="mt-1 w-full border rounded-lg p-2" placeholder="山田 太郎" required>
          </label>
          <label class="block">
            <span class="text-sm">メール</span>
            <input name="email" type="email" class="mt-1 w-full border rounded-lg p-2" placeholder="taro@example.com" required>
          </label>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <label class="block">
            <span class="text-sm">郵便番号</span>
            <input name="zip" class="mt-1 w-full border rounded-lg p-2" placeholder="100-0001" required>
          </label>
          <label class="block">
            <span class="text-sm">都道府県</span>
            <select name="pref" class="mt-1 w-full border rounded-lg p-2" required>
              <?php foreach($PREFS as $p): ?>
                <option value="<?= h($p) ?>" <?= $p==='東京都'?'selected':'' ?>><?= h($p) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="block">
            <span class="text-sm">市区町村</span>
            <input name="city" class="mt-1 w-full border rounded-lg p-2" placeholder="千代田区" required>
          </label>
        </div>

        <label class="block">
          <span class="text-sm">丁目・番地・建物</span>
          <input name="addr" class="mt-1 w-full border rounded-lg p-2" placeholder="1-1-1 ○○ビル3F" required>
        </label>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <label class="block">
            <span class="text-sm">支払方法</span>
            <select name="pay" class="mt-1 w-full border rounded-lg p-2">
              <option value="card" selected>クレジットカード（ダミー）</option>
              <option value="cod">代引き（ダミー）</option>
            </select>
          </label>
          <label class="block">
            <span class="text-sm">ご要望（任意）</span>
            <input name="note" class="mt-1 w-full border rounded-lg p-2" placeholder="到着日・時間帯など">
          </label>
        </div>

        <div class="pt-2">
          <button class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
            確認して購入
          </button>
        </div>
      </form>
    </section>

    <!-- 右：注文要約 -->
    <aside class="rounded-2xl border bg-white p-6">
      <h2 class="text-lg font-semibold mb-3">ご注文内容（要約）</h2>
      <div class="space-y-2">
        <?php foreach ($items as $i): ?>
          <div class="flex items-start justify-between text-sm">
            <div>
              <div class="font-medium"><?= h($i['name']) ?> ×<?= (int)$i['qty'] ?></div>
              <?php if ($i['opts']): ?>
                <div class="text-gray-600"><?= h($i['opts']) ?></div>
              <?php endif; ?>
            </div>
            <div class="font-medium"><?= yen($i['sub']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="mt-4 flex items-center justify-between text-base font-bold">
        <span>合計</span>
        <span><?= yen($total) ?></span>
      </div>
    </aside>
  </div>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
