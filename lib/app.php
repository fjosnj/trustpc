<?php
// /2025/TrustPC/lib/app.php
// セッション開始（未開始なら）
if (session_status() === PHP_SESSION_NONE) session_start();

/* ========= 共通ヘルパ ========= */
function yen($n){ return '¥'.number_format((int)$n); }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/* ========= 商品（20件） ========= */
$PRODUCTS = [
  ['sku'=>'TP-G-LITE','slug'=>'tp-g-lite','name'=>'TrustPC Gaming Lite','type'=>'desktop','cpu'=>'Ryzen 5 5600','gpu'=>'RTX 4060','ram'=>16,'storage'=>'1TB SSD','price'=>149800],
  ['sku'=>'TP-G-STD','slug'=>'tp-g-std','name'=>'TrustPC Gaming Standard','type'=>'desktop','cpu'=>'Ryzen 7 5700X','gpu'=>'RTX 4070','ram'=>32,'storage'=>'1TB SSD','price'=>219800],
  ['sku'=>'TP-G-ADV','slug'=>'tp-g-adv','name'=>'TrustPC Gaming Advance','type'=>'desktop','cpu'=>'Ryzen 7 7800X3D','gpu'=>'RTX 4070 Ti','ram'=>32,'storage'=>'1TB SSD','price'=>249800],
  ['sku'=>'TP-G-MSTR','slug'=>'tp-g-mstr','name'=>'TrustPC Gaming Master','type'=>'desktop','cpu'=>'Ryzen 9 7900X','gpu'=>'RTX 4080','ram'=>64,'storage'=>'2TB SSD','price'=>369800],
  ['sku'=>'TP-G-CORE','slug'=>'tp-g-core','name'=>'TrustPC Core Gamer','type'=>'desktop','cpu'=>'Core i7-14700K','gpu'=>'RTX 4070 Ti','ram'=>32,'storage'=>'1TB SSD','price'=>259800],
  ['sku'=>'TP-G-X','slug'=>'tp-g-x','name'=>'TrustPC Extreme','type'=>'desktop','cpu'=>'Core i9-14900K','gpu'=>'RTX 4080 SUPER','ram'=>64,'storage'=>'2TB SSD','price'=>439800],
  ['sku'=>'TP-G-UP','slug'=>'tp-g-up','name'=>'TrustPC Ultimate Pro','type'=>'desktop','cpu'=>'Core i9-14900KF','gpu'=>'RTX 4090','ram'=>64,'storage'=>'2TB SSD','price'=>499800],
  ['sku'=>'TP-G-CPT','slug'=>'tp-g-cpt','name'=>'TrustPC Compact Gamer','type'=>'desktop','cpu'=>'Ryzen 5 7600','gpu'=>'RTX 4060 Ti','ram'=>32,'storage'=>'1TB SSD','price'=>179800],
  ['sku'=>'TP-G-SIL','slug'=>'tp-g-sil','name'=>'TrustPC Silent Edition','type'=>'desktop','cpu'=>'Ryzen 7 7700','gpu'=>'RTX 4060','ram'=>32,'storage'=>'1TB SSD','price'=>199800],
  ['sku'=>'TP-G-WC','slug'=>'tp-g-wc','name'=>'TrustPC WaterCool Master','type'=>'desktop','cpu'=>'Ryzen 9 7950X','gpu'=>'RTX 4080','ram'=>64,'storage'=>'2TB SSD','price'=>429800],
  ['sku'=>'TP-N-LITE','slug'=>'tp-n-lite','name'=>'TrustPC Note Lite','type'=>'notebook','cpu'=>'Ryzen 5 7535HS','gpu'=>'RTX 4050','ram'=>16,'storage'=>'512GB SSD','price'=>139800],
  ['sku'=>'TP-N-STD','slug'=>'tp-n-std','name'=>'TrustPC Note Standard','type'=>'notebook','cpu'=>'Ryzen 7 7735HS','gpu'=>'RTX 4060','ram'=>16,'storage'=>'1TB SSD','price'=>169800],
  ['sku'=>'TP-N-ADV','slug'=>'tp-n-adv','name'=>'TrustPC Note Advance','type'=>'notebook','cpu'=>'Ryzen 7 7840HS','gpu'=>'RTX 4070','ram'=>32,'storage'=>'1TB SSD','price'=>219800],
  ['sku'=>'TP-N-MSTR','slug'=>'tp-n-mstr','name'=>'TrustPC Note Master','type'=>'notebook','cpu'=>'Core i7-13700H','gpu'=>'RTX 4070','ram'=>32,'storage'=>'1TB SSD','price'=>239800],
  ['sku'=>'TP-N-PRO','slug'=>'tp-n-pro','name'=>'TrustPC Note Pro','type'=>'notebook','cpu'=>'Core i9-13900HX','gpu'=>'RTX 4080','ram'=>32,'storage'=>'2TB SSD','price'=>349800],
  ['sku'=>'TP-S-VAL','slug'=>'tp-s-val','name'=>'TrustPC VALORANT Edition','type'=>'special','cpu'=>'Ryzen 7 7800X3D','gpu'=>'RTX 4070 Ti','ram'=>32,'storage'=>'1TB SSD','price'=>259800],
  ['sku'=>'TP-S-MC','slug'=>'tp-s-mc','name'=>'TrustPC Minecraft Build','type'=>'special','cpu'=>'Ryzen 5 5600','gpu'=>'RTX 4060','ram'=>16,'storage'=>'512GB SSD','price'=>149800],
  ['sku'=>'TP-S-RGB','slug'=>'tp-s-rgb','name'=>'TrustPC RGB Limited','type'=>'special','cpu'=>'Core i7-14700KF','gpu'=>'RTX 4080','ram'=>32,'storage'=>'2TB SSD','price'=>349800],
  ['sku'=>'TP-S-STR','slug'=>'tp-s-str','name'=>'TrustPC Streamer Kit','type'=>'special','cpu'=>'Ryzen 7 7700','gpu'=>'RTX 4060 Ti','ram'=>32,'storage'=>'1TB SSD','price'=>229800],
  ['sku'=>'TP-S-STD','slug'=>'tp-s-std','name'=>'TrustPC Student Model','type'=>'special','cpu'=>'Core i5-13400','gpu'=>'RTX 3050','ram'=>16,'storage'=>'512GB SSD','price'=>109800],
];

