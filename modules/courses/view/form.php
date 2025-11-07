<?php
if (!function_exists('h')) { 
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); } 
}

$errors = $errors ?? [];
$isEdit = $isEdit ?? false;
$formAction = $formAction ?? 'index.php?module=courses&action=store';
$course = $course ?? [];
$teachers = $teachers ?? [];
$classes = $classes ?? [];
$selectedTeacher = $selectedTeacher ?? null;
$selectedClasses = $selectedClasses ?? [];
$schedules = $schedules ?? [];

$c = array_merge([
    'name' => '', 'description' => '', 'category' => '', 
    'teacher_id' => '', 'color' => '#3788d8', 'is_active' => 1
], $course);

$dayNames = [
    1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 
    4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'
];
?>

<div class="mb-3">
    <a href="index.php?module=courses&action=list" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left"></i> Listeye Dön
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <h6 class="alert-heading mb-2">Lütfen aşağıdaki hataları düzeltin:</h6>
        <ul class="mb-0 small">
            <?php foreach ($errors as $error): ?>
                <li><?= h($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form action="<?= h($formAction) ?>" method="post">
    <!-- Temel Bilgiler -->
    <div class="card shadow-sm mb-3">
        <div class="card-header">
            <h6 class="mb-0"><?= $isEdit ? 'Ders Düzenle' : 'Yeni Ders' ?></h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Ders Adı -->
                <div class="col-md-6">
                    <label class="form-label">Ders Adı <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required 
                           value="<?= h($c['name']) ?>">
                </div>

                <!-- Kademe -->
                <div class="col-md-6">
                    <label class="form-label">Kademe <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required>
                        <option value="">-- Seçiniz --</option>
                        <option value="ilkokul" <?= $c['category'] === 'ilkokul' ? 'selected' : '' ?>>
                            İlkokul
                        </option>
                        <option value="ortaokul" <?= $c['category'] === 'ortaokul' ? 'selected' : '' ?>>
                            Ortaokul
                        </option>
                        <option value="lise" <?= $c['category'] === 'lise' ? 'selected' : '' ?>>
                            Lise
                        </option>
                    </select>
                </div>

                <!-- Açıklama -->
                <div class="col-12">
                    <label class="form-label">Açıklama</label>
                    <textarea name="description" class="form-control" rows="2"><?= h($c['description']) ?></textarea>
                </div>

                <!-- Öğretmen -->
                <div class="col-md-6">
                    <label class="form-label">Sorumlu Öğretmen</label>
                    <select name="teacher_id" class="form-select">
                        <option value="">-- Seçilmedi --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= (int)$t['id'] ?>" 
                                    <?= ((int)$selectedTeacher === (int)$t['id']) ? 'selected' : '' ?>>
                                <?= h($t['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Renk -->
                <div class="col-md-3">
                    <label class="form-label">Renk (Takvim için)</label>
                    <input type="color" name="color" class="form-control form-control-color" 
                           value="<?= h($c['color']) ?>">
                </div>

                <!-- Aktif -->
                <div class="col-md-3">
                    <label class="form-label d-block">Durum</label>
                    <div class="form-check form-switch mt-2">
                        <input type="checkbox" name="is_active" value="1" 
                               class="form-check-input" id="is_active"
                               <?= !empty($c['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sınıf Atamaları -->
    <div class="card shadow-sm mb-3">
        <div class="card-header">
            <h6 class="mb-0">Sınıf/Program Atamaları</h6>
        </div>
        <div class="card-body">
            <label class="form-label">Bu derse kayıtlı olabilecek sınıflar</label>
            <select name="class_ids[]" multiple class="form-select" size="8">
                <?php foreach ($classes as $class): ?>
                    <option value="<?= (int)$class['id'] ?>" 
                            <?= in_array($class['id'], $selectedClasses) ? 'selected' : '' ?>>
                        <?= h($class['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">Ctrl+Click ile çoklu seçim yapabilirsiniz</small>
        </div>
    </div>

    <!-- Ders Programı -->
    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Ders Programı</h6>
            <button type="button" class="btn btn-sm btn-success" onclick="addScheduleRow()">
                <i class="fa fa-plus"></i> Yeni Zaman Ekle
            </button>
        </div>
        <div class="card-body">
            <div id="scheduleContainer">
                <?php if (empty($schedules)): ?>
                    <!-- Boş satır -->
                    <div class="row g-2 mb-2 schedule-row">
                        <div class="col-md-3">
                            <label class="form-label small">Gün</label>
                            <select name="schedule_days[]" class="form-select form-select-sm">
                                <option value="">-- Seçiniz --</option>
                                <?php foreach ($dayNames as $dayNum => $dayName): ?>
                                    <option value="<?= $dayNum ?>"><?= h($dayName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Başlangıç</label>
                            <input type="time" name="schedule_start_times[]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Bitiş</label>
                            <input type="time" name="schedule_end_times[]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small d-block">&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeScheduleRow(this)">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($schedules as $schedule): ?>
                        <div class="row g-2 mb-2 schedule-row">
                            <div class="col-md-3">
                                <label class="form-label small">Gün</label>
                                <select name="schedule_days[]" class="form-select form-select-sm">
                                    <option value="">-- Seçiniz --</option>
                                    <?php foreach ($dayNames as $dayNum => $dayName): ?>
                                        <option value="<?= $dayNum ?>" 
                                                <?= ($schedule['day_of_week'] ?? '') == $dayNum ? 'selected' : '' ?>>
                                            <?= h($dayName) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Başlangıç</label>
                                <input type="time" name="schedule_start_times[]" 
                                       class="form-control form-control-sm"
                                       value="<?= h($schedule['start_time'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Bitiş</label>
                                <input type="time" name="schedule_end_times[]" 
                                       class="form-control form-control-sm"
                                       value="<?= h($schedule['end_time'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small d-block">&nbsp;</label>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeScheduleRow(this)">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <small class="text-muted">
                💡 İpucu: Her dersin atölyesi tektir, bu yüzden sadece gün ve saat bilgisi yeterlidir.
            </small>
        </div>
    </div>

    <!-- Submit -->
    <div class="mb-4">
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Kaydet
        </button>
        <a href="index.php?module=courses&action=list" class="btn btn-outline-secondary">
            İptal
        </a>
    </div>
</form>

<script>
// Yeni schedule satırı ekle
function addScheduleRow() {
    const container = document.getElementById('scheduleContainer');
    const template = `
        <div class="row g-2 mb-2 schedule-row">
            <div class="col-md-3">
                <label class="form-label small">Gün</label>
                <select name="schedule_days[]" class="form-select form-select-sm">
                    <option value="">-- Seçiniz --</option>
                    <?php foreach ($dayNames as $dayNum => $dayName): ?>
                        <option value="<?= $dayNum ?>"><?= h($dayName) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Başlangıç</label>
                <input type="time" name="schedule_start_times[]" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Bitiş</label>
                <input type="time" name="schedule_end_times[]" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label small d-block">&nbsp;</label>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeScheduleRow(this)">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', template);
}

// Schedule satırını sil
function removeScheduleRow(btn) {
    const row = btn.closest('.schedule-row');
    if (row) {
        row.remove();
    }
}
</script>