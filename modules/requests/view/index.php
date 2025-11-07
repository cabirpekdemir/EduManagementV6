<?php
/** DATA VARS (controller'dan gelmeli)
 * @var array $requests
 * @var array $currentUser
 */
$requests     = $requests     ?? [];
$currentUser  = $currentUser  ?? ['role'=>'guest','id'=>0];
$base = htmlspecialchars($_SERVER['PHP_SELF'] ?? '/index.php', ENT_QUOTES, 'UTF-8');
$canCreate = true;

function col($row, $key, $fallback = '—') {
  return isset($row[$key]) && $row[$key] !== '' ? htmlspecialchars((string)$row[$key]) : $fallback;
}

$role = strtolower($currentUser['role'] ?? 'guest');
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);

// Router giriş dosyası (alt klasörde olsa bile doğru yolu verir)
$base  = htmlspecialchars($_SERVER['PHP_SELF'] ?? '/index.php', ENT_QUOTES, 'UTF-8');

// Görünürlükler
$canCreate = str_contains($role, 'admin') || $role === 'parent' || $role === 'student';

$sample      = $requests[0] ?? [];
$hasStatus   = array_key_exists('status', $sample);
$hasPriority = array_key_exists('priority', $sample);
$hasType     = array_key_exists('type', $sample);
$hasStudent  = array_key_exists('student_name', $sample) || array_key_exists('student_id', $sample);
$hasParent   = array_key_exists('parent_name',  $sample) || array_key_exists('parent_id',  $sample);
$hasCreatedBy= array_key_exists('created_by_name', $sample) || array_key_exists('created_by', $sample);
?>
<section class="content-header">
  <div class="d-flex justify-content-between align-items-center">
    <h1>Talepler</h1>
    <div class="btn-group">
      <a href="<?= $base ?>?module=requests&action=create" class="btn btn-primary">
        <i class="fa fa-plus"></i> Oluştur
      </a>
      <a href="<?= $base ?>?module=requests&action=index&ts=<?= time() ?>" class="btn btn-outline-secondary">
        <i class="fa fa-refresh"></i> Yeniden Yükle
      </a>
    </div>
  </div>
</section>


<section class="content">
  <?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'info') ?>">
      <?= htmlspecialchars($flash['message'] ?? '') ?>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body table-responsive p-0">
      <table class="table table-hover table-striped mb-0">
        <thead>
        <tr>
          <th>#</th>
          <?php if ($hasType): ?><th>Tür</th><?php endif; ?>
          <th>Başlık</th>
          <?php if ($hasStatus): ?><th>Durum</th><?php endif; ?>
          <?php if ($hasPriority): ?><th>Öncelik</th><?php endif; ?>
          <?php if ($hasStudent): ?><th>Öğrenci</th><?php endif; ?>
          <?php if ($hasParent): ?><th>Veli</th><?php endif; ?>
          <?php if ($hasCreatedBy): ?><th>Oluşturan</th><?php endif; ?>
          <th>Oluşturma</th>
          <th style="width:110px">İşlem</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($requests)): ?>
          <tr><td colspan="99" class="text-center text-muted">Kayıt bulunamadı.</td></tr>
        <?php else: foreach ($requests as $r): ?>
          <tr>
            <td><?= col($r,'id') ?></td>
            <?php if ($hasType): ?><td><?= col($r,'type') ?></td><?php endif; ?>
            <td><?= col($r,'title') ?></td>
            <?php if ($hasStatus): ?>
              <td>
                <span class="badge bg-<?= ($r['status']??'')==='closed'?'secondary':(($r['status']??'')==='in_progress'?'warning':'success') ?>">
                  <?= col($r,'status','—') ?>
                </span>
              </td>
            <?php endif; ?>
            <?php if ($hasPriority): ?><td><?= col($r,'priority') ?></td><?php endif; ?>
            <?php if ($hasStudent): ?><td><?= htmlspecialchars($r['student_name'] ?? ('#'.($r['student_id']??'—'))) ?></td><?php endif; ?>
            <?php if ($hasParent): ?><td><?= htmlspecialchars($r['parent_name'] ?? ('#'.($r['parent_id']??'—'))) ?></td><?php endif; ?>
            <?php if ($hasCreatedBy): ?><td><?= htmlspecialchars($r['created_by_name'] ?? ('#'.($r['created_by']??'—'))) ?></td><?php endif; ?>
            <td><?= col($r,'created_at') ?></td>
            <td>
              <a class="btn btn-sm btn-outline-primary"
                 href="<?= $base ?>?module=requests&action=show&id=<?= urlencode($r['id']) ?>">
                Gör
              </a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
