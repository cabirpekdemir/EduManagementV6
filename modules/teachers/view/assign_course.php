<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$teacher = $teacher ?? [];
$allCourses = $allCourses ?? [];
$assignedIds = $assignedIds ?? [];

if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?= h($_SESSION['flash']['type']) ?> alert-dismissible fade show">
        <?= h($_SESSION['flash']['msg']) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php unset($_SESSION['flash']); endif;
?>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fa fa-chalkboard-teacher"></i> 
            <?= h($teacher['name'] ?? '') ?> - Ders Ata
        </h5>
    </div>
    <div class="card-body">
        <form method="post" action="index.php?module=teachers&action=assign_course_store">
            <input type="hidden" name="teacher_id" value="<?= (int)$teacher['id'] ?>">

            <div class="mb-3">
                <label class="form-label">
                    Dersler Seçin <span class="text-danger">*</span>
                </label>
                <div class="list-group" style="max-height:400px;overflow-y:auto;">
                    <?php if (empty($allCourses)): ?>
                        <div class="alert alert-warning">Sistemde ders bulunamadı.</div>
                    <?php else: foreach ($allCourses as $c): 
                        $isAssigned = in_array($c['id'], $assignedIds);
                    ?>
                        <label class="list-group-item d-flex align-items-center">
                            <input type="checkbox" 
                                   name="course_ids[]" 
                                   value="<?= (int)$c['id'] ?>"
                                   class="form-check-input me-2"
                                   <?= $isAssigned ? 'checked' : '' ?>>
                            <div class="flex-grow-1">
                                <strong><?= h($c['name']) ?></strong>
                                <?php if (($c['course_category'] ?? '') === 'akademi'): ?>
                                    <span class="badge bg-info ms-2">Akademi</span>
                                <?php elseif (($c['course_category'] ?? '') === 'proje'): ?>
                                    <span class="badge bg-warning ms-2">Proje</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($isAssigned): ?>
                                <span class="badge bg-success">Mevcut</span>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; endif; ?>
                </div>
                <small class="text-muted">
                    Seçili derslerin öğretmeni bu kişi olacaktır. Mevcut atamalar işaretli gelir.
                </small>
            </div>

            <div class="d-flex gap-2">
                <a href="index.php?module=teachers&action=show&id=<?= (int)$teacher['id'] ?>" 
                   class="btn btn-outline-secondary">
                    <i class="fa fa-times"></i> İptal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Dersleri Kaydet
                </button>
            </div>
        </form>
    </div>
</div>