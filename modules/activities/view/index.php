<?php
// modules/activities/view/index.php
if (!function_exists('h')) { 
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); } 
}

$activities = $activities ?? [];
$csrf_token = $csrf_token ?? '';

// Flash mesajları
if (isset($_SESSION['form_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= h($_SESSION['form_error']) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php unset($_SESSION['form_error']); endif;

if (isset($_SESSION['form_ok'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= h($_SESSION['form_ok']) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php unset($_SESSION['form_ok']); endif;
?>

<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Etkinlikler</h5>
    <div>
      <?php if (in_array(currentRole(), ['admin', 'teacher'])): ?>
      <a href="index.php?module=activities&action=create" class="btn btn-primary btn-sm">
        <i class="fa fa-plus"></i> Yeni Etkinlik
      </a>
      <?php endif; ?>
      <a href="index.php?module=activities&action=calendar" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-calendar"></i> Takvim
      </a>
    </div>
  </div>
  <div class="card-body">
    <?php if (empty($activities)): ?>
      <div class="alert alert-info">Henüz kayıtlı etkinlik bulunmuyor.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:8%">Afiş</th>
            <th style="width:5%">#</th>
            <th style="width:23%">Başlık</th>
            <th style="width:16%">Öğretmen</th>
            <th style="width:18%">Sınıflar</th>
            <th style="width:16%">Tarih</th>
            <th class="text-end" style="width:14%">İşlemler</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($activities as $row): ?>
            <tr>
              <td>
                <?php if (!empty($row['image_path'])): ?>
                  <a href="index.php?module=activities&action=view&id=<?= (int)$row['id'] ?>">
                    <img src="<?= h(BASE_URL . $row['image_path']) ?>" 
                         alt="<?= h($row['title']) ?>"
                         class="rounded"
                         style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;"
                         title="Etkinliği görüntüle">
                  </a>
                <?php else: ?>
                  <div class="bg-light rounded d-flex align-items-center justify-content-center"
                       style="width: 50px; height: 50px;">
                    <i class="fa fa-image text-muted"></i>
                  </div>
                <?php endif; ?>
              </td>
              <td><?= h($row['id']) ?></td>
              <td>
                <strong><?= h($row['title']) ?></strong>
                <?php if (!empty($row['location'])): ?>
                  <br><small class="text-muted"><i class="fa fa-map-marker"></i> <?= h($row['location']) ?></small>
                <?php endif; ?>
              </td>
              <td><?= h($row['teacher_name'] ?? '—') ?></td>
              <td>
                <?php if (!empty($row['classes'])): ?>
                  <?php foreach ($row['classes'] as $cl): ?>
                    <span class="badge bg-secondary me-1"><?= h($cl['class_name']) ?></span>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
              <td>
                <small>
                  <?php 
                    if (!empty($row['start_date'])) {
                        echo h(date('d.m.Y H:i', strtotime($row['start_date'])));
                        if (!empty($row['end_date']) && $row['end_date'] !== $row['start_date']) {
                            echo '<br>→ ' . h(date('d.m.Y H:i', strtotime($row['end_date'])));
                        }
                    } else {
                        echo '—';
                    }
                  ?>
                </small>
              </td>
              <td class="text-end">
                <!-- STANDART BUTONLAR -->
                <a href="index.php?module=activities&action=view&id=<?= (int)$row['id'] ?>"
                   class="btn btn-sm btn-outline-primary me-1" 
                   title="Görüntüle">
                  <i class="fa fa-eye"></i> Gör
                </a>
                
                <?php if (in_array(currentRole(), ['admin', 'teacher'])): ?>
                <a href="index.php?module=activities&action=edit&id=<?= (int)$row['id'] ?>" 
                   class="btn btn-sm btn-warning me-1"
                   title="Düzenle">
                  <i class="fa fa-edit"></i> Düzenle
                </a>
                
                <form action="index.php?module=activities&action=delete"
                      method="post" 
                      class="d-inline"
                      onsubmit="return confirm('Bu etkinliği silmek istediğinize emin misiniz?');">
                  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                  <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">
                  <button type="submit" class="btn btn-sm btn-danger" title="Sil">
                    <i class="fa fa-trash"></i> Sil
                  </button>
                </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<style>
td img:hover {
  transform: scale(1.1);
  transition: transform 0.2s ease;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
</style>