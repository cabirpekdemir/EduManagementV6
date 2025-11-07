<?php
if (!function_exists('h')) { function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); } }
$activities = $activities ?? [];
$students   = $students ?? [];
$classes    = $classes ?? [];
$action     = $formAction ?? 'index.php?module=activity_attendance&action=store_take';
$today      = $today ?? date('Y-m-d');
?>
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Yoklama Al</h5>
    <a href="index.php?module=activity_attendance&action=index" class="btn btn-outline-secondary btn-sm">&larr; Liste</a>
  </div>
  <div class="card-body">
    <form action="<?= h($action) ?>" method="post">
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Etkinlik</label>
          <select name="activity_id" class="form-select" required>
            <option value="">Seçiniz…</option>
            <?php foreach ($activities as $a): ?>
              <option value="<?= (int)$a['id'] ?>"><?= h($a['title'] ?? ('#'.$a['id'])) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Tarih</label>
          <input type="date" name="attendance_date" class="form-control" value="<?= h($today) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Sınıf (opsiyonel)</label>
          <select name="class_id" class="form-select">
            <option value="">—</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= h($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <?php if (empty($students)): ?>
        <div class="text-muted">Öğrenci bulunamadı.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th style="width:4%"><input type="checkbox" onclick="document.querySelectorAll('.chk-stu').forEach(c=>c.checked=this.checked)"></th>
                <th>Öğrenci</th>
                <th style="width:22%">Durum</th>
                <th style="width:30%">Not</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($students as $s): $sid=(int)$s['id']; ?>
                <tr>
                  <td><input type="checkbox" class="chk-stu" name="student_id[]" value="<?= $sid ?>"></td>
                  <td><?= h($s['name'] ?? ('#'.$sid)) ?></td>
                  <td>
                    <select name="status[<?= $sid ?>]" class="form-select form-select-sm">
                      <option value="Geldi">Geldi</option>
                      <option value="Gelmedi">Gelmedi</option>
                      <option value="İzinli">İzinli</option>
                    </select>
                  </td>
                  <td><input type="text" name="notes[<?= $sid ?>]" class="form-control form-control-sm" placeholder="Not (opsiyonel)"></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="text-end">
          <button type="submit" class="btn btn-primary">Kaydet</button>
        </div>
      <?php endif; ?>
    </form>
  </div>
</div>
