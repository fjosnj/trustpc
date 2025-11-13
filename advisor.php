<?php
// /2025/trustpc/advisor.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/lib/app.php';   // list_products(), h(), yen()
$all = list_products();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>PC購入診断 | trustPC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css"/>
  <style>
    .chip{padding:.4rem .8rem;border:1px solid rgba(17,18,20,.12);border-radius:9999px;background:#fff;cursor:pointer}
    .chip.is-on{background:#eef2ff;border-color:#c7d2fe;color:#1d4ed8}
    .panel{border:1px solid rgba(17,18,20,.1);border-radius:16px;background:#fff}
    .ghost{color:#94a3b8}
  </style>
</head>
<body class="bg-gray-50 text-gray-900 has-sticky-offset">
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="max-w-7xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-semibold mb-6">PC購入診断</h1>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- 左：条件 -->
    <section class="panel p-4">
      <h2 class="font-semibold mb-3">条件を選んでください</h2>

      <!-- 予算（下限・上限の2本） -->
      <div class="mb-5">
        <div class="text-sm text-gray-600 mb-1">予算</div>

        <!-- 最小 -->
        <label class="block text-xs text-gray-600 mb-1">最小</label>
        <input id="budgetMin" type="range" min="60000" max="500000" step="10000" value="60000" class="w-full">
        <div class="mt-1 text-sm flex justify-between text-gray-600">
          <span>¥60,000</span><span id="budgetMinView">¥60,000</span><span>¥500,000</span>
        </div>

        <!-- 最大 -->
        <label class="block text-xs text-gray-600 mt-4 mb-1">最大</label>
        <input id="budget" type="range" min="60000" max="500000" step="10000" value="200000" class="w-full">
        <div class="mt-1 text-sm flex justify-between text-gray-600">
          <span>¥60,000</span><span id="budgetView">¥200,000</span><span>¥500,000</span>
        </div>
      </div>

      <!-- 用途 -->
      <div class="mb-5">
        <div class="text-sm text-gray-600 mb-2">用途（複数選択可）</div>
        <div id="uses" class="flex flex-wrap gap-2">
          <?php
            $useTags = ['ゲーム','動画編集/3DCG','Office/学習','プログラミング','配信'];
            foreach($useTags as $t){
              echo '<button type="button" class="chip" data-val="'.h($t).'">'.h($t).'</button>';
            }
          ?>
        </div>
      </div>

      <!-- 重点ポイント -->
      <div class="mb-5">
        <div class="text-sm text-gray-600 mb-2">重視ポイント</div>
        <div id="prio" class="flex flex-wrap gap-2">
          <?php foreach (['バランス','CPU性能','GPU性能','静音性'] as $t): ?>
            <button type="button" class="chip" data-val="<?=h($t)?>"><?=h($t)?></button>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="flex gap-2">
        <button id="btnRun" class="px-4 py-2 rounded-lg bg-blue-600 text-white">診断する</button>
        <button id="btnReset" class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-50">リセット</button>
      </div>
    </section>

    <!-- 右：おすすめ結果 -->
    <section class="panel p-4">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold">おすすめ結果</h2>
        <div class="flex gap-2">
          <button id="btnCopy" class="px-3 py-1.5 rounded-lg border bg-white hover:bg-gray-50 text-sm">結果をコピー</button>
          <a href="compare.php" class="px-3 py-1.5 rounded-lg bg-blue-600 text-white text-sm">比較ページへ</a>
        </div>
      </div>
      <div id="resultHint" class="text-sm text-gray-600">
        条件を選んで「診断する」を押してください。
      </div>
      <div id="resultList" class="hidden divide-y"></div>
    </section>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
const PRODUCTS = <?= json_encode($all, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;

// --- 小道具 ---
const fmtYen = n => '¥' + (Math.round(n)).toLocaleString();
const byId = id => document.getElementById(id);
function toast(msg){
  let el = document.getElementById('advisorToast');
  if(!el){
    el = document.createElement('div');
    el.id='advisorToast';
    Object.assign(el.style, {position:'fixed',right:'16px',bottom:'16px',background:'#111827',color:'#fff',padding:'10px 14px',borderRadius:'10px',boxShadow:'0 6px 20px rgba(0,0,0,.2)',zIndex:'2000',transition:'opacity .2s'});
    document.body.appendChild(el);
  }
  el.textContent = msg; el.style.opacity=1; setTimeout(()=>{ el.style.opacity=0; }, 1400);
}

// --- UI 取得 ---
const budgetMin = byId('budgetMin');
const budgetMinView = byId('budgetMinView');
const budget = byId('budget');
const budgetView = byId('budgetView');
const usesWrap = byId('uses');
const prioWrap = byId('prio');
const resultHint = byId('resultHint');
const resultList = byId('resultList');

// 初期表示
budgetMinView.textContent = fmtYen(budgetMin.value);
budgetView.textContent    = fmtYen(budget.value);

// スライダー同士の整合
function syncMinMax() {
  let min = parseInt(budgetMin.value,10);
  let max = parseInt(budget.value,10);
  if (min > max) {
    const active = document.activeElement === budgetMin ? 'min'
                  : (document.activeElement === budget ? 'max' : '');
    if (active === 'min') { budget.value = min; max = min; }
    else { budgetMin.value = max; min = max; }
  }
  budgetMinView.textContent = fmtYen(min);
  budgetView.textContent    = fmtYen(max);
}
budgetMin.addEventListener('input', syncMinMax);
budget.addEventListener('input', syncMinMax);

// チップのON/OFF
function toggleChip(e){
  const btn = e.target.closest('.chip');
  if(!btn) return;
  btn.classList.toggle('is-on');
}
usesWrap.addEventListener('click', toggleChip);
prioWrap.addEventListener('click', toggleChip);

// 診断ロジック（形状フィルタなし）
function run(){
  const budgetMax = parseInt(budget.value,10);
  const budgetMinVal = parseInt(budgetMin.value,10);
  const useSel  = Array.from(usesWrap.querySelectorAll('.chip.is-on')).map(b=>b.dataset.val);
  const prioSel = Array.from(prioWrap.querySelectorAll('.chip.is-on')).map(b=>b.dataset.val);

  // 1) 価格レンジで絞る
  let cand = PRODUCTS.filter(p => {
    const price = parseInt(p.price||0,10);
    return price >= budgetMinVal && price <= budgetMax;
  });

  // 2) スコアリング（簡易）
  function gpuTier(g){
    const s = (g||'').toLowerCase();
    if(s.includes('4090')) return 6;
    if(s.includes('4080')) return 5;
    if(s.includes('4070')) return 4;
    if(s.includes('4060')) return 3;
    if(s.includes('4050')||s.includes('3050')) return 2;
    return 1;
  }
  function cpuTier(c){
    const s = (c||'').toLowerCase();
    if(s.includes('7800x3d')||s.includes('i9')) return 5;
    if(s.includes('7950')||s.includes('7900')||s.includes('14700')||s.includes('i7')) return 4;
    if(s.includes('7700')||s.includes('5600')||s.includes('i5')) return 3;
    return 2;
  }

  cand = cand.map(p=>{
    const price = parseInt(p.price||0,10);
    let score = 0;

    // 使途
    if(useSel.length===0){ score += 1; }
    else{
      for(const u of useSel){
        if(u==='ゲーム') score += gpuTier(p.gpu)*1.2;
        if(u==='動画編集/3DCG') score += gpuTier(p.gpu)+cpuTier(p.cpu);
        if(u==='Office/学習') score += 2 + (price<200000?1:0);
        if(u==='プログラミング') score += cpuTier(p.cpu)+1;
        if(u==='配信') score += gpuTier(p.gpu)+1;
      }
    }

    // 重視ポイント
    for(const pr of prioSel){
      if(pr==='GPU性能') score += gpuTier(p.gpu)*1.3;
      if(pr==='CPU性能') score += cpuTier(p.cpu)*1.3;
      if(pr==='バランス') score += (gpuTier(p.gpu)+cpuTier(p.cpu))*0.8;
      if(pr==='静音性') score += 1; // ダミー
    }

    // 価格が近いほど加点
    const diff = Math.max(0, budgetMax-price);
    score += Math.min(3, diff/50000);

    return {...p, _score: score};
  });

  // 3) 並べ替え（スコア desc → 価格 asc）
  cand.sort((a,b)=> (b._score - a._score) || (a.price - b.price));

  render(cand.slice(0,6));
}

function render(items){
  if(items.length===0){
    resultList.classList.add('hidden');
    resultHint.classList.remove('hidden');
    resultHint.textContent = '条件に合う商品が見つかりませんでした。条件を緩めてお試しください。';
    return;
  }
  resultHint.classList.add('hidden');
  resultList.classList.remove('hidden');
  resultList.innerHTML = items.map(p=>`
    <div class="py-3">
      <div class="flex items-start justify-between">
        <div>
          <div class="font-semibold">${escapeHtml(p.name||p.slug)}</div>
          <div class="text-sm text-gray-600 mt-0.5">
            ${escapeHtml(p.cpu||'-')} / ${escapeHtml(p.gpu||'-')} / ${escapeHtml((p.ram||'')+'GB')} / ${escapeHtml(p.storage||'-')}
          </div>
        </div>
        <div class="text-right">
          <div class="font-semibold">${fmtYen(parseInt(p.price||0,10))}</div>
          <div class="text-xs ghost">score: ${Math.round((p._score||0)*10)/10}</div>
        </div>
      </div>
      <div class="mt-2 flex gap-2">
        <a href="product_detail.php?s=${encodeURIComponent(p.slug||'')}"
           class="px-3 py-1.5 rounded-lg border bg-white hover:bg-gray-50 text-sm">詳細</a>
        <button class="px-3 py-1.5 rounded-lg bg-blue-600 text-white text-sm add-compare"
                data-slug="${encodeURIComponent(p.slug||'')}">比較に追加</button>
      </div>
    </div>
  `).join('');
}

// エスケープ
function escapeHtml(s){ return String(s??'').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' })[m]); }

// 比較に追加（非遷移）
resultList.addEventListener('click', async (e)=>{
  const btn = e.target.closest('.add-compare');
  if(!btn) return;
  const slug = btn.dataset.slug || '';
  if(!slug) return;
  try{
    const res = await fetch('compare_add.php?ajax=1', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({slug})
    });
    if(!res.ok) throw new Error('HTTP '+res.status);
    await res.json();
    toast('比較に追加しました');
  }catch(err){
    console.error(err);
    toast('追加に失敗しました');
  }
});

// ボタン
byId('btnRun').addEventListener('click', run);
byId('btnReset').addEventListener('click', ()=>{
  budgetMin.value = 60000;
  budget.value    = 200000;
  syncMinMax();
  document.querySelectorAll('.chip').forEach(c=>c.classList.remove('is-on'));
  resultList.classList.add('hidden');
  resultHint.classList.remove('hidden');
  resultHint.textContent='条件を選んで「診断する」を押してください。';
});

// 結果をコピー
byId('btnCopy').addEventListener('click', ()=>{
  const rows = Array.from(resultList.querySelectorAll('.py-3')).map(card=>{
    const name = card.querySelector('.font-semibold')?.textContent?.trim()||'';
    const price= card.querySelector('.text-right .font-semibold')?.textContent?.trim()||'';
    return `${name} / ${price}`;
  });
  const text = rows.join('\n') || '（結果なし）';
  navigator.clipboard.writeText(text).then(()=> toast('クリップボードにコピーしました'));
});
</script>
</body>
</html>
