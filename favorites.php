<?php
// /2025/trustpc/favorites.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__.'/lib/app.php'; // h(), yen(), list_products()

/* -----------------------------
 * 1) お気に入り（slug配列）を安全に正規化
 *    - 文字列: そのまま
 *    - 配列: ['slug'=>...] を拾う（旧データ互換）
 *    - その他スカラー: 文字列化（保険）
 * ----------------------------- */
$favsRaw = $_SESSION['favorites'] ?? [];
$favs = [];
if (is_array($favsRaw)) {
  foreach ($favsRaw as $v) {
    if (is_array($v) && isset($v['slug'])) {
      $slug = (string)$v['slug'];
      if ($slug !== '') $favs[] = $slug;
    } elseif (is_string($v)) {
      if ($v !== '') $favs[] = $v;
    } elseif (is_scalar($v)) {
      $s = (string)$v;
      if ($s !== '') $favs[] = $s;
    }
  }
}
$favs = array_values(array_unique($favs)); // ★ ここまで来れば全部文字列なので安全

/* -----------------------------
 * 2) 詳細（ram/ssd/qty/price）は別配列
 * ----------------------------- */
$details = $_SESSION['favorites_detail'] ?? [];
if (!is_array($details)) $details = [];

/* -----------------------------
 * 3) 商品データ取得（slug → 商品）
 * ----------------------------- */
$all = list_products();
function find_by_slug_local($slug, $all){
  foreach ($all as $p) if (!empty($p['slug']) && $p['slug'] === $slug) return $p;
  return null;
}
$prods = array_map(fn($s)=> find_by_slug_local($s, $all), $favs);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>お気に入り | trustPC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css"/>
  <style>html{scroll-behavior:smooth}</style>
</head>
<body class="bg-gray-50 text-gray-900 has-sticky-offset">
<?php include __DIR__.'/includes/header.php'; ?>

<main class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">お気に入り</h1>
  </div>

  <?php
    // 全て null（slug不正など）だったら空扱いにする
    $hasAny = false;
    if ($prods) {
      foreach ($prods as $pp) { if ($pp) { $hasAny = true; break; } }
    }
  ?>

  <?php if (!$prods || !$hasAny): ?>
    <p>お気に入りは空です。</p>
  <?php else: ?>
    <div id="favGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($prods as $p):
        if(!$p) continue;
        $slug = (string)($p['slug'] ?? '');
        // セッションに保存された選択値と現在価格
        $ramSel = (string)($details[$slug]['ram']   ?? '');
        $ssdSel = (string)($details[$slug]['ssd']   ?? '');
        $nowPri = (int)   ($details[$slug]['price'] ?? 0);

        // 表示RAM文言（セッションが優先、なければ製品既定）
        $ramText = $ramSel !== '' ? ($ramSel.'GB') : ((string)($p['ram'] ?? '') !== '' ? ($p['ram'].'GB') : '');
        // 表示価格（セッション保存の現在価格があれば優先）
        $unitPrice = $nowPri > 0 ? $nowPri : (int)($p['price'] ?? 0);
      ?>
        <article class="fav-card rounded-2xl border bg-white p-4" data-slug="<?= h($slug) ?>">
          <div class="badge mb-2"><?= h($p['cpu'] ?? '') ?></div>
          <h3 class="font-semibold"><?= h($p['name'] ?? $slug) ?></h3>
          <p class="text-sm text-gray-600">
            <?= h($p['gpu'] ?? '') ?><?= $ramText!=='' ? ' / '.h($ramText) : '' ?>
          </p>
          <div class="mt-2 flex items-center justify-between">
            <div class="font-semibold"><?= yen($unitPrice) ?></div>
            <div class="flex gap-2">
              <a class="text-blue-600 underline" href="product_detail.php?s=<?= h($slug) ?>">詳細</a>
              <button type="button" class="px-3 py-1 text-sm border rounded remove-fav">削除</button>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php include __DIR__.'/includes/footer.php'; ?>

<script>
(function(){
  function toast(msg){
    let el = document.getElementById('favToast');
    if(!el){
      el = document.createElement('div');
      el.id = 'favToast';
      Object.assign(el.style, {
        position:'fixed', right:'16px', bottom:'16px',
        background:'#111827', color:'#fff', padding:'10px 14px',
        borderRadius:'10px', boxShadow:'0 6px 20px rgba(0,0,0,.2)',
        zIndex:'2000', transition:'opacity .2s'
      });
      document.body.appendChild(el);
    }
    el.textContent = msg;
    el.style.opacity = '1';
    setTimeout(()=>{ el.style.opacity = '0'; }, 1500);
  }

  const grid = document.getElementById('favGrid');
  if(!grid) return;

  grid.addEventListener('click', async (e)=>{
    const btn = e.target.closest('.remove-fav');
    if(!btn) return;

    const card = btn.closest('.fav-card');
    const slug = card?.dataset?.slug || '';
    if(!slug) return;

    try{
      const url = new URL('favorite_remove.php', window.location.href);
      const res = await fetch(url.toString(), {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({ slug })
      });
      if(!res.ok) throw new Error('HTTP '+res.status);
      const data = await res.json();

      if (data.ok){
        card.remove();
        if (typeof data.count !== 'undefined') {
          const badge = document.getElementById('favCount');
          if (badge) badge.textContent = String(data.count);
        }
        toast('お気に入りから削除しました');

        if (!grid.querySelector('.fav-card')) {
          grid.insertAdjacentHTML('beforebegin', '<p>お気に入りは空です。</p>');
          grid.remove();
        }
      }else{
        toast(data.message || '削除に失敗しました');
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
