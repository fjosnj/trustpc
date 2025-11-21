<?php
// /2025/trustpc/product_detail.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/lib/app.php'; // h(), yen(), list_products(), $OPT_*
require __DIR__ . '/includes/db_connect.php';

// --- 商品取得（SKU or slug どちらでもOK・頑丈版） ---
$id   = $_GET['id'] ?? '';   // 例: TP-G-ADV
$slug = $_GET['s']  ?? '';   // 例: tp-g-adv
$key  = $_GET['key'] ?? '';  // 名前キーワード（任意）

$all  = list_products();
$prod = null;

// 1) slug 完全一致
if ($slug !== '') {
  $stmt = $pdo->prepare("SELECT * FROM products WHERE slug = ?");
  $stmt->execute([$slug]);
  $prod = $stmt->fetch(PDO::FETCH_ASSOC);
}
// 2) SKU 完全一致
if (!$prod && $id !== '') {
  $stmt = $pdo->prepare("SELECT * FROM products WHERE sku = ?");
  $stmt->execute([$id]);
  $prod = $stmt->fetch(PDO::FETCH_ASSOC);
}
// 3) 大文字小文字無視（LIKEで拾う）
if (!$prod && ($id !== '' || $slug !== '')) {
  $want = strtolower($id ?: $slug);
  $stmt = $pdo->prepare("SELECT * FROM products WHERE LOWER(slug) = ? OR LOWER(sku) = ?");
  $stmt->execute([$want, $want]);
  $prod = $stmt->fetch(PDO::FETCH_ASSOC);
}
// 4) 名前の部分一致（任意）
if (!$prod && $key !== '') {
  $want = '%' . $key . '%';
  $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ?");
  $stmt->execute([$want]);
  $prod = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (!$prod) { http_response_code(404); exit('商品が見つかりません'); }

// --- 既定のオプション初期値（初期選択の決定に使用） ---
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
$img = $prod['image_url'] ?: './img/products/noimage.png';

// ===== ここから追加：$OPT_RAM / $OPT_SSD をDBから読み込み（なければ既定値） =====
global $OPT_RAM, $OPT_SSD;
$OPT_RAM = $OPT_SSD = [];

try {
  // 例: option_groups(id, product_id, code, name)
  //     option_items(id, group_id, val, label, delta, sort_order)
  $stmt = $pdo->prepare("
    SELECT g.code, i.val, i.label, i.delta
      FROM option_groups g
      JOIN option_items  i ON i.group_id = g.id
     WHERE g.product_id = ?
     ORDER BY g.code, i.sort_order, i.id
  ");
  $stmt->execute([$prod['id'] ?? 0]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($rows as $r) {
    $row = [
      'val'   => (string)($r['val'] ?? ''),
      'label' => (string)($r['label'] ?? ''),
      'delta' => (int)($r['delta'] ?? 0),
    ];
    if ($r['code'] === 'ram') $OPT_RAM[] = $row;
    if ($r['code'] === 'ssd') $OPT_SSD[] = $row;
  }
} catch (Throwable $e) {
  // 無視してフォールバックへ
}

// 何も取れなかった場合のフォールバック（必ず選べる状態を保証）
if (!$OPT_RAM) {
  $OPT_RAM = [
    ['val'=>'16','label'=>'16GB','delta'=>0],
    ['val'=>'32','label'=>'32GB（+¥20,000）','delta'=>20000],
    ['val'=>'64','label'=>'64GB（+¥50,000）','delta'=>50000],
  ];
}
if (!$OPT_SSD) {
  $OPT_SSD = [
    ['val'=>'512','label'=>'SSD 512GB（-¥10,000）','delta'=>-10000],
    ['val'=>'1tb','label'=>'SSD 1TB','delta'=>0],
    ['val'=>'2tb','label'=>'SSD 2TB（+¥12,000）','delta'=>12000],
  ];
}
// ===== 追加ここまで =====
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
        <img src="<?= h($img) ?>" alt="<?= h($prod['name']) ?>" class="w-full h-full object-cover"
             onerror="this.onerror=null;this.src='./img/products/noimage.png';">
      </div>
    </div>

    <!-- 右：価格/在庫/オプション/ボタン -->
    <div>
      <div class="flex items-center gap-3">
        <div id="priceView" class="text-2xl font-bold"><?= yen($basePrice) ?></div>
        <span class="inline-flex items-center text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">在庫あり</span>
      </div>

      <!-- 比較/お気に入り/カート すべて非遷移AJAX -->
      <form class="mt-4 space-y-4" method="post">
        <input type="hidden" name="slug" value="<?= h($prod['slug']) ?>">
        <input type="hidden" name="qty" value="1">
        <input type="hidden" name="base_price" value="<?= (int)$basePrice ?>">
        <input type="hidden" id="priceInput" name="price" value="<?= (int)$basePrice ?>"><!-- 現在価格 -->

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
          <button id="btnCompare" type="button"
                  class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-50">
            比較に追加
          </button>

          <button id="btnFav" type="button"
                  class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-50">
            お気に入りに追加
          </button>

          <button id="btnCart" type="button"
                  class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
            カートに入れる
          </button>
        </div>
      </form>

      <!-- 仕様表（メモリ/ストレージはJSで即時反映） -->
      <div class="mt-6 rounded-2xl border bg-white overflow-hidden">
        <table class="w-full text-sm">
          <tr class="border-b"><th class="w-28 text-left p-3 text-gray-600">CPU</th><td class="p-3"><?= h($prod['cpu']) ?></td></tr>
          <tr class="border-b"><th class="w-28 text-left p-3 text-gray-600">GPU</th><td class="p-3"><?= h($prod['gpu']) ?></td></tr>
          <tr class="border-b"><th class="w-28 text-left p-3 text-gray-600">メモリ</th><td class="p-3"><span id="specRam"><?= h($prod['ram']) ?>GB DDR5</span></td></tr>
          <tr><th class="w-28 text-left p-3 text-gray-600">ストレージ</th><td class="p-3"><span id="specSsd"><?= h($prod['storage']) ?></span></td></tr>
        </table>
      </div>

      <div class="mt-4 rounded-2xl border bg-white p-4 text-sm text-gray-700">
        ご不明点は <a href="https://lin.ee/enlPoIF" onclick="location.href='line://ti/p/@enlPoIF'; return false;">LINEサポート</a> へ。
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<!-- 価格更新 + 仕様表同期 -->
<script>
(function(){
  const base = <?= (int)$basePrice ?>;
  const ram  = document.getElementById('optRam');
  const ssd  = document.getElementById('optSsd');
  const view = document.getElementById('priceView');
  const priceInput = document.getElementById('priceInput');
  const specRam = document.getElementById('specRam');
  const specSsd = document.getElementById('specSsd');

  const yen = n => '¥' + (Math.round(n)).toLocaleString();

  function syncSpecs(){
    const ramLabel = ram?.selectedOptions?.[0]?.textContent?.trim() || '';
    const ssdLabel = ssd?.selectedOptions?.[0]?.textContent?.trim() || '';
    if (specRam && ramLabel){
      const txt = /GB/i.test(ramLabel) ? ramLabel : (ramLabel + 'GB');
      specRam.textContent = txt + ' DDR5';
    }
    if (specSsd && ssdLabel){
      specSsd.textContent = ssdLabel;
    }
  }

  function calc(){
    const d1 = parseInt(ram?.selectedOptions[0]?.dataset.delta || '0', 10);
    const d2 = parseInt(ssd?.selectedOptions[0]?.dataset.delta || '0', 10);
    const price = base + d1 + d2;
    view.textContent = yen(price);
    if (priceInput) priceInput.value = String(price);
    syncSpecs();
  }
  ram?.addEventListener('change', calc);
  ssd?.addEventListener('change', calc);
  calc();
})();
</script>

<!-- 比較・お気に入り・カート：現在価格も送る -->
<script>
(function(){
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

  const form = document.querySelector('form');
  const getPayload = (extra={})=>{
    const slug  = form.querySelector('input[name="slug"]').value;
    const qty   = form.querySelector('input[name="qty"]').value || '1';
    const ram   = form.querySelector('#optRam').value;
    const ssd   = form.querySelector('#optSsd').value;
    const price = form.querySelector('#priceInput').value; // 現在価格
    return new URLSearchParams(Object.assign({ slug, qty, ram, ssd, price }, extra));
  };

  document.getElementById('btnCompare')?.addEventListener('click', async ()=>{
    try{
      const res = await fetch('compare_add.php?ajax=1', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: getPayload()
      });
      const data = await res.json().catch(()=>({ok:true}));
      toast(data.ok ? '比較に追加しました' : (data.message || '追加に失敗しました'));
    }catch(e){ console.error(e); toast('通信エラー'); }
  });

  document.getElementById('btnFav')?.addEventListener('click', async ()=>{
    try{
      const res = await fetch('favorite_add.php?ajax=1', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: getPayload()
      });
      const data = await res.json().catch(()=>({ok:true}));
      if (data.ok && typeof data.count !== 'undefined') {
        const badge = document.getElementById('favCount'); if (badge) badge.textContent = String(data.count);
      }
      toast(data.ok ? 'お気に入りに追加しました' : (data.message || '追加に失敗しました'));
    }catch(e){ console.error(e); toast('通信エラー'); }
  });

  document.getElementById('btnCart')?.addEventListener('click', async ()=>{
    try{
      const res = await fetch('cart_add.php?ajax=1', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: getPayload()
      });
      const data = await res.json().catch(()=>({ok:true}));
      if (data.ok && typeof data.count !== 'undefined') {
        const badge = document.getElementById('cartCount'); if (badge) badge.textContent = String(data.count);
      }
      toast(data.ok ? 'カートに追加しました' : (data.message || '追加に失敗しました'));
    }catch(e){ console.error(e); toast('通信エラー'); }
  });
})();
</script>

</body>
</html>