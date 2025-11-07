<?php
// modules/activities/view/create.php
$title = $title ?? 'Yeni Etkinlik';
$isEdit = false;
$csrf_token = $csrf_token ?? '';
$formAction = $formAction ?? '';
?>

<div class="card shadow-sm">
  <div class="card-header">
    <h5 class="mb-0"><?= htmlspecialchars($title) ?></h5>
  </div>
  <div class="card-body">
    <form action="<?= htmlspecialchars($formAction) ?>" method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
      <?php include __DIR__ . '/form.php'; ?>
      <div class="col-12 text-end mt-4">
        <a href="index.php?module=activities&action=index" class="btn btn-outline-secondary">
          <i class="fa fa-times"></i> Vazgeç
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="fa fa-save"></i> Kaydet
        </button>
      </div>
    </form>
  </div>
</div>