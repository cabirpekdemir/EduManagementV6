<?php
if (!function_exists('h')) { function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); } }
$rows = $rows ?? [];
?>
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Yoklama Raporu</h5>
    <a href="index.php?module=activity_attendance&action=index" class="btn btn-outline-secondary btn-sm">&larr; Liste</a>
  </div>
  <div class="card-body">
    <?php if (empty($rows)): ?>
      <div class="text-muted">Rapor oluşturulacak veri bulunamadı.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th style="width:10%">Etkinlik #</th>
              <th>Etkinlik</th>
              <th style="width:12%">Toplam</th>
              <th style="width:12%">Geldi</th>
              <th style="width:12%">Gelmedi</th>
              <th style="width:12%">İzinli</th>
              <th style="width:22%">Tarih Aralığı</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= h($r['activity_id'] ?? '') ?></td>
                <td><?= h($r['activity_title'] ?? '') ?></td>
                <td><?= h($r['total'] ?? 0) ?></td>
                <td><?= h($r['geldi'] ?? 0) ?></td>
                <td><?= h($r['gelmedi'] ?? 0) ?></td>
                <td><?= h($r['izinli'] ?? 0) ?></td>
                <td><?= h(($r['first_date'] ?? '').(($r['last_date'] ?? '') && ($r['last_date'] !== $r['first_date']) ? ' — '.$r['last_date'] : '')) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
