<?php
// modules/teacher_enrollment/view/bulk_assign.php - YENİ SAYFA
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$courses = $courses ?? [];
$students = $students ?? [];
$csrf_token = $csrf_token ?? '';
?>
<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$courses = $courses ?? [];
$students = $students ?? [];
$selectedStage = $selectedStage ?? '';
$csrf_token = $csrf_token ?? '';

// Flash mesajlar
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

<h4 class="mb-4"><i class="fa fa-users-cog"></i> Toplu Ders Atama</h4>

<!-- KADEME FİLTRESİ -->
<div class="card shadow-sm mb-3">
    <div class="card-header bg-info text-white">
        <h6 class="mb-0"><i class="fa fa-filter"></i> Kademe Seçin</h6>
    </div>
    <div class="card-body">
        <div class="btn-group" role="group">
            <a href="?module=teacher_enrollment&action=bulk_assign" 
               class="btn <?= $selectedStage === '' ? 'btn-primary' : 'btn-outline-primary' ?>">
                Tümü
            </a>
            <a href="?module=teacher_enrollment&action=bulk_assign&stage=primary" 
               class="btn <?= $selectedStage === 'primary' ? 'btn-primary' : 'btn-outline-primary' ?>">
                İlkokul
            </a>
            <a href="?module=teacher_enrollment&action=bulk_assign&stage=middle" 
               class="btn <?= $selectedStage === 'middle' ? 'btn-primary' : 'btn-outline-primary' ?>">
                Ortaokul
            </a>
        </div>
    </div>
</div>

<?php if (empty($courses)): ?>
    <div class="alert alert-warning">
        <i class="fa fa-exclamation-triangle"></i> Size atanmış ders bulunamadı.
    </div>
<?php else: ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="index.php?module=teacher_enrollment&action=bulk_assign_store">
            <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ders Seçin <span class="text-danger">*</span></label>
                    <select name="course_id" class="form-select" required>
                        <option value="">-- Ders Seçiniz --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= (int)$c['id'] ?>">
                                <?= h($c['name']) ?>
                                <?php if (!empty($c['teacher_name'])): ?>
                                    (<?= h($c['teacher_name']) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Dönem Yılı</label>
                    <input type="text" name="semester_year" class="form-control" value="2024-2025" required>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Dönem</label>
                    <select name="semester_period" class="form-select" required>
                        <option value="1">1. Dönem</option>
                        <option value="2">2. Dönem</option>
                    </select>
                </div>
            </div>
            
            <?php if (empty($students)): ?>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> 
                    <?= $selectedStage ? 'Bu kademede öğrenci bulunamadı.' : 'Öğrenci bulunamadı. Lütfen kademe seçin.' ?>
                </div>
            <?php else: ?>
            
            <div class="mb-3">
                <label class="form-label">
                    Öğrencileri Seçin (Çoklu seçim için Ctrl basılı tutun)
                    <span class="text-danger">*</span>
                </label>
                <select name="student_ids[]" class="form-select" size="15" multiple required>
                    <?php 
                    $currentClass = '';
                    foreach ($students as $st): 
                        $className = $st['class_name'] ?? ($st['stage'] ? ucfirst($st['stage']) . ' - ' . ($st['sinif'] ?? '') : ($st['sinif'] ?? 'Sınıfsız'));
                        
                        // Sınıf grubu başlığı
                        if ($currentClass !== $className) {
                            $currentClass = $className;
                            echo '<option disabled>───── ' . h($currentClass) . ' ─────</option>';
                        }
                    ?>
                        <option value="<?= (int)$st['id'] ?>">
                            <?= h($st['name']) ?> 
                            <?php if (!empty($st['okul'])): ?>
                                - <?= h($st['okul']) ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">
                    <i class="fa fa-info-circle"></i> 
                    Windows: Ctrl + Sol Tık | Mac: Cmd + Sol Tık | Tümünü Seç: Ctrl+A
                </small>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Seçili Öğrencileri Derse Ekle
                </button>
                <button type="button" class="btn btn-secondary" onclick="selectAll()">
                    <i class="fa fa-check-square"></i> Tümünü Seç
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="deselectAll()">
                    <i class="fa fa-square"></i> Seçimi Kaldır
                </button>
            </div>
            
            <?php endif; ?>
        </form>
    </div>
</div>

<?php endif; ?>

<script>
function selectAll() {
    const select = document.querySelector('select[name="student_ids[]"]');
    if (select) {
        for (let i = 0; i < select.options.length; i++) {
            if (!select.options[i].disabled) {
                select.options[i].selected = true;
            }
        }
    }
}

function deselectAll() {
    const select = document.querySelector('select[name="student_ids[]"]');
    if (select) {
        for (let i = 0; i < select.options.length; i++) {
            select.options[i].selected = false;
        }
    }
}
</script>
<h4 class="mb-4"><i class="fa fa-users-cog"></i> Toplu Ders Atama</h4>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="index.php?module=teacher_enrollment&action=bulk_assign_store">
            <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ders Seçin</label>
                    <select name="course_id" class="form-select" required>
                        <option value="">-- Ders Seçiniz --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= (int)$c['id'] ?>">
                                <?= h($c['name']) ?> (<?= h($c['course_category']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Dönem Yılı</label>
                    <input type="text" name="semester_year" class="form-control" value="2024-2025" required>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Dönem</label>
                    <select name="semester_period" class="form-select" required>
                        <option value="1">1. Dönem</option>
                        <option value="2">2. Dönem</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Öğrencileri Seçin (Çoklu seçim için Ctrl basılı tutun)</label>
                <select name="student_ids[]" class="form-select" size="15" multiple required>
                    <?php 
                    $currentSchool = '';
                    foreach ($students as $st): 
                        if ($currentSchool !== ($st['okul'] ?? '')) {
                            $currentSchool = $st['okul'] ?? '';
                            if ($currentSchool) {
                                echo '<option disabled>─── ' . h($currentSchool) . ' ───</option>';
                            }
                        }
                    ?>
                        <option value="<?= (int)$st['id'] ?>">
                            <?= h($st['name']) ?> (<?= h($st['sinif'] ?? '-') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Windows: Ctrl + Sol Tık | Mac: Cmd + Sol Tık</small>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> Seçili Öğrencileri Derse Ekle
            </button>
        </form>
    </div>
</div>