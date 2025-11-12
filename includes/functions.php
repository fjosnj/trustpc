<?php
function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

function isLoggedIn() { return !empty($_SESSION['customer']); }

function requireLogin() {
  if (!isLoggedIn()) {
    header('Location: /login.php');
    exit;
  }
}
