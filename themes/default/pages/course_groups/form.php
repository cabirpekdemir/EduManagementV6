<h2><?= $isEdit ? "Ders Grubunu Düzenle" : "Yeni Ders Grubu Oluştur" ?></h2>

<form method="post" action="<?= e($formAction) ?>">
    <div>
        <label for="name">Grup Adı:</label><br>
        <input type="text" id="name" name="name" value="<?= e($course_group['name'] ?? '') ?>" required size="50">
    </div>
    <br>
    <div>
        <label for="description">Açıklama:</label><br>
        <textarea id="description" name="description" rows="4" cols="50"><?= e($course_group['description'] ?? '') ?></textarea>
    </div>
    <br>
    <div>
        <label>Bu Gruba Dahil Edilecek Dersler:</label><br>
        <div style="border: 1px solid #ccc; padding: 10px; max-height: 200px; overflow-y: auto;">
            <?php if (!empty($all_courses)): ?>
                <?php foreach ($all_courses as $course): ?>
                    <?php
                        $is_selected = !empty($selected_course_ids) && in_array($course['id'], $selected_course_ids);
                        $is_selectable_checked = false; // Varsayılan
                        if ($isEdit && isset($course_select_options[$course['id']])) {
                            $is_selectable_checked = $course_select_options[$course['id']];
                        } elseif (!$isEdit && $is_selected) { 
                            // Yeni grup oluşturuluyor ve bu ders seçiliyse, varsayılan olarak "ayrıca seçilebilir" olsun
                            $is_selectable_checked = true; 
                        } elseif (!$isEdit && !$is_selected) {
                            // Yeni grup oluşturuluyor ve bu ders seçili değilse, varsayılan olarak "ayrıca seçilebilir" olsun (kullanıcı seçince işaretli gelsin diye)
                            $is_selectable_checked = true;
                        }
                    ?>
                    <div style="margin-bottom: 5px;">
                        <input type="checkbox" 
                               name="course_ids[]" 
                               value="<?= e($course['id']) ?>" 
                               id="course_<?= e($course['id']) ?>"
                               <?= $is_selected ? 'checked' : '' ?>>
                        <label for="course_<?= e($course['id']) ?>"><?= e($course['name']) ?></label>
                        
                        <span class="selectable-option" style="<?= $is_selected ? '' : 'display:none;' ?>">
                            &nbsp; | &nbsp;
                            <input type="checkbox" 
                                   name="course_selectable[<?= e($course['id']) ?>]" 
                                   value="1" 
                                   id="selectable_<?= e($course['id']) ?>"
                                   <?= $is_selectable_checked ? 'checked' : '' ?>>
                            <label for="selectable_<?= e($course['id']) ?>"><small>Ayrıca tek seçilebilsin</small></label>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Sistemde kayıtlı ders bulunamadı.</p>
            <?php endif; ?>
        </div>
        <p><small>(Ders seçimi yapmak için kutucuğu işaretleyin. Birden fazla seçebilirsiniz.)</small></p>
    </div>
    <br>
    <button type="submit"><?= $isEdit ? "Güncelle" : "Oluştur" ?></button>
    <a href="index.php?module=course_groups&action=index" style="margin-left: 10px;">Vazgeç</a>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const courseCheckboxes = document.querySelectorAll('input[name="course_ids[]"]');
    courseCheckboxes.forEach(function(checkbox) {
        const selectableSpan = checkbox.closest('div').querySelector('.selectable-option');
        
        // Fonksiyonu tanımla
        function toggleSelectableOption(currentCheckbox) {
            if (selectableSpan) {
                selectableSpan.style.display = currentCheckbox.checked ? 'inline' : 'none';
                const selectableCheckbox = selectableSpan.querySelector('input[type="checkbox"]');
                if (selectableCheckbox && !currentCheckbox.checked) {
                    selectableCheckbox.checked = false; // Ana checkbox işaretli değilse, bunu da false yap
                } else if (selectableCheckbox && currentCheckbox.checked && !selectableCheckbox.getAttribute('data-initial-check-done')) {
                    // Eğer ana checkbox işaretliyse ve bu seçenek daha önce düzenleme modunda işaretlenmemişse,
                    // ve yeni ekleme değilse, varsayılan olarak işaretli gelmesin (kullanıcının tercihine kalsın)
                    // Bu kısım düzenleme (`isEdit`) ve `course_select_options` ile daha iyi yönetilmeli.
                    // PHP tarafında $is_selectable_checked zaten bu mantığı içeriyor, JS'i basitleştirebiliriz.
                    // Şimdilik PHP'nin ayarladığı gibi bırakalım.
                }
                if (selectableCheckbox) {
                     selectableCheckbox.setAttribute('data-initial-check-done', 'true');
                }
            }
        }

        // Sayfa yüklendiğinde mevcut duruma göre ayarla
        toggleSelectableOption(checkbox);

        // Checkbox değiştiğinde çalıştır
        checkbox.addEventListener('change', function() {
            toggleSelectableOption(this);
        });
    });
});
</script>