/* ========= カスタムオプション ========= */
$OPT_RAM = [
  ['val'=>'16','label'=>'16GB','delta'=>0],
  ['val'=>'32','label'=>'32GB（+¥8,000）','delta'=>8000],
  ['val'=>'64','label'=>'64GB（+¥24,000）','delta'=>24000],
];
$OPT_SSD = [
  ['val'=>'512','label'=>'SSD 512GB（-¥5,000）','delta'=>-5000],
  ['val'=>'1tb','label'=>'SSD 1TB','delta'=>0],
  ['val'=>'2tb','label'=>'SSD 2TB（+¥10,000）','delta'=>10000],
];
$OPT_PSU = [
  ['val'=>'650','label'=>'電源 650W','delta'=>0],
  ['val'=>'750','label'=>'電源 750W（+¥5,000）','delta'=>5000],
  ['val'=>'850','label'=>'電源 850W Gold（+¥12,000）','delta'=>12000],
];
$OPT_COOLER = [
  ['val'=>'air','label'=>'空冷タワー','delta'=>0],
  ['val'=>'240aio','label'=>'簡易水冷 240mm（+¥8,000）','delta'=>8000],
  ['val'=>'360aio','label'=>'簡易水冷 360mm（+¥15,000）','delta'=>15000],
];
$OPT_EXTRA = [
  ['val'=>'none','label'=>'なし','delta'=>0],
  ['val'=>'ssd1','label'=>'追加SSD 1TB（+¥10,000）','delta'=>10000],
  ['val'=>'hdd2','label'=>'追加HDD 2TB（+¥8,000）','delta'=>8000],
];

/* ========= 検索・参照 ========= */
function find_product_by_slug($slug){
  global $PRODUCTS;
  foreach($PRODUCTS as $p){ if($p['slug'] === $slug) return $p; }
  return null;
}
function list_products($filters = []){
  global $PRODUCTS;
  $out = [];
  foreach($PRODUCTS as $p){
    $ok = true;
    foreach($filters as $k=>$v){
      if($v===''||$v===null) continue;
      if($k==='type' && $p['type']!==$v){ $ok=false; break; }
      if($k==='cpu'  && stripos($p['cpu'],$v)===false){ $ok=false; break; }
      if($k==='gpu'  && stripos($p['gpu'],$v)===false){ $ok=false; break; }
      if($k==='max'  && $p['price']>$v){ $ok=false; break; }
      if($k==='min'  && $p['price']<$v){ $ok=false; break; }
    }
    if($ok) $out[] = $p;
  }
  return $out;
}

