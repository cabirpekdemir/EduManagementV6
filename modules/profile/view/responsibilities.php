<?php
// modules/profile/view/responsibilities.php
if (!function_exists('e')) { function e($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); } }
$base     = e($_SERVER['PHP_SELF'] ?? 'index.php');

$teacher  = $teacher  ?? ['name'=>'Öğretmen'];
$classes  = $classes  ?? [];
$subjects = $subjects ?? [];
$groups   = $groups   ?? [];
$requests = $requests ?? [];
?>

<section class="content-header d-flex justify-content-between align-items-center">
  <h1>Danışmanlık &amp; Sorumluluk</h1>
  <span class="text-muted"><?= e($teacher['name'] ?? '') ?></span>
</section>

<section class="content">
  <!-- Danışmanı olduğu sınıflar -->
  <div class="card">
    <div class="card-header"><strong>Danışmanı Olduğu Sınıflar</strong></div>
    <div class="card-body">
      <?php if (!$classes): ?>
        <p class="text-muted mb-0">Kayıtlı sınıf yok.</p>
      <?php else: ?>
        <ul class="ms-3">
          <?php foreach ($classes as $c): ?>
            <li>
              <a class="text-decoration-none"
                 href="<?= $base ?>?module=students&action=index&class_id=<?= (int)$c['id'] ?>">
                <?= e($c['label'] ?? ('Sınıf #'.(int)$c['id'])) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
        <small class="text-muted">Sınıf adına tıklayınca o sınıfın öğrenci listesi açılır.</small>
      <?php endif; ?>
    </div>
  </div>

  <div class="row">
    <!-- Verdiği dersler -->
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header">
          <strong>Sorumlu Olduğu Dersler</strong>
          <?php if ($subjects): ?><span class="badge bg-secondary ms-2"><?= count($subjects) ?></span><?php endif; ?>
        </div>
        <div class="card-body">
          <?php if (!$subjects): ?>
            <p class="text-muted mb-0">Kayıtlı ders yok.</p>
          <?php else: ?>
            <ul class="ms-3 mb-0">
              <?php foreach ($subjects as $s): ?>
                <li><?= e($s['label'] ?? ('Ders #'.(int)$s['id'])) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Ders grupları -->
    <div class="col-md-6 mt-3 mt-md-0">
      <div class="card h-100">
        <div class="card-header">
          <strong>Sorumlu Olduğu Ders Grupları</strong>
          <?php if ($groups): ?><span class="badge bg-secondary ms-2"><?= count($groups) ?></span><?php endif; ?>
        </div>
        <div class="card-body">
          <?php if (!$groups): ?>
            <p class="text-muted mb-0">Kayıtlı grup yok.</p>
          <?php else: ?>
            <ul class="ms-3 mb-0">
              <?php foreach ($groups as $g): ?>
                <li><?= e($g['label'] ?? ('Grup #'.(int)$g['id'])) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Son talepler -->
  <div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Son Talepler</strong>
      <a class="btn btn-sm btn-outline-primary" href="<?= $base ?>?module=requests&action=index">Tüm Talepler</a>
    </div>
    <div class="card-body">
      <?php if (!$requests): ?>
        <p class="text-muted mb-0">Gösterilecek talep yok.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm table-striped align-middle mb-0">
            <thead>
              <tr>
                <th style="width:80px">#</th>
                <th>Başlık</th>
                <th style="width:120px">Durum</th>
                <th style="width:180px">Oluşturma</th>
                <th style="width:120px">İşlem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($requests as $r): ?>
                <tr>
                  <td>#<?= (int)$r['id'] ?></td>
                  <td><?= e($r['title'] ?? '—') ?></td>
                  <td><?= e($r['status'] ?? '—') ?></td>
                  <td><?= e($r['created_at'] ?? '—') ?></td>
                  <td>
                    <a class="btn btn-sm btn-outline-primary"
                       href="<?= $base ?>?module=requests&action=show&id=<?= (int)$r['id'] ?>">Gör</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
