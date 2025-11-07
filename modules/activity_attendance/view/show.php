<?php
if (!function_exists('e')) {
  function e($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}
$row = $row ?? [];
$id  = (int)($row['id'] ?? 0);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Yoklama Detayı</h4>
  <div>
    <a href="index.php?module=activity_attendance&action=index" class="btn btn-outline-secondary btn-sm">&larr; Listeye Dön</a>
    <a href="index.php?module=activity_attendance&action=edit&id=<?= $id ?>" class="btn btn-warning btn-sm">Düzenle</a>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-3"><div class="text-muted small">Tarih</div><div class="fw-semibold"><?= e($row['attendance_date']) ?></div></div>
      <div class="col-md-3"><div class="text-muted small">Durum</div><div class="fw-semibold"><?= e($row['status']) ?></div></div>
      <div class="col-md-6"><div class="text-muted small">Not</div><div><?= nl2br(e($row['notes'] ?? '')) ?></div></div>

      <div class="col-md-4"><div class="text-muted small">Etkinlik</div>
        <a class="fw-semibold" href="index.php?module=activities&action=show&id=<?= (int)$row['activity_id'] ?>"><?= e($row['activity_title']) ?></a>
      </div>
      <div class="col-md-4"><div class="text-muted small">Öğrenci</div>
        <a class="fw-semibold" href="index.php?module=students&action=show&id=<?= (int)$row['student_id'] ?>"><?= e($row['student_name']) ?></a>
      </div>
      <div class="col-md-4"><div class="text-muted small">Sınıf</div>
        <?php if (!empty($row['class_id'])): ?>
          <a class="fw-semibold" href="index.php?module=classes&action=show&id=<?= (int)$row['class_id'] ?>"><?= e($row['class_name'] ?? '—') ?></a>
        <?php else: ?>
          <span class="fw-semibold text-muted">—</span>
        <?php endif; ?>
      </div>

      <div class="col-md-4"><div class="text-muted small">Kaydı Giren</div>
        <?php if (!empty($row['entry_by_user_id'])): ?>
          <a class="fw-semibold" href="index.php?module=users&action=show&id=<?= (int)$row['entry_by_user_id'] ?>"><?= e($row['entry_by_name'] ?? '—') ?></a>
        <?php else: ?>
          <span class="fw-semibold"><?= e($row['entry_by_name'] ?? '—') ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
