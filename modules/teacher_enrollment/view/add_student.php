<?php
// modules/teacher_enrollment/view/add_student.php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$course = $course ?? [];
$students = $students ?? [];
$enrolled = $enrolled ?? [];
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

<div class="row mb-3">
    <div class="col-md-8">
        <h4>
            <i class="fa fa-user-plus"></i> Öğrenci Yönetimi
            <small class="text-muted">- <?= h($course['name']) ?></small>
        </h4>
    </div>
    <div class="col-md-4 text-end">
        <a href="index.php?module=teacher_enrollment&action=index" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Derslerime Dön
        </a>
    </div>
</div>

<div class="row">
    <!-- Kayıtlı Öğrenciler -->
    <div class="col-md-6">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fa fa-users"></i> Kayıtlı Öğrenciler 
                    <span class="badge bg-light text-dark"><?= count($enrolled) ?></span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($enrolled)): ?>
                    <div class="p-3 text-center text-muted">
                        <i class="fa fa-info-circle"></i> Henüz öğrenci eklenmemiş
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($enrolled as $st): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= h($st['name']) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        Sınıf: <?= h($st['sinif'] ?? '-') ?> | 
                                        <?= h($st['semester_year']) ?> - <?= h($st['semester_period']) ?>. Dönem
                                        <?php if ($st['enrolled_by_teacher']): ?>
                                            <span class="badge bg-warning">Öğretmen Ekledi</span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <form method="post" action="index.php?module=teacher_enrollment&action=remove_student" class="d-inline">
                                    <input type="hidden" name="enrollment_id" value="<?= (int)$st['id'] ?>">
                                    <input type="hidden" name="course_id" value="<?= (int)$course['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Bu öğrenciyi dersten çıkarmak istediğinize emin misiniz?');">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Öğrenci Ekle -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-plus-circle"></i> Öğrenci Ekle</h5>
            </div>
            <div class="card-body">
                <form method="post" action="index.php?module=teacher_enrollment&action=store_student">
                    <input type="hidden" name="course_id" value="<?= (int)$course['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Öğrenci Seç</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">-- Öğrenci Seçiniz --</option>
                            <?php foreach ($students as $st): ?>
                                <option value="<?= (int)$st['id'] ?>">
                                    <?= h($st['name']) ?> (<?= h($st['sinif'] ?? '-') ?> - <?= h($st['okul'] ?? '-') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Dönem Yılı</label>
                        <input type="text" name="semester_year" class="form-control" 
                               value="2024-2025" required 
                               placeholder="Örn: 2024-2025">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Dönem</label>
                        <select name="semester_period" class="form-select" required>
                            <option value="1">1. Dönem</option>
                            <option value="2">2. Dönem</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-save"></i> Öğrenciyi Ekle
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>