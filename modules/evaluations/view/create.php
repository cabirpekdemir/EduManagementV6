<?php
if (!function_exists('h')) {
    function h($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$classes_by_level = $classes_by_level ?? [];
$all_students = $all_students ?? [];
$all_teachers = $all_teachers ?? [];
$enrollment_statuses = $enrollment_statuses ?? [];
$evaluation_types = $evaluation_types ?? [];
?>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Yeni Değerlendirme Oluştur</h5>
    </div>
    <div class="card-body">
        <form action="index.php?module=evaluations&action=store" method="post">
            <div class="row g-3">
                <!-- Değerlendirme Adı -->
                <div class="col-md-6">
                    <label class="form-label">Değerlendirme Adı <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <!-- Değerlendirme Türü -->
                <div class="col-md-6">
                    <label class="form-label">Değerlendirme Türü <span class="text-danger">*</span></label>
                    <select name="evaluation_type" class="form-select" required id="evaluation_type">
                        <?php foreach ($evaluation_types as $type): ?>
                            <option value="<?= h($type) ?>"><?= h($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Açıklama -->
                <div class="col-12">
                    <label class="form-label">Açıklama</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>

                <!-- Tarih ve Saat -->
                <div class="col-md-4">
                    <label class="form-label">Tarih</label>
                    <input type="date" name="exam_date" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Saat</label>
                    <input type="time" name="exam_time" class="form-control">
                </div>

                <div class="col-md-5">
                    <label class="form-label">Maksimum Puan</label>
                    <input type="number" name="max_score" class="form-control" step="0.01" value="100">
                </div>

                <!-- Sınıf/Program Seçimi -->
                <div class="col-12">
                    <label class="form-label">İlgili Sınıf/Program</label>
                    <select name="class_id" class="form-select">
                        <option value="">-- Sınıf Seçilmedi --</option>
                        
                        <!-- İLKOKUL -->
                        <optgroup label="📚 İLKOKUL">
                            <?php foreach ($classes_by_level['ilkokul'] ?? [] as $class): ?>
                                <option value="<?= (int)$class['id'] ?>">
                                    <?= h(str_replace('İlkokul - ', '', $class['name'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        
                        <!-- ORTAOKUL -->
                        <optgroup label="📖 ORTAOKUL">
                            <?php foreach ($classes_by_level['ortaokul'] ?? [] as $class): ?>
                                <option value="<?= (int)$class['id'] ?>">
                                    <?= h(str_replace('Ortaokul - ', '', $class['name'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                    <small class="text-muted">
                        Bir program seçilirse o programdaki tüm öğrenciler otomatik atanır
                    </small>
                </div>

                <div class="col-12">
                    <hr>
                    <h6 class="text-primary mb-3">Öğrenci Atama Seçenekleri</h6>
                </div>

                <!-- Duruma Göre Toplu Atama -->
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="fa fa-users"></i> Duruma Göre Toplu Atama
                    </label>
                    <select name="assign_by_status" class="form-select">
                        <option value="">-- Durum Seçilmedi --</option>
                        <?php foreach ($enrollment_statuses as $key => $label): ?>
                            <option value="<?= h($key) ?>"><?= h($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Seçilen durumdaki tüm öğrenciler otomatik atanır</small>
                </div>

                <!-- Sınıf Seviyesine Göre Toplu Atama -->
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="fa fa-level-up"></i> Sınıf Geçişine Göre Toplu Atama
                    </label>
                    <select name="assign_by_grade" class="form-select">
                        <option value="">-- Seviye Seçilmedi --</option>
                        <option value="4-5">4. Sınıftan 5. Sınıfa Geçenler</option>
                        <option value="5-6">5. Sınıftan 6. Sınıfa Geçenler</option>
                        <option value="6-7">6. Sınıftan 7. Sınıfa Geçenler</option>
                        <option value="7-8">7. Sınıftan 8. Sınıfa Geçenler</option>
                        <option value="8-lise">8. Sınıftan Liseye Geçenler</option>
                    </select>
                    <small class="text-muted">Örnek: 4→5 seçilirse tüm 4. sınıf öğrencileri atanır</small>
                </div>

                <!-- Bireysel Öğrenci Seçimi -->
                <div class="col-12">
                    <label class="form-label">
                        <i class="fa fa-user"></i> Bireysel Öğrenci Seçimi (Ctrl+Click ile çoklu seçim)
                    </label>
                    <select name="students[]" multiple class="form-select" size="8" id="studentSelect">
                        <?php 
                        $statuses = [
                            'on_kayit' => '📝',
                            'sinav_secim' => '📋',
                            'aktif' => '✅',
                            'mezun' => '🎓'
                        ];
                        foreach ($all_students as $student): 
                            $statusIcon = $statuses[$student['enrollment_status'] ?? ''] ?? '⭕';
                            $statusText = $enrollment_statuses[$student['enrollment_status'] ?? ''] ?? '';
                            $classInfo = !empty($student['sinif']) ? ' [' . $student['sinif'] . ']' : '';
                        ?>
                            <option value="<?= (int)$student['id'] ?>" 
                                    data-status="<?= h($student['enrollment_status'] ?? '') ?>"
                                    data-class="<?= h($student['sinif'] ?? '') ?>">
                                <?= $statusIcon ?> <?= h($student['name']) ?><?= $classInfo ?> - <?= $statusText ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">
                        Durum: 📝 Ön Kayıt | 📋 Sınav Seçim | ✅ Aktif | 🎓 Mezun
                    </small>
                </div>

                <div class="col-12">
                    <hr>
                    <h6 class="text-primary mb-3">Görevli Öğretmenler</h6>
                </div>

                <!-- Öğretmen Atamaları -->
                <div class="col-md-6">
                    <label class="form-label">Öğretmen 1</label>
                    <div class="input-group">
                        <select name="teachers[0][id]" class="form-select">
                            <option value="">-- Öğretmen Seç --</option>
                            <?php foreach ($all_teachers as $teacher): ?>
                                <option value="<?= (int)$teacher['id'] ?>">
                                    <?= h($teacher['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select name="teachers[0][role]" class="form-select" style="max-width: 130px;">
                            <option value="">-- Rol --</option>
                            <option value="sorumlu">Sorumlu</option>
                            <option value="gozetmen">Gözetmen</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Öğretmen 2</label>
                    <div class="input-group">
                        <select name="teachers[1][id]" class="form-select">
                            <option value="">-- Öğretmen Seç --</option>
                            <?php foreach ($all_teachers as $teacher): ?>
                                <option value="<?= (int)$teacher['id'] ?>">
                                    <?= h($teacher['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select name="teachers[1][role]" class="form-select" style="max-width: 130px;">
                            <option value="">-- Rol --</option>
                            <option value="sorumlu">Sorumlu</option>
                            <option value="gozetmen">Gözetmen</option>
                        </select>
                    </div>
                </div>

                <!-- Durum -->
                <div class="col-12">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="draft">Taslak</option>
                        <option value="active" selected>Aktif</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Kaydet
                </button>
                <a href="index.php?module=evaluations&action=index" class="btn btn-outline-secondary">
                    İptal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Öğrenci listesinde arama
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('studentSelect');
    if (!select) return;
    
    const filterDiv = document.createElement('div');
    filterDiv.className = 'mb-2';
    filterDiv.innerHTML = `
        <input type="text" class="form-control form-control-sm" 
               placeholder="Öğrenci ara..." id="studentSearch">
    `;
    select.parentElement.insertBefore(filterDiv, select);
    
    const searchInput = document.getElementById('studentSearch');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        Array.from(select.options).forEach(option => {
            const text = option.textContent.toLowerCase();
            option.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
});
</script>