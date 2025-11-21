<?php
// /2025/trustpc/lib/db_repo.php
require_once __DIR__ . '/../includes/db_connect.php'; // ← ここで $pdo が使える

function db_list_products(PDO $pdo): array {
  $sql = "SELECT id, sku, slug, name, cpu, gpu, ram, storage, price, image_url
          FROM products WHERE is_active=1 ORDER BY id DESC";
  return $pdo->query($sql)->fetchAll();
}

function db_find_product(PDO $pdo, string $key): ?array {
  $st = $pdo->prepare(
    "SELECT * FROM products
     WHERE slug = :k OR sku = :k OR LOWER(slug)=LOWER(:k) OR LOWER(sku)=LOWER(:k)
     LIMIT 1"
  );
  $st->execute([':k'=>$key]);
  $row = $st->fetch();
  return $row ?: null;
}

function db_product_option_groups(PDO $pdo, int $product_id): array {
  $sql = "SELECT pog.id, pog.option_group_id, cog.code, cog.name, cog.selection_type, cog.help_text
          FROM product_option_groups pog
          JOIN custom_option_groups cog ON cog.id = pog.option_group_id
          WHERE pog.product_id = ?";
  $st = $pdo->prepare($sql);
  $st->execute([$product_id]);
  $groups = $st->fetchAll();

  $sqlC = "SELECT c.id, c.code, c.label, c.price_diff, c.is_default, c.sort_order,
                  pco.override_price_diff
           FROM custom_choices c
           LEFT JOIN product_choice_overrides pco
             ON pco.choice_id = c.id AND pco.product_id = ?
           WHERE c.option_group_id = ?
           ORDER BY c.sort_order, c.id";
  foreach ($groups as &$g) {
    $st2 = $pdo->prepare($sqlC);
    $st2->execute([$product_id, $g['option_group_id']]);
    $g['choices'] = $st2->fetchAll();
  }
  return $groups;
}
