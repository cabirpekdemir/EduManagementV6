<?php
// modules/teacher_enrollment/view/index.php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$courses = $courses ?? [];
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

<h4 class="mb-4"><i class="fa fa-chalkboard-teacher"></i> Derslerim</h4>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($courses)): ?>
            <div class="p-4 text-center text-muted">
                <i class="fa fa-info-circle"></i> Size atanmış ders bulunmuyor.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ders Adı</th>
                            <th>Kategori</th>
                            <th>Öğrenci Sayısı</th>
                            <th class="text-end">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><strong><?= h($course['name']) ?></strong></td>
                            <td>
                                <?php if ($course['course_category'] === 'akademi'): ?>
                                    <span class="badge bg-info">Akademi</span>
                                <?php elseif ($course['course_category'] === 'proje'): ?>
                                    <span class="badge bg-warning">Proje</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?= (int)$course['student_count'] ?> Öğrenci</span>
                            </td>
                            <td class="text-end">
                                <a href="index.php?module=teacher_enrollment&action=add_student&course_id=<?= (int)$course['id'] ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fa fa-user-plus"></i> Öğrenci Ekle/Çıkar
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