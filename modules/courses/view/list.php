<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$courses = $courses ?? [];
$canDelete = $canDelete ?? false;

// Flash mesajlar
if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= h($_SESSION['flash_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); endif;

if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= h($_SESSION['flash_error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); endif;
?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Dersler</h5>
        <div>
            <a href="index.php?module=courses&action=schedule" class="btn btn-info btn-sm">
                <i class="fa fa-calendar"></i> Ders Programı
            </a>
            <a href="index.php?module=courses&action=create" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Yeni Ders
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <?php if (empty($courses)): ?>
            <div class="text-center text-muted py-5">
                <i class="fa fa-book fa-3x mb-3"></i>
                <p>Kayıt bulunamadı.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Ders Adı</th>
                            <th style="width:150px">Kademe</th>
                            <th>Öğretmen</th>
                            <th style="width:120px">Atanan Sınıflar</th>
                            <th class="text-end" style="width:200px">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?= (int)$course['id'] ?></td>
                                <td>
                                    <div class="fw-semibold"><?= h($course['name']) ?></div>
                                    <?php if (!empty($course['description'])): ?>
                                        <small class="text-muted">
                                            <?= h(mb_substr($course['description'], 0, 50)) ?>
                                            <?= mb_strlen($course['description']) > 50 ? '...' : '' ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $catNames = [
                                        'ilkokul' => ['text' => 'İlkokul', 'color' => 'primary'],
                                        'ortaokul' => ['text' => 'Ortaokul', 'color' => 'info'],
                                        'lise' => ['text' => 'Lise', 'color' => 'danger']
                                    ];
                                    if (!empty($course['category'])): 
                                        $cat = $catNames[$course['category']] ?? ['text' => $course['category'], 'color' => 'secondary'];
                                    ?>
                                        <span class="badge bg-<?= $cat['color'] ?>"><?= h($cat['text']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= h($course['teacher_name'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($course['class_count'])): ?>
                                        <span class="badge bg-secondary"><?= (int)$course['class_count'] ?> Sınıf</span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="index.php?module=courses&action=show&id=<?= (int)$course['id'] ?>" 
                                       class="btn btn-sm btn-outline-info" title="Görüntüle">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="index.php?module=courses&action=edit&id=<?= (int)$course['id'] ?>" 
                                       class="btn btn-sm btn-outline-warning" title="Düzenle">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <?php if ($canDelete): ?>
                                        <a href="index.php?module=courses&action=delete&id=<?= (int)$course['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger" title="Sil"
                                           onclick="return confirm('Bu dersi silmek istediğinize emin misiniz?')">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($courses)): ?>
        <div class="card-footer bg-light">
            <div class="text-muted small">
                Toplam <strong><?= count($courses) ?></strong> ders
            </div>
        </div>
    <?php endif; ?>
</div>