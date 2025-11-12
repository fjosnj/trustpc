<?php
// /2025/trustpc/product_detail.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/lib/app.php'; // h(), yen(), list_products(), $OPT_*

// --- 商品取得（SKU or slug どちらでもOK・頑丈版） ---
$id   = $_GET['id'] ?? '';   // 例: TP-G-ADV
$slug = $_GET['s']  ?? '';   // 例: tp-g-adv
$key  = $_GET['key'] ?? '';  // 名前キーワード（任意）

$all  = list_products();
$prod = null;

// 1) slug 完全一致
if ($slug !== '') {
  foreach ($all as $p) { if (!empty($p['slug']) && $p['slug'] === $slug) { $prod = $p; break; } }
}
// 2) SKU 完全一致
if (!$prod && $id !== '') {
  foreach ($all as $p) { if (!empty($p['sku']) && $p['sku'] === $id) { $prod = $p; break; } }
}
// 3) 大文字小文字無視
if (!$prod && ($id !== '' || $slug !== '')) {
  $want = strtolower($id ?: $slug);
  foreach ($all as $p) {
    if (!empty($p['slug']) && strtolower($p['slug']) === $want) { $prod = $p; break; }
    if (!empty($p['sku'])  && strtolower($p['sku'])  === $want) { $prod = $p; break; }
  }
}
// 4) 名前の部分一致（任意）
if (!$prod && $key !== '') {
  $want = mb_strtolower($key);
  foreach ($all as $p) {
    if (!empty($p['name']) && mb_strpos(mb_strtolower($p['name']), $want) !== false) { $prod = $p; break; }
  }
}
if (!$prod) { http_response_code(404); exit('商品が見つかりません'); }

// 既定のオプション初期値
function pick_ram_val($ram){ return (string)max(16,(int)$ram); }
function pick_ssd_val($storage){
  $s = strtolower($storage ?? '');
  if (strpos($s,'2tb') !== false) return '2tb';
  if (strpos($s,'512') !== false) return '512';
  return '1tb';
}
$default_ram = pick_ram_val($prod['ram'] ?? 16);
$default_ssd = pick_ssd_val($prod['storage'] ?? '1TB SSD');

$basePrice = (int)($prod['price'] ?? 0);
$img = 'data/img/noimage.jpg'; // 画像があるなら差し替え
global $OPT_RAM, $OPT_SSD;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($prod['name']) ?> | trustPC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
  <style>html{scroll-behavior:smooth}</style>
