<?php
// /2025/trustpc/cart.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/lib/app.php'; // h(), yen(), list_products(), $OPT_RAM, $OPT_SSD

$cart = $_SESSION['cart'] ?? [];
if (!is_array($cart)) $cart = [];

$all = list_products();
function find_by_slug_local($slug, $all){
  foreach ($all as $p) if (!empty($p['slug']) && $p['slug'] === $slug) return $p;
  return null;
}
function opt_delta($list, $val){
  if (!is_array($list)) return 0;
  foreach ($list as $o) {
    if ((string)($o['val'] ?? '') === (string)$val) return (int)($o['delta'] ?? 0);
  }
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

foreach ($cart as $idx => $line) {
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
    $v = ($k==='ram') ? $ram : $ssd;
    $label = opt_label($k, $v);
    if ($label) $optTexts[] = $label;
  }

  $items[] = [
    'idx'  => $idx,
    'name' => $p['name'] ?? $slug,
    'slug' => $slug,
    'unit' => $unit,
    'qty'  => $qty,
    'sub'  => $sub,
    'opts' => implode(' / ', $optTexts),
  ];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>カート | trustPC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
  <style>html{scroll-behavior:smooth}</style>
</head>
<body class="bg-gray-50 text-gray-900">
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container mx-auto px-4 py-6">
  <h1 class="text-xl font-bold mb-4">カート</h1>

  <?php if (!$items): ?>
    <p>カートは空です。</p>
    <div class="mt-4">
      <a class="px-4 py-2 rounded bg-blue-600 text-white" href="index.php">トップへ戻る</a>
    </div>
  <?php else: ?>
    <div id="cartList" class="space-y-3">
      <?php foreach ($items as $i): ?>
        <div class="cart-card bg-white rounded-lg p-4 shadow flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2"
             data-idx="<?= (int)$i['idx'] ?>">
          <div>
            <div class="font-semibold"><?= h($i['name']) ?></div>
            <?php if ($i['opts']): ?>
              <div class="text-sm text-gray-600"><?= h($i['opts']) ?></div>
            <?php endif; ?>
            <div class="text-sm text-gray-600">数量: <?= h($i['qty']) ?></div>
          </div>
          <div class="text-right">
            <div class="text-sm text-gray-500">単価: <?= yen($i['unit']) ?></div>
            <div class="font-bold">小計: <span class="sub"><?= yen($i['sub']) ?></span></div>
            <!-- ★ 非遷移の削除ボタン -->
            <button type="button" class="mt-2 px-3 py-1 text-sm border rounded remove-cart">削除</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="text-right mt-4 text-lg font-bold">合計: <span id="cartTotal"><?= yen($total) ?></span></div>

    <div class="mt-4 flex gap-2 justify-end">
      <a class="px-4 py-2 rounded border" href="index.php">買い物を続ける</a>
      <a class="px-4 py-2 rounded bg-blue-600 text-white" href="checkout.php?p=checkout">購入手続きに進む</a>
    </div>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
(function(){
  function toast(msg){
    let el = document.getElementById('cartToast');
    if(!el){
      el = document.createElement('div');
      el.id = 'cartToast';
      Object.assign(el.style,{
        position:'fixed', right:'16px', bottom:'16px',
        background:'#111827', color:'#fff', padding:'10px 14px',
        borderRadius:'10px', boxShadow:'0 6px 20px rgba(0,0,0,.2)',
        zIndex:'2000', transition:'opacity .2s'
      });
      document.body.appendChild(el);
    }
    el.textContent = msg;
    el.style.opacity='1';
    setTimeout(()=>{ el.style.opacity='0'; }, 1500);
  }

  const list = document.getElementById('cartList');
  if(!list) return;

  list.addEventListener('click', async (e)=>{
    const btn = e.target.closest('.remove-cart');
    if(!btn) return;

    const card = btn.closest('.cart-card');
    const idx  = card?.dataset?.idx;
    if (typeof idx === 'undefined') return;

    const url = new URL('cart_remove.php', window.location.href);
    try{
      const res = await fetch(url.toString(), {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({ idx })
      });
      if(!res.ok) throw new Error('HTTP '+res.status);
      const data = await res.json();

      if (data.ok){
        // DOMからカードを削除
        card.remove();

        // 合計を更新
        const totalEl = document.getElementById('cartTotal');
        if (totalEl && data.total_html) totalEl.textContent = data.total_html;

        // ヘッダーのカート個数バッジ（id="cartCount"想定）も更新
        if (typeof data.count !== 'undefined') {
          const badge = document.getElementById('cartCount');
          if (badge) badge.textContent = String(data.count);
        }

        toast('カートから削除しました');

        // すべて無くなったら空表示に差し替え
        if (!list.querySelector('.cart-card')) {
          list.insertAdjacentHTML('beforebegin', '<p>カートは空です。</p>');
          list.remove();
          // 合計0にも更新
          if (totalEl) totalEl.textContent = '¥0';
        }
      }else{
        toast('削除に失敗しました');
      }
    }catch(err){
      console.error(err);
      toast('通信エラーが発生しました');
    }
  });
})();
</script>
</body>
</html>
