<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$classes = $classes ?? [];
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
        <h5 class="mb-0">Sınıflar</h5>
        <a href="index.php?module=classes&action=create" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Yeni Sınıf
        </a>
    </div>

    <div class="card-body p-0">
        <?php if (empty($classes)): ?>
            <div class="text-center text-muted py-5">
                Kayıt bulunamadı.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Sınıf Adı</th>
                            <th>Danışman Öğretmen</th>
                            <th>Öğrenci Sayısı</th>
                            <th>Açıklama</th>
                            <th class="text-end" style="width:200px">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $class): ?>
                            <tr>
                                <td><?= (int)$class['id'] ?></td>
                                <td>
                                    <div class="fw-semibold"><?= h($class['name']) ?></div>
                                </td>
                                <td><?= h($class['advisor_name'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($class['student_count'])): ?>
                                        <span class="badge bg-info"><?= (int)$class['student_count'] ?> Öğrenci</span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($class['description'])): ?>
                                        <?= h(mb_substr($class['description'], 0, 50)) ?>
                                        <?= mb_strlen($class['description']) > 50 ? '...' : '' ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="index.php?module=classes&action=show&id=<?= (int)$class['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        Gör
                                    </a>
                                    <a href="index.php?module=classes&action=edit&id=<?= (int)$class['id'] ?>" 
                                       class="btn btn-sm btn-outline-warning">
                                        Düzenle
                                    </a>
                                    <?php if ($canDelete): ?>
                                        <a href="index.php?module=classes&action=delete&id=<?= (int)$class['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Bu sınıfı silmek istediğinize emin misiniz?')">
                                            Sil
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
</div>