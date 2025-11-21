<?php
// DB から指定製品の RAM/SSD の delta を連想配列で返す
function load_option_deltas(PDO $pdo, int $product_id): array {
  $out = ['ram'=>[], 'ssd'=>[]];
  $sql = "
    SELECT g.code, i.val, COALESCE(i.delta,0) AS delta
      FROM option_groups g
      JOIN option_items  i ON i.group_id = g.id
     WHERE g.product_id = ?
  ";
  $st = $pdo->prepare($sql);
  $st->execute([$product_id]);
  foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $code = strtolower($r['code']);
    if ($code === 'ram' || $code === 'ssd') {
      $out[$code][(string)$r['val']] = (int)$r['delta'];
    }
  }
  // フォールバック（テーブル未整備でも動く）
  if (!$out['ram']) $out['ram'] = ['16'=>0, '32'=>20000, '64'=>50000];
  if (!$out['ssd']) $out['ssd'] = ['512'=>-10000, '1tb'=>0, '2tb'=>12000];
  return $out;
}
