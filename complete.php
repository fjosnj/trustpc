<?php
// /2025/trustpc/complete.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/lib/app.php'; // h(), yen(), list_products(), $OPT_RAM, $OPT_SSD

// 直前の入力
$addr = $_SESSION['checkout'] ?? null;

// 注文番号（例: TP20251111-1234）
$orderNo = 'TP' . date('Ymd') . '-' . random_int(1000, 9999);

// カート→明細作成
$cart = $_SESSION['cart'] ?? [];
$all  = list_products();
function find_by_slug_local($slug, $all){ foreach ($all as $p) if (!empty($p['slug']) && $p['slug']===$slug) return $p; return null; }
function opt_delta($list, $val){ if(!is_array($list))return 0; foreach($list as $o) if((string)($o['val']??'')===(string)$val) return (int)($o['delta']??0); return 0; }
function opt_label($k,$v){ $m=['ram'=>['16'=>'16GB','32'=>'32GB','64'=>'64GB'], 'ssd'=>['512'=>'SSD 512GB','1tb'=>'SSD 1TB','2tb'=>'SSD 2TB']]; return $m[$k][$v]??null; }
global $OPT_RAM, $OPT_SSD;

$lines=[]; $total=0;
foreach ($cart as $line) {
  $p=find_by_slug_local((string)($line['slug']??''), $all);
  if(!$p) continue;
  $qty=max(1,(int)($line['qty']??1));
  $ram=(string)($line['ram']??''); $ssd=(string)($line['ssd']??'');
  $unit=(int)$p['price'] + opt_delta($OPT_RAM,$ram) + opt_delta($OPT_SSD,$ssd);
  $sub=$unit*$qty; $total+=$sub;
  $opts=array_filter([opt_label('ram',$ram), opt_label('ssd',$ssd)]);
  $lines[]=['name'=>$p['name'],'qty'=>$qty,'sub'=>$sub,'opts'=>implode(' / ',$opts)];
}

// 疑似：注文履歴に保存（本実装はDBへ）
$_SESSION['orders'] = $_SESSION['orders'] ?? [];
$_SESSION['orders'][] = [
  'no'    => $orderNo,
  'at'    => date('Y-m-d H:i:s'),
  'items' => $lines,
  'total' => $total,
  'addr'  => $addr,
];

// カートは空に
$_SESSION['cart'] = [];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ご購入ありがとうございました | trustPC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css"><style>html{scroll-behavior:smooth}</style>
</head>
<body class="bg-gray-50 text-gray-900 has-sticky-offset">
<?php include __DIR__.'/includes/header.php'; ?>

<main class="max-w-7xl mx-auto px-4 py-6">
  <!-- 成功バナー -->
  <div class="rounded-2xl border bg-green-50 text-green-900 p-5 flex items-start gap-3 mb-6">
    <div>✅</div>
    <div>
      <div class="font-semibold">ご購入ありがとうございました。</div>
      <div class="text-sm">ご注文番号 <strong><?= h($orderNo) ?></strong> の受付が完了しました。確認メールをお送りします。</div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- 左：ご注文内容 -->
    <section class="lg:col-span-2 rounded-2xl border bg-white p-6">
      <h2 class="text-lg font-semibold mb-3">ご注文内容</h2>
      <table class="w-full text-sm border-collapse">
        <thead><tr class="border-b">
          <th class="text-left p-3">商品</th>
          <th class="text-center p-3">数量</th>
          <th class="text-right p-3">価格</th>
        </tr></thead>
        <tbody>
        <?php foreach($lines as $i): ?>
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

      <div class="mt-5 flex gap-2">
        <a class="px-4 py-2 rounded-lg border" href="index.php?p=mypage">購入履歴を見る</a>
        <a class="px-4 py-2 rounded-lg border" href="index.php">トップへ戻る</a>
      </div>
    </section>

    <!-- 右：お届け情報 -->
    <aside class="rounded-2xl border bg-white p-6">
      <h2 class="text-lg font-semibold mb-3">お届け情報</h2>
      <?php if($addr): ?>
        <dl class="text-sm space-y-2">
          <div><dt class="text-gray-600">お届け先</dt>
              <dd><?= h($addr['name']) ?><br><?= h($addr['pref'].' '.$addr['city'].' '.$addr['addr']) ?></dd></div>
          <div><dt class="text-gray-600">支払い方法</dt>
              <dd><?= $addr['pay']==='card'?'クレジットカード（VISA）':'代引き' ?></dd></div>
          <div><dt class="text-gray-600">出荷予定</dt><dd>2〜3営業日以内に出荷</dd></div>
          <div><dt class="text-gray-600">連絡先</dt><dd><?= h($addr['email']) ?></dd></div>
        </dl>
      <?php else: ?>
        <p class="text-gray-600">住所情報は未入力です。</p>
      <?php endif; ?>
    </aside>
  </div>
</main>

