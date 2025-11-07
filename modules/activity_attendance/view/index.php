<?php
if (!function_exists('e')) {
  function e($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}
$rows = $rows ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Etkinlik Yoklamaları</h4>
  <div>
    <a href="index.php?module=activity_attendance&action=take" class="btn btn-primary btn-sm">Yoklama Al</a>
    <a href="index.php?module=activity_attendance&action=report" class="btn btn-outline-secondary btn-sm">Rapor</a>
  </div>
</div>

<?php if (empty($rows)): ?>
  <div class="card"><div class="card-body text-muted">Kayıt bulunamadı.</div></div>
<?php else: ?>
  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-sm align-middle mb-0">
        <thead>
          <tr>
            <th style="width:12%">Tarih</th>
            <th style="width:24%">Etkinlik</th>
            <th style="width:20%">Öğrenci</th>
            <th style="width:10%">Sınıf</th>
            <th style="width:10%">Durum</th>
            <th>Not</th>
            <th style="width:14%">Kaydı Giren</th>
            <th style="width:10%">İşlemler</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): 
            $id  = (int)$r['id'];
            $aid = (int)$r['activity_id'];
            $cid = (int)($r['class_id'] ?? 0);
            $eid = (int)($r['entry_by_user_id'] ?? 0);
          ?>
            <tr>
              <td><?= e($r['attendance_date']) ?></td>
              <td>
                <a href="index.php?module=activities&action=show&id=<?= $aid ?>">
                  <?= e($r['activity_title']) ?>
                </a>
              </td>
              <td>
                <a href="index.php?module=students&action=show&id=<?= (int)$r['student_id'] ?>">
                  <?= e($r['student_name']) ?>
                </a>
              </td>
              <td>
                <?php if ($cid): ?>
                  <a href="index.php?module=classes&action=show&id=<?= $cid ?>">
                    <?= e($r['class_name'] ?? '—') ?>
                  </a>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
              <td>
                <?php
                  $st = (string)($r['status'] ?? '');
                  $cls = ($st==='Geldi' ? 'bg-success' : ($st==='İzinli' ? 'bg-warning text-dark' : 'bg-danger'));
                ?>
                <span class="badge <?= $cls ?>"><?= e($st ?: '—') ?></span>
              </td>
              <td class="text-muted"><?= nl2br(e($r['notes'] ?? '')) ?></td>
              <td>
                <?php if ($eid): ?>
                  <a href="index.php?module=users&action=show&id=<?= $eid ?>"><?= e($r['entry_by_name'] ?? '—') ?></a>
                <?php else: ?>
                  <span class="text-muted"><?= e($r['entry_by_name'] ?? '—') ?></span>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="index.php?module=activity_attendance&action=show&id=<?= $id ?>">Gör</a>
                <a class="btn btn-sm btn-warning" href="index.php?module=activity_attendance&action=edit&id=<?= $id ?>">Düzenle</a>
                <form action="index.php?module=activity_attendance&action=delete" method="post" class="d-inline" onsubmit="return confirm('Bu yoklama kaydı silinsin mi?');">
                  <input type="hidden" name="id" value="<?= $id ?>">
                  <button type="submit" class="btn btn-sm btn-danger">Sil</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
