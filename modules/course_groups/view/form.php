<?php
// modules/course_groups/view/form.php
if (!function_exists('e')) {
  function e($v){ return htmlspecialchars((string)($v??''), ENT_QUOTES, 'UTF-8'); }
}

$isEdit = $isEdit ?? false;
$course_group = $course_group ?? [];
$all_courses = $all_courses ?? [];
$selected_course_ids = $selected_course_ids ?? [];
$course_select_options = $course_select_options ?? [];
$formAction = $formAction ?? '';
$csrf_token = $csrf_token ?? '';
?>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0"><?= $isEdit ? "Ders Grubunu Düzenle" : "Yeni Ders Grubu Oluştur" ?></h5>
    </div>
    <div class="card-body">
        <form method="post" action="<?= e($formAction) ?>">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            
            <div class="row g-3">
                <div class="col-md-12">
                    <label for="name" class="form-label">Grup Adı <span class="text-danger">*</span></label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           class="form-control" 
                           value="<?= e($course_group['name'] ?? '') ?>" 
                           required 
                           maxlength="255"
                           placeholder="Örn: Düz Paket, Ters Paket">
                </div>

                <div class="col-md-12">
                    <label for="description" class="form-label">Açıklama</label>
                    <textarea id="description" 
                              name="description" 
                              class="form-control" 
                              rows="3"
                              placeholder="Grup hakkında açıklama..."><?= e($course_group['description'] ?? '') ?></textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Bu Gruba Dahil Edilecek Dersler</label>
                    <div class="border rounded p-3 bg-light" style="max-height: 400px; overflow-y: auto;">
                        <?php if (!empty($all_courses)): ?>
                            <?php foreach ($all_courses as $course): ?>
                                <?php
                                    $is_selected = in_array($course['id'], $selected_course_ids);
                                    $is_selectable_checked = false;
                                    
                                    if ($isEdit && isset($course_select_options[$course['id']])) {
                                        $is_selectable_checked = $course_select_options[$course['id']];
                                    } elseif (!$isEdit && $is_selected) { 
                                        $is_selectable_checked = true; 
                                    } elseif (!$isEdit && !$is_selected) {
                                        $is_selectable_checked = true;
                                    }
                                ?>
                                <div class="mb-2 p-2 border-bottom">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input course-checkbox" 
                                               name="course_ids[]" 
                                               value="<?= e($course['id']) ?>" 
                                               id="course_<?= e($course['id']) ?>"
                                               <?= $is_selected ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="course_<?= e($course['id']) ?>">
                                            <strong><?= e($course['name']) ?></strong>
                                        </label>
                                    </div>
                                    
                                    <div class="selectable-option ms-4 mt-1" style="<?= $is_selected ? '' : 'display:none;' ?>">
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   name="course_selectable[<?= e($course['id']) ?>]" 
                                                   value="1" 
                                                   id="selectable_<?= e($course['id']) ?>"
                                                   <?= $is_selectable_checked ? 'checked' : '' ?>>
                                            <label class="form-check-label text-muted" for="selectable_<?= e($course['id']) ?>">
                                                <small><i class="fa fa-check-circle"></i> Ayrıca tek başına seçilebilsin</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Sistemde kayıtlı ders bulunamadı.</p>
                        <?php endif; ?>
                    </div>
                    <small class="form-text text-muted">
                        Gruba eklemek istediğiniz dersleri seçin. "Ayrıca tek başına seçilebilsin" seçeneği ile öğrencilere bu dersi grup dışında da atayabilirsiniz.
                    </small>
                </div>

                <div class="col-12 text-end mt-4">
                    <a href="index.php?module=course_groups&action=index" class="btn btn-outline-secondary">
                        <i class="fa fa-times"></i> Vazgeç
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> <?= $isEdit ? "Güncelle" : "Oluştur" ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const courseCheckboxes = document.querySelectorAll('.course-checkbox');
    
    courseCheckboxes.forEach(function(checkbox) {
        const selectableSpan = checkbox.closest('.mb-2').querySelector('.selectable-option');
        
        function toggleSelectableOption(currentCheckbox) {
            if (selectableSpan) {
                selectableSpan.style.display = currentCheckbox.checked ? 'block' : 'none';
                const selectableCheckbox = selectableSpan.querySelector('input[type="checkbox"]');
                if (selectableCheckbox && !currentCheckbox.checked) {
                    selectableCheckbox.checked = false;
                }
            }
        }

        toggleSelectableOption(checkbox);

        checkbox.addEventListener('change', function() {
            toggleSelectableOption(this);
        });
    });
});
</script>