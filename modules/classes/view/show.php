<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$class = $class ?? [];
$students = $students ?? [];
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

<div class="mb-3">
    <a href="index.php?module=classes&action=list" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left"></i> Listeye Dön
    </a>
    <a href="index.php?module=classes&action=edit&id=<?= (int)$class['id'] ?>" class="btn btn-primary btn-sm">
        <i class="fa fa-pen"></i> Düzenle
    </a>
    <?php if ($canDelete): ?>
        <a href="index.php?module=classes&action=delete&id=<?= (int)$class['id'] ?>" 
           class="btn btn-danger btn-sm"
           onclick="return confirm('Bu sınıfı silmek istediğinize emin misiniz?')">
            <i class="fa fa-trash"></i> Sil
        </a>
    <?php endif; ?>
</div>

<!-- Sınıf Bilgileri -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="mb-0">Sınıf Bilgileri</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <strong class="text-muted small">Sınıf Adı</strong>
                <div class="fs-5"><?= h($class['name'] ?? '') ?></div>
            </div>
            <div class="col-md-6">
                <strong class="text-muted small">Danışman Öğretmen</strong>
                <div><?= h($class['advisor_name'] ?? '—') ?></div>
            </div>
            <div class="col-12">
                <strong class="text-muted small">Açıklama</strong>
                <div><?= nl2br(h($class['description'] ?? '—')) ?></div>
            </div>
            <div class="col-md-6">
                <strong class="text-muted small">Öğrenci Sayısı</strong>
                <div>
                    <span class="badge bg-info fs-6"><?= count($students) ?> Öğrenci</span>
                </div>
            </div>
            <div class="col-md-6">
                <strong class="text-muted small">Ders Sayısı</strong>
                <div>
                    <span class="badge bg-success fs-6"><?= count($courses) ?> Ders</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dersler -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="mb-0">Atanmış Dersler</h6>
    </div>
    <div class="card-body">
        <?php if (empty($courses)): ?>
            <div class="text-muted">Bu sınıfa atanmış ders bulunmuyor.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Ders Adı</th>
                            <th>Ders Kodu</th>
                            <th>Öğretmen</th>
                            <th class="text-end">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?= h($course['name']) ?></td>
                                <td><?= h($course['code'] ?? '—') ?></td>
                                <td><?= h($course['teacher_name'] ?? '—') ?></td>
                                <td class="text-end">
                                    <a href="index.php?module=courses&action=show&id=<?= (int)$course['id'] ?>" 
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

<!-- Öğrenciler -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="mb-0">Kayıtlı Öğrenciler</h6>
    </div>
    <div class="card-body">
        <?php if (empty($students)): ?>
            <div class="text-muted">Bu sınıfta kayıtlı öğrenci bulunmuyor.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Ad Soyad</th>
                            <th>E-posta</th>
                            <th>TC Kimlik</th>
                            <th class="text-end">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= h($student['name']) ?></td>
                                <td><?= h($student['email']) ?></td>
                                <td><?= h($student['tc_kimlik'] ?? '—') ?></td>
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