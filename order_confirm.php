<?php
// /2025/trustpc/order_confirm.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/lib/app.php'; // h(), yen(), list_products(), $OPT_RAM, $OPT_SSD

// checkout.php からの入力を保存
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_SESSION['checkout'] = [
    'name' => $_POST['name'] ?? '',
    'email'=> $_POST['email'] ?? '',
    'zip'  => $_POST['zip'] ?? '',
    'pref' => $_POST['pref'] ?? '',
    'city' => $_POST['city'] ?? '',
    'addr' => $_POST['addr'] ?? '',
    'pay'  => $_POST['pay'] ?? 'card',
    'note' => $_POST['note'] ?? '',
  ];
}
$addr = $_SESSION['checkout'] ?? null;

// カート読み込み＆計算（cart.phpと同じ方針）
$cart = $_SESSION['cart'] ?? [];
$all  = list_products();

function find_by_slug_local($slug, $all){ foreach ($all as $p) if (!empty($p['slug']) && $p['slug']===$slug) return $p; return null; }
function opt_delta($list, $val){ if(!is_array($list))return 0; foreach($list as $o) if((string)($o['val']??'')===(string)$val) return (int)($o['delta']??0); return 0; }
function opt_label($k,$v){ $m=['ram'=>['16'=>'16GB','32'=>'32GB','64'=>'64GB'], 'ssd'=>['512'=>'SSD 512GB','1tb'=>'SSD 1TB','2tb'=>'SSD 2TB']]; return $m[$k][$v]??null; }
global $OPT_RAM, $OPT_SSD;

$items=[]; $total=0;
foreach ($cart as $line) {
  $p = find_by_slug_local((string)($line['slug']??''), $all);
  if(!$p) continue;

  $qty = max(1,(int)($line['qty']??1));
  $ram = (string)($line['ram']??'');
  $ssd = (string)($line['ssd']??'');

  // ★ 詳細画面の “現在単価” を最優先
  if (isset($line['price']) && (int)$line['price'] > 0) {
    $unit = (int)$line['price'];
  } else {
    // フォールバック：差額から再計算
    $unit = (int)$p['price'] + opt_delta($OPT_RAM,$ram) + opt_delta($OPT_SSD,$ssd);
  }

  $sub   = $unit * $qty; 
  $total += $sub;

  $opts = array_filter([opt_label('ram',$ram), opt_label('ssd',$ssd)]);
  $items[] = [
    'name' => $p['name'],
    'qty'  => $qty,
    'sub'  => $sub,
    'opts' => implode(' / ',$opts),
  ];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ご注文内容の確認 | trustPC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css"><style>html{scroll-behavior:smooth}</style>
</head>
<body class="bg-gray-50 text-gray-900 has-sticky-offset">
<?php include __DIR__.'/includes/header.php'; ?>

<main class="max-w-7xl mx-auto px-4 py-6">
  <h1 class="text-2xl font-bold mb-6">ご注文内容の確認</h1>

  <?php if(!$items): ?>
    <div class="rounded-2xl border bg-white p-6">カートが空です。<a class="text-blue-600 underline" href="index.php">トップへ戻る</a></div>
  <?php else: ?>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- 左：注文内容 -->
    <section class="lg:col-span-2 rounded-2xl border bg-white p-6">
      <h2 class="text-lg font-semibold mb-3">ご注文内容</h2>
      <table class="w-full text-sm border-collapse">
        <thead><tr class="border-b">
          <th class="text-left p-3">商品</th>
          <th class="text-center p-3">数量</th>
          <th class="text-right p-3">価格</th>
        </tr></thead>
        <tbody>
        <?php foreach($items as $i): ?>
          <tr class="border-b">
            <td class="p-3">
              <div class="font-medium"><?= h($i['name']) ?></div>
              <?php if($i['opts']): ?><div class="text-gray-600"><?= h($i['opts']) ?></div><?php endif; ?>
            </td>
            <td class="p-3 text-center"><?= (int)$i['qty'] ?></td>
            <td class="p-3 text-right"><?= yen($i['sub']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr><td class="p-3 font-bold text-right" colspan="2">合計</td><td class="p-3 font-bold text-right"><?= yen($total) ?></td></tr>
        </tfoot>
      </table>

      <form class="mt-5 flex gap-2" method="post" action="complete.php">
        <button class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">購入して完了</button>
        <a class="px-4 py-2 rounded-lg border" href="checkout.php">入力に戻る</a>
      </form>
    </section>

    <!-- 右：お届け情報 -->
    <aside class="rounded-2xl border bg-white p-6">
      <h2 class="text-lg font-semibold mb-3">お届け情報</h2>
      <?php if($addr): ?>
        <div class="space-y-2 text-sm">
          <div><span class="text-gray-600">お届け先</span><div><?= h($addr['name']) ?><br><?= h($addr['pref'].' '.$addr['city'].' '.$addr['addr']) ?></div></div>
          <div><span class="text-gray-600">支払い方法</span><div><?= $addr['pay']==='card'?'クレジットカード（VISA）':'代引き' ?></div></div>
          <div><span class="text-gray-600">連絡先</span><div><?= h($addr['email']) ?></div></div>
        </div>
      <?php else: ?>
        <p class="text-gray-600">住所情報が見つかりません。<a class="text-blue-600 underline" href="checkout.php">入力へ戻る</a></p>
      <?php endif; ?>
    </aside>
  </div>
  <?php endif; ?>
</main>

<?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>
        