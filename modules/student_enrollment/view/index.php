<?php
// modules/student_enrollment/view/index.php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$student = $student ?? [];
$semester = $semester ?? [];
$my_enrollments = $my_enrollments ?? [];
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h4>
            <i class="fa fa-book"></i> Derslerim
            <small class="text-muted">(<?= h($semester['label'] ?? '') ?>)</small>
        </h4>
    </div>
    <div class="col-md-4 text-end">
        <a href="index.php?module=student_enrollment&action=history" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-history"></i> Ders Geçmişim
        </a>
    </div>
</div>

<div class="alert alert-info">
    <i class="fa fa-info-circle"></i> Dersleriniz öğretmenleriniz tarafından atanmaktadır. 
    Ders ekleme/çıkarma için öğretmeninizle iletişime geçiniz.
</div>

<!-- Atanmış Dersler -->
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fa fa-list"></i> Kayıtlı Olduğum Dersler</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($my_enrollments)): ?>
            <div class="p-4 text-center text-muted">
                <i class="fa fa-inbox"></i> Size henüz ders atanmamış.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ders Adı</th>
                            <th>Öğretmen</th>
                            <th>Kategori</th>
                            <th>Sınıflar</th>
                            <th>Ders Saatleri</th>
                            <th>Dönem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($my_enrollments as $enroll): ?>
                        <tr>
                            <td><strong><?= h($enroll['course_name']) ?></strong></td>
                            <td><?= h($enroll['teacher_name'] ?? '-') ?></td>
                            <td>
                                <?php if ($enroll['course_category'] === 'akademi'): ?>
                                    <span class="badge bg-info">Akademi</span>
                                <?php elseif ($enroll['course_category'] === 'proje'): ?>
                                    <span class="badge bg-warning">Proje</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td><small><?= h($enroll['class_names'] ?? '-') ?></small></td>
                            <td><small><?= h($enroll['time_slots'] ?? '-') ?></small></td>
                            <td>
                                <small>
                                    <?= h($enroll['semester_year']) ?><br>
                                    <?= h($enroll['semester_period']) ?>. Dönem
                                </small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>