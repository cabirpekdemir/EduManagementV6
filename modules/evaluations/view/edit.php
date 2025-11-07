<?php
if (!function_exists('h')) {
    function h($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$evaluation = $evaluation ?? [];
$classes_by_level = $classes_by_level ?? [];
$all_students = $all_students ?? [];
$all_teachers = $all_teachers ?? [];
$assigned_students = $assigned_students ?? [];
$assigned_teachers = $assigned_teachers ?? [];
$enrollment_statuses = $enrollment_statuses ?? [];
$evaluation_types = $evaluation_types ?? [];
?>

<div class="mb-3">
    <a href="index.php?module=evaluations&action=index" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left"></i> Listeye Dön
    </a>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] === 'updated'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        Değerlendirme başarıyla güncellendi!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0"><?= h($evaluation['name'] ?? '') ?> - Düzenle</h5>
    </div>
    <div class="card-body">
        <form action="index.php?module=evaluations&action=update" method="post">
            <input type="hidden" name="id" value="<?= (int)$evaluation['id'] ?>">
            
            <div class="row g-3">
                <!-- Değerlendirme Adı -->
                <div class="col-md-6">
                    <label class="form-label">Değerlendirme Adı <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" 
                           value="<?= h($evaluation['name'] ?? '') ?>" required>
                </div>

                <!-- Değerlendirme Türü -->
                <div class="col-md-6">
                    <label class="form-label">Değerlendirme Türü <span class="text-danger">*</span></label>
                    <select name="evaluation_type" class="form-select" required>
                        <?php foreach ($evaluation_types as $type): ?>
                            <option value="<?= h($type) ?>" 
                                    <?= ($evaluation['evaluation_type'] ?? '') === $type ? 'selected' : '' ?>>
                                <?= h($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Açıklama -->
                <div class="col-12">
                    <label class="form-label">Açıklama</label>
                    <textarea name="description" class="form-control" rows="2"><?= h($evaluation['description'] ?? '') ?></textarea>
                </div>

                <!-- Tarih ve Saat -->
                <div class="col-md-4">
                    <label class="form-label">Tarih</label>
                    <input type="date" name="exam_date" class="form-control" 
                           value="<?= h($evaluation['exam_date'] ?? '') ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Saat</label>
                    <input type="time" name="exam_time" class="form-control" 
                           value="<?= h($evaluation['exam_time'] ?? '') ?>">
                </div>

                <div class="col-md-5">
                    <label class="form-label">Maksimum Puan</label>
                    <input type="number" name="max_score" class="form-control" step="0.01" 
                           value="<?= h($evaluation['max_score'] ?? '100') ?>">
                </div>

                <!-- Sınıf/Program Seçimi -->
                <div class="col-12">
                    <label class="form-label">İlgili Sınıf/Program</label>
                    <select name="class_id" class="form-select">
                        <option value="">-- Sınıf Seçilmedi --</option>
                        
                        <!-- İLKOKUL -->
                        <optgroup label="📚 İLKOKUL">
                            <?php foreach ($classes_by_level['ilkokul'] ?? [] as $class): ?>
                                <option value="<?= (int)$class['id'] ?>"
                                        <?= ($evaluation['class_id'] ?? '') == $class['id'] ? 'selected' : '' ?>>
                                    <?= h(str_replace('İlkokul - ', '', $class['name'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        
                        <!-- ORTAOKUL -->
                        <optgroup label="📖 ORTAOKUL">
                            <?php foreach ($classes_by_level['ortaokul'] ?? [] as $class): ?>
                                <option value="<?= (int)$class['id'] ?>"
                                        <?= ($evaluation['class_id'] ?? '') == $class['id'] ? 'selected' : '' ?>>
                                    <?= h(str_replace('Ortaokul - ', '', $class['name'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>

                <!-- Bireysel Öğrenci Seçimi -->
                <div class="col-12">
                    <label class="form-label">
                        <i class="fa fa-user"></i> Atanmış Öğrenciler (Ctrl+Click ile çoklu seçim)
                    </label>
                    <select name="students[]" multiple class="form-select" size="8">
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
                            $isSelected = in_array($student['id'], $assigned_students);
                        ?>
                            <option value="<?= (int)$student['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                <?= $statusIcon ?> <?= h($student['name']) ?><?= $classInfo ?> - <?= $statusText ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <hr>
                    <h6 class="text-primary mb-3">Görevli Öğretmenler</h6>
                </div>

                <!-- Öğretmen Atamaları -->
                <?php for ($i = 0; $i < 2; $i++): 
                    $current_teacher_id = $assigned_teachers[$i]['teacher_id'] ?? '';
                    $current_role = $assigned_teachers[$i]['role'] ?? '';
                ?>
                <div class="col-md-6">
                    <label class="form-label">Öğretmen <?= $i + 1 ?></label>
                    <div class="input-group">
                        <select name="teachers[<?= $i ?>][id]" class="form-select">
                            <option value="">-- Öğretmen Seç --</option>
                            <?php foreach ($all_teachers as $teacher): ?>
                                <option value="<?= (int)$teacher['id'] ?>"
                                        <?= $current_teacher_id == $teacher['id'] ? 'selected' : '' ?>>
                                    <?= h($teacher['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select name="teachers[<?= $i ?>][role]" class="form-select" style="max-width: 130px;">
                            <option value="">-- Rol --</option>
                            <option value="sorumlu" <?= $current_role === 'sorumlu' ? 'selected' : '' ?>>
                                Sorumlu
                            </option>
                            <option value="gozetmen" <?= $current_role === 'gozetmen' ? 'selected' : '' ?>>
                                Gözetmen
                            </option>
                        </select>
                    </div>
                </div>
                <?php endfor; ?>

                <!-- Durum -->
                <div class="col-12">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="draft" <?= ($evaluation['status'] ?? '') === 'draft' ? 'selected' : '' ?>>
                            Taslak
                        </option>
                        <option value="active" <?= ($evaluation['status'] ?? '') === 'active' ? 'selected' : '' ?>>
                            Aktif
                        </option>
                        <option value="completed" <?= ($evaluation['status'] ?? '') === 'completed' ? 'selected' : '' ?>>
                            Tamamlandı
                        </option>
                        <option value="cancelled" <?= ($evaluation['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>
                            İptal Edildi
                        </option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Güncelle
                </button>
                <a href="index.php?module=evaluations&action=index" class="btn btn-outline-secondary">
                    İptal
                </a>
            </div>
        </form>
    </div>
</div>