</head>
<body class="bg-gray-50 text-gray-900 has-sticky-offset">
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="max-w-7xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-semibold mb-4"><?= h($prod['name']) ?></h1>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- 左：画像枠 -->
    <div class="rounded-2xl border bg-white p-4">
      <div class="w-full aspect-[4/3] rounded-xl bg-gray-100 overflow-hidden">
        <!-- 画像を使う場合は hidden を外す -->
        <img src="<?= h($img) ?>" alt="<?= h($prod['name']) ?>" class="w-full h-full object-cover hidden">
      </div>
    </div>

    <!-- 右：価格/在庫/オプション/ボタン -->
    <div>
      <div class="flex items-center gap-3">
        <div id="priceView" class="text-2xl font-bold"><?= yen($basePrice) ?></div>
        <span class="inline-flex items-center text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">在庫あり</span>
      </div>

      <!-- 比較は遷移なし（AJAX）／カートは従来どおり送信 -->
      <form class="mt-4 space-y-4" method="post" action="lib/app.php">
        <input type="hidden" name="slug" value="<?= h($prod['slug']) ?>">
        <input type="hidden" name="qty" value="1">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- メモリ -->
          <label class="block">
            <span class="text-sm text-gray-700">メモリ</span>
            <select name="ram" id="optRam" class="mt-1 w-full border rounded-lg p-2">
              <?php foreach ($OPT_RAM as $o): ?>
                <option value="<?= h($o['val']) ?>"
                        data-delta="<?= (int)$o['delta'] ?>"
                        <?= ($o['val']===$default_ram ? 'selected' : '') ?>>
                  <?= h($o['label']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <!-- ストレージ -->
          <label class="block">
            <span class="text-sm text-gray-700">ストレージ</span>
            <select name="ssd" id="optSsd" class="mt-1 w-full border rounded-lg p-2">
              <?php foreach ($OPT_SSD as $o): ?>
                <option value="<?= h($o['val']) ?>"
                        data-delta="<?= (int)$o['delta'] ?>"
                        <?= ($o['val']===$default_ssd ? 'selected' : '') ?>>
                  <?= h($o['label']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>
        </div>

        <div class="flex items-center gap-3">
  <!-- 比較：遷移なし -->
  <button id="btnCompare" type="button"
          class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-50">
    比較に追加
  </button>

  <button id="btnFav" type="button"
          class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-50">
    お気に入りに追加
  </button>

  <!-- カート：遷移なし（AJAXで cart_add.php に送る） -->
  <button id="btnCart" type="button"
          class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
    カートに入れる
  </button>
</div>
      </form>

      <div class="mt-6 rounded-2xl border bg-white overflow-hidden">
        <table class="w-full text-sm">
          <tr class="border-b"><th class="w-28 text-left p-3 text-gray-600">CPU</th><td class="p-3"><?= h($prod['cpu']) ?></td></tr>
          <tr class="border-b"><th class="w-28 text-left p-3 text-gray-600">GPU</th><td class="p-3"><?= h($prod['gpu']) ?></td></tr>
          <tr class="border-b"><th class="w-28 text-left p-3 text-gray-600">メモリ</th><td class="p-3"><?= h($prod['ram']) ?>GB DDR5</td></tr>
          <tr><th class="w-28 text-left p-3 text-gray-600">ストレージ</th><td class="p-3"><?= h($prod['storage']) ?></td></tr>
        </table>
      </div>

      <div class="mt-4 rounded-2xl border bg-white p-4 text-sm text-gray-700">
        ご不明点は <a class="text-blue-600 underline" href="index.php?p=line">LINEサポート</a> へ。
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
(function(){
  // 価格のリアルタイム更新
  const base = <?= (int)$basePrice ?>;
  const ram  = document.getElementById('optRam');
  const ssd  = document.getElementById('optSsd');
  const view = document.getElementById('priceView');
  function yen(n){ return '¥' + (Math.round(n)).toLocaleString(); }
  function calc(){
    const d1 = parseInt(ram?.selectedOptions[0]?.dataset.delta || '0', 10);
    const d2 = parseInt(ssd?.selectedOptions[0]?.dataset.delta || '0', 10);
    view.textContent = yen(base + d1 + d2);
  }
  ram?.addEventListener('change', calc);
  ssd?.addEventListener('change', calc);
  calc();

  // 比較に追加（AJAX）
  const btn = document.getElementById('btnCompare');
  const form = btn?.closest('form');
  if(!btn || !form) return;

  function toast(msg){
    let el = document.getElementById('cmpToast');
    if(!el){
      el = document.createElement('div');
      el.id = 'cmpToast';
      el.style.position='fixed';
      el.style.right='16px';
      el.style.bottom='16px';
      el.style.background='#111827';
      el.style.color='#fff';
      el.style.padding='10px 14px';
      el.style.borderRadius='10px';
      el.style.boxShadow='0 6px 20px rgba(0,0,0,.2)';
      el.style.zIndex='2000';
      document.body.appendChild(el);
    }
    el.textContent = msg;
    el.style.opacity='1';
    setTimeout(()=>{ el.style.opacity='0'; }, 1600);
  }

  btn.addEventListener('click', async ()=>{
    const slug = form.querySelector('input[name="slug"]')?.value || '';
    if(!slug){ toast('エラー：slugが見つかりません'); return; }
    try{
      const res = await fetch('compare_add.php?ajax=1', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({ slug })
      });
      if(!res.ok) throw new Error('HTTP '+res.status);
      const data = await res.json().catch(()=>({ok:true}));
      if(data.ok){
        toast('比較に追加しました。比較ページで確認できます。');
      }else{
        toast(data.message || '追加に失敗しました');
      }
    }catch(err){
      console.error(err);
      toast('通信エラーが発生しました');
    }
  });
})();
</script>
<script>
(function(){
  // ---- すでにある価格更新と比較の処理はそのまま ----
  // ここでは「カートに入れる（AJAX）」だけ追加します。

  const cartBtn = document.getElementById('btnCart');
  const form    = cartBtn?.closest('form');
  if(!cartBtn || !form) return;

  function toast(msg){
    let el = document.getElementById('cmpToast');
    if(!el){
      el = document.createElement('div');
      el.id = 'cmpToast';
      el.style.position='fixed';
      el.style.right='16px';
      el.style.bottom='16px';
      el.style.background='#111827';
      el.style.color='#fff';
      el.style.padding='10px 14px';
      el.style.borderRadius='10px';
      el.style.boxShadow='0 6px 20px rgba(0,0,0,.2)';
      el.style.zIndex='2000';
      el.style.transition='opacity .2s';
      document.body.appendChild(el);
    }
    el.textContent = msg;
    el.style.opacity='1';
    setTimeout(()=>{ el.style.opacity='0'; }, 1600);
  }

  cartBtn.addEventListener('click', async ()=>{
    const slug = form.querySelector('input[name="slug"]')?.value || '';
    const qty  = form.querySelector('input[name="qty"]')?.value || '1';
    const ram  = form.querySelector('#optRam')?.value || '';
    const ssd  = form.querySelector('#optSsd')?.value || '';
    if(!slug){ toast('エラー：slugが見つかりません'); return; }

    // 相対パスずれ対策：現在URLを基準に絶対化
    const url = new URL('cart_add.php?ajax=1', window.location.href);
    try{
      const res = await fetch(url.toString(), {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({ slug, qty, ram, ssd })
      });
      if(!res.ok) throw new Error('HTTP '+res.status);
      const data = await res.json().catch(()=>({ok:true}));
      if(data.ok){
        toast('カートに追加しました');
        // ヘッダーに個数バッジがあるなら更新（id="cartCount" を想定・任意）
        if (typeof data.count !== 'undefined') {
          const badge = document.getElementById('cartCount');
          if (badge) badge.textContent = String(data.count);
        }
      }else{
        toast(data.message || '追加に失敗しました');
      }
    }catch(err){
      console.error(err);
      toast('通信エラーが発生しました');
    }
  });
})();
</script>
<script>
(function(){
  // すでにある toast() を流用。無ければ下の簡易版でOK
  function ensureToast(){
    let el = document.getElementById('cmpToast');
    if(!el){
      el = document.createElement('div');
      el.id = 'cmpToast';
      Object.assign(el.style, {
        position:'fixed', right:'16px', bottom:'16px',
        background:'#111827', color:'#fff',
        padding:'10px 14px', borderRadius:'10px',
        boxShadow:'0 6px 20px rgba(0,0,0,.2)', zIndex:'2000',
        transition:'opacity .2s'
      });
      document.body.appendChild(el);
    }
    return el;
  }
  function toast(msg){
    const el = ensureToast();
    el.textContent = msg;
    el.style.opacity = '1';
    setTimeout(()=>{ el.style.opacity = '0'; }, 1600);
  }

  const favBtn = document.getElementById('btnFav');
  const form   = favBtn?.closest('form');
  if(!favBtn || !form) return;

  favBtn.addEventListener('click', async ()=>{
    const slug = form.querySelector('input[name="slug"]')?.value || '';
    if(!slug){ toast('エラー：slugが見つかりません'); return; }

    const url = new URL('favorite_add.php?ajax=1', window.location.href);
    try{
      const res = await fetch(url.toString(), {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({ slug })
      });
      if(!res.ok) throw new Error('HTTP '+res.status);
      const data = await res.json().catch(()=>({ok:true}));
      if(data.ok){
        toast('お気に入りに追加しました');
        // ヘッダにバッジがある場合（id="favCount"想定）を更新
        if (typeof data.count !== 'undefined') {
          const badge = document.getElementById('favCount');
          if (badge) badge.textContent = String(data.count);
        }
      }else{
        toast(data.message || '追加に失敗しました');
      }
    }catch(e){
      console.error(e);
      toast('通信エラーが発生しました');
    }
  });
})();
</script>


</body>
</html>
