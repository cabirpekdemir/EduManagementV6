<?php
if (!function_exists('e')) {
  function e($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}
$row = $row ?? [];
$id  = (int)($row['id'] ?? 0);
$st  = (string)($row['status'] ?? 'Geldi');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Yoklama Düzenle</h4>
  <a href="index.php?module=activity_attendance&action=index" class="btn btn-outline-secondary btn-sm">&larr; Listeye Dön</a>
</div>

<div class="card">
  <div class="card-body">
    <form method="post" action="index.php?module=activity_attendance&action=update">
      <input type="hidden" name="id" value="<?= $id ?>">

      <div class="mb-3">
        <label class="form-label">Durum</label>
        <select name="status" class="form-select form-select-sm" required>
          <option value="Geldi"   <?= $st==='Geldi'   ? 'selected':'' ?>>Geldi</option>
          <option value="Gelmedi" <?= $st==='Gelmedi' ? 'selected':'' ?>>Gelmedi</option>
          <option value="İzinli"  <?= $st==='İzinli'  ? 'selected':'' ?>>İzinli</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Not</label>
        <textarea name="notes" class="form-control" rows="4"><?= e($row['notes'] ?? '') ?></textarea>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Kaydet</button>
        <a href="index.php?module=activity_attendance&action=show&id=<?= $id ?>" class="btn btn-outline-secondary">İptal</a>
      </div>
    </form>
  </div>
</div>