<?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>
<?php
// /2025/trustpc/complete.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/lib/app.php'; // h(), yen(), list_products(), $OPT_RAM, $OPT_SSD

$addr = $_SESSION['checkout'] ?? null;

// 注文番号（例: TP20251111-1234）
$orderNo = 'TP' . date('Ymd') . '-' . random_int(1000, 9999);

// カート→明細作成
$cart = $_SESSION['cart'] ?? [];
$all  = list_products();

function find_by_slug_local($slug, $all){
  foreach ($all as $p) if (!empty($p['slug']) && $p['slug'] === $slug) return $p;
  return null;
}
function opt_delta($list, $val){
  if(!is_array($list)) return 0;
  foreach($list as $o){
    if((string)($o['val']??'')===(string)$val) return (int)($o['delta']??0);
  }
  return 0;
}
function opt_label($k,$v){
  $m=['ram'=>['16'=>'16GB','32'=>'32GB','64'=>'64GB'],
      'ssd'=>['512'=>'SSD 512GB','1tb'=>'SSD 1TB','2tb'=>'SSD 2TB']];
  return $m[$k][$v]??null;
}
global $OPT_RAM, $OPT_SSD;

$lines=[]; $total=0;
foreach ($cart as $line) {
  $p = find_by_slug_local((string)($line['slug']??''), $all);
  if(!$p) continue;

  $qty = max(1,(int)($line['qty']??1));
  $ram = (string)($line['ram']??'');
  $ssd = (string)($line['ssd']??'');

  // ★ここが肝：詳細画面で確定した現在価格(単価)を最優先で採用
  $unit = (int)($line['price'] ?? 0);
  if ($unit <= 0) {
    // 保険：priceが未保存の古いデータは従来計算
    $unit = (int)$p['price'] + opt_delta($OPT_RAM,$ram) + opt_delta($OPT_SSD,$ssd);
  }

  $sub   = $unit * $qty;
  $total += $sub;

  $opts = array_filter([opt_label('ram',$ram), opt_label('ssd',$ssd)]);
  $lines[] = [
    'name'=>$p['name'],
    'qty'=>$qty,
    'sub'=>$sub,
    'opts'=>implode(' / ',$opts)
  ];
}

// 疑似：注文履歴に保存（本実装はDBへ）
$_SESSION['orders'] = $_SESSION['orders'] ?? [];
$_SESSION['orders'][] = [
  'no'    => $orderNo,
  'at'    => date('Y-m-d H:i:s'),
  'items' => $lines,
  'total' => $total,
  'addr'  => $addr,
];

// カートは空に
$_SESSION['cart'] = [];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ご購入ありがとうございました | trustPC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css"><style>html{scroll-behavior:smooth}</style>
</head>
<body class="bg-gray-50 text-gray-900 has-sticky-offset">
<?php include __DIR__.'/includes/header.php'; ?>

<main class="max-w-7xl mx-auto px-4 py-6">
  <div class="rounded-2xl border bg-green-50 text-green-900 p-5 flex items-start gap-3 mb-6">
    <div>✅</div>
    <div>
      <div class="font-semibold">ご購入ありがとうございました。</div>
      <div class="text-sm">ご注文番号 <strong><?= h($orderNo) ?></strong> の受付が完了しました。確認メールをお送りします。</div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <section class="lg:col-span-2 rounded-2xl border bg-white p-6">
      <h2 class="text-lg font-semibold mb-3">ご注文内容</h2>
      <table class="w-full text-sm border-collapse">
        <thead><tr class="border-b">
          <th class="text-left p-3">商品</th>
          <th class="text-center p-3">数量</th>
          <th class="text-right p-3">価格</th>
        </tr></thead>
        <tbody>
        <?php foreach($lines as $i): ?>
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

      <div class="mt-5 flex gap-2">
        <a class="px-4 py-2 rounded-lg border" href="mypage.php">購入履歴を見る</a>
        <a class="px-4 py-2 rounded-lg border" href="index.php">トップへ戻る</a>
      </div>
    </section>

    <aside class="rounded-2xl border bg-white p-6">
      <h2 class="text-lg font-semibold mb-3">お届け情報</h2>
      <?php if($addr): ?>
        <dl class="text-sm space-y-2">
          <div><dt class="text-gray-600">お届け先</dt>
              <dd><?= h($addr['name']) ?><br><?= h($addr['pref'].' '.$addr['city'].' '.$addr['addr']) ?></dd></div>
          <div><dt class="text-gray-600">支払い方法</dt>
              <dd><?= $addr['pay']==='card'?'クレジットカード（VISA）':'代引き' ?></dd></div>
          <div><dt class="text-gray-600">出荷予定</dt><dd>2〜3営業日以内に出荷</dd></div>
          <div><dt class="text-gray-600">連絡先</dt><dd><?= h($addr['email']) ?></dd></div>
        </dl>
      <?php else: ?>
        <p class="text-gray-600">住所情報は未入力です。</p>
      <?php endif; ?>
    </aside>
  </div>
</main>

<?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>
