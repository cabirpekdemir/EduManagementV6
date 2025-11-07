<?php
// modules/activities/view/show.php
if (!function_exists('h')) { 
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); } 
}

$activity = $activity ?? [];
$classes = $classes ?? [];
$csrf_token = $csrf_token ?? '';
$currentRole = currentRole();
$currentUserId = currentUserId();

$canEdit = in_array($currentRole, ['admin', 'teacher']);
if ($currentRole === 'teacher') {
    $canEdit = ((int)($activity['creator_id'] ?? 0) === $currentUserId);
}

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

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">
    <i class="fa fa-calendar"></i> Etkinlik Detay
  </h4>
  <div>
    <!-- STANDART BUTONLAR -->
    <a href="index.php?module=activities&action=index" 
       class="btn btn-sm btn-outline-secondary me-1">
      <i class="fa fa-arrow-left"></i> Liste
    </a>
    
    <?php if ($canEdit): ?>
    <a href="index.php?module=activities&action=edit&id=<?= (int)($activity['id'] ?? 0) ?>" 
       class="btn btn-sm btn-warning me-1">
      <i class="fa fa-edit"></i> Düzenle
    </a>
    
    <form action="index.php?module=activities&action=destroy" 
          method="post" 
          class="d-inline"
          onsubmit="return confirm('Bu etkinliği silmek istediğinize emin misiniz?\n\nİlgili tüm kayıtlar silinecektir.');">
      <input type="hidden" name="id" value="<?= (int)($activity['id'] ?? 0) ?>">
      <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">
      <button type="submit" class="btn btn-sm btn-danger">
        <i class="fa fa-trash"></i> Sil
      </button>
    </form>
    <?php endif; ?>
  </div>
</div>

<div class="row">
  <?php if (!empty($activity['image_path'])): ?>
  <div class="col-md-4 mb-3">
    <div class="card">
      <div class="card-body text-center">
        <h6 class="card-title">Etkinlik Afişi</h6>
        <a href="<?= h(BASE_URL . $activity['image_path']) ?>" target="_blank">
          <img src="<?= h(BASE_URL . $activity['image_path']) ?>" 
               class="img-fluid rounded shadow" 
               alt="<?= h($activity['title']) ?>"
               style="max-height: 300px; object-fit: cover;">
        </a>
      </div>
    </div>
  </div>
  <?php endif; ?>
  
  <div class="<?= !empty($activity['image_path']) ? 'col-md-8' : 'col-md-12' ?>">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12">
            <h3><?= h($activity['title'] ?? '—') ?></h3>
            <hr>
          </div>

          <div class="col-md-6">
            <div class="text-muted small mb-1">
              <i class="fa fa-user"></i> Sorumlu Öğretmen
            </div>
            <div class="fw-semibold"><?= h($activity['teacher_name'] ?? '—') ?></div>
          </div>

          <div class="col-md-6">
            <div class="text-muted small mb-1">
              <i class="fa fa-map-marker"></i> Konum
            </div>
            <div class="fw-semibold"><?= h($activity['location'] ?? '—') ?></div>
          </div>

          <div class="col-md-6">
            <div class="text-muted small mb-1">
              <i class="fa fa-clock-o"></i> Başlangıç
            </div>
            <div class="fw-semibold">
              <?= !empty($activity['start_date']) ? h(date('d.m.Y H:i', strtotime($activity['start_date']))) : '—' ?>
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="text-muted small mb-1">
              <i class="fa fa-clock-o"></i> Bitiş
            </div>
            <div class="fw-semibold">
              <?= !empty($activity['end_date']) ? h(date('d.m.Y H:i', strtotime($activity['end_date']))) : '—' ?>
            </div>
          </div>

          <div class="col-md-12">
            <div class="text-muted small mb-1">
              <i class="fa fa-users"></i> Katılımcı Sınıflar
            </div>
            <?php if (empty($classes)): ?>
              <span class="text-muted">Atanmış sınıf yok</span>
            <?php else: ?>
              <div>
                <?php foreach ($classes as $cl): ?>
                  <span class="badge bg-secondary me-1 mb-1"><?= h($cl['name']) ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="col-12">
            <hr>
            <div class="text-muted small mb-2">
              <i class="fa fa-info-circle"></i> Açıklama
            </div>
            <div class="p-3 bg-light rounded">
              <?php if (!empty($activity['description'])): ?>
                <?= nl2br(h($activity['description'])) ?>
              <?php else: ?>
                <span class="text-muted">Açıklama girilmemiş</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>