/* ========= 比較 ========= */
if(!isset($_SESSION['compare'])) $_SESSION['compare'] = [];
function compare_add($slug){
  if(!in_array($slug, $_SESSION['compare'], true)){
    if(count($_SESSION['compare']) < 3) $_SESSION['compare'][] = $slug;
  }
}
function compare_clear(){ $_SESSION['compare'] = []; }
function compare_items(){
  $slugs = $_SESSION['compare'] ?? []; $items = [];
  foreach($slugs as $s){ $p = find_product_by_slug($s); if($p) $items[] = $p; }
  return $items;
}

/* ========= カート ========= */
if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
// item: ['slug','name','base','qty','opts'=>['ram','ssd','psu','cooler','extra']]
function cart_add($slug, $qty, $opts){
  $prod = find_product_by_slug($slug); if(!$prod) return;
  $_SESSION['cart'][] = [
    'slug'=>$slug, 'name'=>$prod['name'], 'base'=>$prod['price'],
    'qty'=>max(1,(int)$qty), 'opts'=>$opts
  ];
}
function price_with_opts($base, $opts){
  global $OPT_RAM,$OPT_SSD,$OPT_PSU,$OPT_COOLER,$OPT_EXTRA;
  $delta=0;
  $maps = ['ram'=>$OPT_RAM,'ssd'=>$OPT_SSD,'psu'=>$OPT_PSU,'cooler'=>$OPT_COOLER,'extra'=>$OPT_EXTRA];
  foreach($maps as $k=>$arr){
    $val = $opts[$k] ?? '';
    foreach($arr as $o){ if($o['val']===$val){ $delta += $o['delta']; break; } }
  }
  return $base + $delta;
}
function cart_total(){
  $sum=0;
  foreach($_SESSION['cart'] as $it){
    $sum += price_with_opts($it['base'],$it['opts']) * $it['qty'];
  }
  return $sum;
}
function cart_clear(){ $_SESSION['cart'] = []; }

/* ========= 注文（セッションのみ） ========= */
if(!isset($_SESSION['orders'])) $_SESSION['orders'] = [];
function order_create($customer){
  $id = (int) (microtime(true)*1000) % 100000000; // 簡易ID
  $_SESSION['orders'][] = [
    'id'=>$id,'items'=>$_SESSION['cart'],'total'=>cart_total(),
    'customer'=>$customer,'created_at'=>date('Y-m-d H:i')
  ];
  cart_clear();
  return $id;
}

/* ========= 認証（セッションのみ） ========= */
function login($name,$email){ $_SESSION['user']=['name'=>$name,'email'=>$email]; }
function logout(){ unset($_SESSION['user']); }
function authed(){ return isset($_SESSION['user']); }

/* ========= POSTアクション ルータ ========= */
$action = $_POST['action'] ?? '';

if ($action === 'add_to_cart') {
  cart_add($_POST['slug'] ?? '', (int)($_POST['qty'] ?? 1), [
    'ram'   => $_POST['ram']    ?? '',
    'ssd'   => $_POST['ssd']    ?? '',
    'psu'   => $_POST['psu']    ?? '',
    'cooler'=> $_POST['cooler'] ?? '',
    'extra' => $_POST['extra']  ?? '',
  ]);
  header('Location: index.php?p=cart'); exit;
}

if ($action === 'compare_add') {
  compare_add($_POST['slug'] ?? '');
  header('Location: index.php?p=compare'); exit;
}

if ($action === 'compare_clear') {
  compare_clear();
  header('Location: index.php?p=compare'); exit;
}

if ($action === 'order_confirm') {
  $name = $_POST['name'] ?? ''; $email = $_POST['email'] ?? ''; $addr = $_POST['addr'] ?? '';
  $id = order_create(['name'=>$name,'email'=>$email,'addr'=>$addr]);
  header('Location: index.php?p=complete&id='.$id); exit;
}

if ($action === 'login') {
  login($_POST['name'] ?? 'User', $_POST['email'] ?? '');
  header('Location: index.php?p=mypage'); exit;
}

if ($action === 'logout') {
  logout();
  header('Location: index.php'); exit;
}
