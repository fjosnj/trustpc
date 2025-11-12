<?php
// /2025/trustpc/favorites.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__.'/lib/app.php'; // h(), yen(), list_products()

$favs = $_SESSION['favorites'] ?? [];
$favs = is_array($favs) ? array_values(array_unique(array_filter($favs))) : [];

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

  <?php if (!$prods): ?>
    <p>お気に入りは空です。</p>
  <?php else: ?>
    <div id="favGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($prods as $p): if(!$p) continue; ?>
        <article class="fav-card rounded-2xl border bg-white p-4" data-slug="<?= h($p['slug']) ?>">
          <div class="badge mb-2"><?= h($p['cpu']) ?></div>
          <h3 class="font-semibold"><?= h($p['name']) ?></h3>
          <p class="text-sm text-gray-600"><?= h($p['gpu']) ?> / <?= h($p['ram']) ?>GB</p>
          <div class="mt-2 flex items-center justify-between">
            <div class="font-semibold"><?= yen((int)$p['price']) ?></div>
            <div class="flex gap-2">
              <a class="text-blue-600 underline" href="product_detail.php?s=<?= h($p['slug']) ?>">詳細</a>
              <!-- ★ 非遷移の削除ボタンのみ -->
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
  // かんたんトースト（共有）
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

    const url = new URL('favorite_remove.php', window.location.href); // JSONのみ
    try{
      const res = await fetch(url.toString(), {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({ slug })
      });
      if(!res.ok) throw new Error('HTTP '+res.status);
      const data = await res.json();
      if(data.ok){
        // カード削除
        card.remove();
        // ヘッダの件数バッジ（id="favCount"想定）があれば更新
        if (typeof data.count !== 'undefined') {
          const badge = document.getElementById('favCount');
          if (badge) badge.textContent = String(data.count);
        }
        toast('お気に入りから削除しました');
        // 0件になったら空表示に切り替え
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
