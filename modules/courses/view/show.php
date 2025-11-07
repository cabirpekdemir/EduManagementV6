<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$course = $course ?? [];
$classes = $classes ?? [];
$students = $students ?? [];
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

<div class="mb-3">
    <a href="index.php?module=courses&action=list" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left"></i> Listeye Dön
    </a>
    <a href="index.php?module=courses&action=edit&id=<?= (int)$course['id'] ?>" class="btn btn-primary btn-sm">
        <i class="fa fa-pen"></i> Düzenle
    </a>
    <?php if ($canDelete): ?>
        <a href="index.php?module=courses&action=delete&id=<?= (int)$course['id'] ?>" 
           class="btn btn-danger btn-sm"
           onclick="return confirm('Bu dersi silmek istediğinize emin misiniz?')">
            <i class="fa fa-trash"></i> Sil
        </a>
    <?php endif; ?>
</div>

<!-- Ders Bilgileri -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="mb-0">Ders Detayları</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <strong class="text-muted small">Ders Adı</strong>
                <div class="fs-5"><?= h($course['name'] ?? '') ?></div>
            </div>
            <div class="col-md-6">
                <strong class="text-muted small">Kademe</strong>
                <div>
                    <?php 
                    $catNames = [
                        'ilkokul' => ['text' => 'İlkokul (1-4. Sınıf)', 'color' => 'primary'],
                        'ortaokul' => ['text' => 'Ortaokul (5-8. Sınıf)', 'color' => 'info'],
                        'ortaokul_1' => ['text' => 'Ortaokul I. Kademe (5-6. Sınıf)', 'color' => 'success'],
                        'ortaokul_2' => ['text' => 'Ortaokul II. Kademe (7-8. Sınıf)', 'color' => 'warning'],
                        'lise' => ['text' => 'Lise (9-12. Sınıf)', 'color' => 'danger']
                    ];
                    if (!empty($course['category'])): 
                        $cat = $catNames[$course['category']] ?? ['text' => $course['category'], 'color' => 'secondary'];
                    ?>
                        <span class="badge bg-<?= $cat['color'] ?> fs-6"><?= h($cat['text']) ?></span>
                    <?php else: ?>
                        <span class="text-muted">Belirtilmemiş</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <strong class="text-muted small">Öğretmen</strong>
                <div><?= h($course['teacher_name'] ?? '—') ?></div>
            </div>
            <div class="col-md-6">
                <strong class="text-muted small">Kredi</strong>
                <div>
                    <?= !empty($course['credits']) 
                        ? '<span class="badge bg-dark fs-6">' . (int)$course['credits'] . '</span>' 
                        : '—' ?>
                </div>
            </div>
            <div class="col-12">
                <strong class="text-muted small">Açıklama</strong>
                <div><?= nl2br(h($course['description'] ?? '—')) ?></div>
            </div>
            <div class="col-md-6">
                <strong class="text-muted small">Durum</strong>
                <div>
                    <?= !empty($course['is_active']) 
                        ? '<span class="badge bg-success">Aktif</span>' 
                        : '<span class="badge bg-secondary">Pasif</span>' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Atanmış Sınıflar -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="mb-0">Atanmış Sınıflar</h6>
    </div>
    <div class="card-body">
        <?php if (empty($classes)): ?>
            <div class="text-muted">Bu derse atanmış sınıf bulunmuyor.</div>
        <?php else: ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($classes as $class): ?>
                    <span class="badge bg-primary fs-6 px-3 py-2">
                        <?= h($class['name']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Öğrenciler -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="mb-0">Kayıtlı Öğrenciler</h6>
    </div>
    <div class="card-body">
        <?php if (empty($students)): ?>
            <div class="text-muted">Bu derse kayıtlı öğrenci bulunmuyor.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Ad Soyad</th>
                            <th>E-posta</th>
                            <th>Sınıf</th>
                            <th class="text-end">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= h($student['name']) ?></td>
                                <td><?= h($student['email']) ?></td>
                                <td><?= h($student['class_name'] ?? '—') ?></td>
                                <td class="text-end">
                                    <a href="index.php?module=students&action=show&id=<?= (int)$student['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        Görüntüle
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>