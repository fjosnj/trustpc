<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__.'/lib/app.php';

// クリア処理
if (isset($_GET['clear'])) {
  if ($_GET['clear'] === 'all') {
    unset($_SESSION['compare'], $_SESSION['compare_detail']);
  } else {
    $slot = max(0, min(2, (int)($_GET['slot'] ?? -1)));
    if (isset($_SESSION['compare'][$slot])) {
      $slug = $_SESSION['compare'][$slot];
      $_SESSION['compare'][$slot] = null;
      if (!empty($_SESSION['compare_detail'][$slug])) unset($_SESSION['compare_detail'][$slug]);
    }
  }
  header('Location: compare.php'); exit;
}

// スロット復元
$slots = [null, null, null];
if (!empty($_SESSION['compare']) && is_array($_SESSION['compare'])) {
  foreach ([0,1,2] as $i) if (!empty($_SESSION['compare'][$i])) $slots[$i] = $_SESSION['compare'][$i];
}

// 商品取得
$all = list_products();
function find_by_slug_local($slug, $all){
  foreach ($all as $p) if (!empty($p['slug']) && $p['slug'] === $slug) return $p;
  return null;
}
$prods   = array_map(fn($s)=> $s ? find_by_slug_local($s, $all) : null, $slots);

// ★ ここが肝：詳細画面から保存した選択＆現在価格を参照
$details = $_SESSION['compare_detail'] ?? [];

function cell($v){ return $v!==null && $v!=='' ? h($v) : '—'; }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>製品比較 | trustPC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css"/>
  <style>
    .cmp-card{border-radius:16px;border:1px solid rgba(17,18,20,.1);background:#fff}
    .cmp-row{display:flex;justify-content:space-between;align-items:center;border:1px solid rgba(17,18,20,.06);
             border-radius:12px;padding:10px 12px;margin-top:10px}
    .cmp-key{color:#64748b;font-size:14px}
    .cmp-val{font-size:14px}
    .cmp-img{height:220px;background:#f3f4f6;border:1px solid rgba(17,18,20,.06);border-radius:12px;
             display:flex;align-items:center;justify-content:center;color:#9aa3af}
  </style>
</head>
<body class="bg-gray-50 text-gray-900 has-sticky-offset">
<?php include __DIR__.'/includes/header.php'; ?>

<main class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">製品比較</h1>
    <a class="text-sm text-blue-600 underline" href="compare.php?clear=all">すべてクリア</a>
  </div>

  <div class="grid gap-5 grid-cols-1 md:grid-cols-3">
    <?php foreach ([0,1,2] as $i): $p = $prods[$i]; ?>
      <section class="cmp-card p-4">
        <h2 class="text-lg font-semibold mb-3">製品 <?= $i+1 ?></h2>

        <!-- ★★★ DB の image_url を表示できる画像部分 ★★★ -->
        <div class="cmp-img">
          <?php if ($p && !empty($p['image_url'])): ?>
            <img
              src="<?= h($p['image_url']) ?>"
              alt="<?= h($p['name']) ?>"
              class="max-h-full max-w-full object-contain"
            >
          <?php else: ?>
            画像なし
          <?php endif; ?>
        </div>
        <!-- ★★★★★★★★★★★★★★★★★★★★★★★★★★ -->

        <div class="cmp-row mt-4">
          <span class="cmp-key">製品名</span>
          <span class="cmp-val"><?= $p ? h($p['name']) : '未登録' ?></span>
        </div>

        <div class="cmp-row">
          <span class="cmp-key">価格</span>
          <span class="cmp-val">
            <?php if ($p):
              $slug = $p['slug'] ?? '';
              $unit = (!empty($details[$slug]['price']) && (int)$details[$slug]['price'] > 0)
                        ? (int)$details[$slug]['price'] : (int)($p['price'] ?? 0);
              echo yen($unit);
            else: ?>—<?php endif; ?>
          </span>
        </div>

        <div class="cmp-row">
          <span class="cmp-key">CPU</span>
          <span class="cmp-val"><?= $p ? cell($p['cpu']) : '—' ?></span>
        </div>
        <div class="cmp-row">
          <span class="cmp-key">GPU</span>
          <span class="cmp-val"><?= $p ? cell($p['gpu']) : '—' ?></span>
        </div>

        <div class="cmp-row">
          <span class="cmp-key">メモリ</span>
          <span class="cmp-val">
            <?php if ($p):
              $slug = $p['slug'] ?? '';
              echo h(!empty($details[$slug]['ram']) ? $details[$slug]['ram'].'GB DDR5' : (($p['ram'] ?? '').'GB DDR5'));
            else: ?>—<?php endif; ?>
          </span>
        </div>

        <div class="cmp-row">
          <span class="cmp-key">ストレージ</span>
          <span class="cmp-val">
            <?php if ($p):
              $slug = $p['slug'] ?? '';
              echo h($details[$slug]['ssd'] ?? ($p['storage'] ?? ''));
            else: ?>—<?php endif; ?>
          </span>
        </div>

        <div class="mt-3">
          <a class="inline-flex items-center px-4 py-2 rounded-lg border bg-white hover:bg-gray-50"
             href="compare.php?clear=1&slot=<?= $i ?>">この列をクリア</a>
          <?php if ($p): ?>
            <a class="inline-flex items-center px-3 py-2 rounded-lg bg-blue-600 text-white ml-2"
               href="product_detail.php?s=<?= h($p['slug']) ?>">詳細へ</a>
          <?php endif; ?>
        </div>
      </section>
    <?php endforeach; ?>
  </div>
</main>

<?php include __DIR__.'/includes/footer.php'; ?>
</body>
</html>
