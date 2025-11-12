<?php
require __DIR__ . "/includes/admin_db_connect.php";
$id = $_POST['id'] ?? ''; $name=$_POST['name'] ?? ''; $price=intval($_POST['price'] ?? 0); $spec=$_POST['spec'] ?? '{}';
if ($id && $name && $price>0) {
  $stmt = $pdo->prepare("INSERT INTO products(id,name,price,spec) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name), price=VALUES(price), spec=VALUES(spec)");
  $stmt->execute([$id,$name,$price,$spec]);
  header('Location: /admin/product_manage.php'); exit;
}
echo "入力が不足しています。";
