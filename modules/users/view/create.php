<?php
// modules/users/view/create.php
if (!function_exists('e')) { function e($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); } }
$base = e($_SERVER['PHP_SELF'] ?? 'index.php');
$user = $user ?? null;
?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <strong>Yeni Kullanıcı</strong>
    <a class="btn btn-outline-secondary btn-sm" href="<?= $base ?>?module=users&action=index">Listeye Dön</a>
  </div>
  <div class="card-body">
    <?php include __DIR__ . '/form.php'; ?>
  </div>
</div>
