<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$course = $course ?? [];
$date = $date ?? date('Y-m-d');
$students = $students ?? [];
$attendanceMap = $attendanceMap ?? [];

$dateFormatted = date('d F Y, l', strtotime($date));

// Türkçe aylar ve günler
$months = ['January' => 'Ocak', 'February' => 'Şubat', 'March' => 'Mart', 'April' => 'Nisan', 'May' => 'Mayıs', 'June' => 'Haziran', 'July' => 'Temmuz', 'August' => 'Ağustos', 'September' => 'Eylül', 'October' => 'Ekim', 'November' => 'Kasım', 'December' => 'Aralık'];
$days = ['Monday' => 'Pazartesi', 'Tuesday' => 'Salı', 'Wednesday' => 'Çarşamba', 'Thursday' => 'Perşembe', 'Friday' => 'Cuma', 'Saturday' => 'Cumartesi', 'Sunday' => 'Pazar'];
$dateFormatted = str_replace(array_keys($months), array_values($months), $dateFormatted);
$dateFormatted = str_replace(array_keys($days), array_values($days), $dateFormatted);

// Flash mesajlar
if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= h($_SESSION['flash_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); endif;

if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= h($_SESSION['flash_error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); endif;
?>

<div class="mb-3">
    <a href="index.php?module=lesson_attendance&action=index" class="btn btn-outline-secondary">
        <i class="fa fa-arrow-left"></i> Ders Seçimine Dön
    </a>
</div>

<div class="card shadow">
    <div class="card-header bg-gradient-primary text-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1"><?= h($course['name'] ?? '') ?></h4>
                <div class="small"><?= $dateFormatted ?></div>
            </div>
            <div class="text-end">
                <div class="badge bg-light text-dark fs-6">
                    <?= count($students) ?> Öğrenci
                </div>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <form method="POST" action="index.php?module=lesson_attendance&action=save" id="attendanceForm">
            <input type="hidden" name="course_id" value="<?= (int)$course['id'] ?>">
            <input type="hidden" name="date" value="<?= h($date) ?>">

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th style="width:50px">#</th>
                            <th style="width:60px">Fotoğraf</th>
                            <th>Öğrenci</th>
                            <th style="width:100px">Sınıf</th>
                            <th style="width:400px" class="text-center">Durum</th>
                            <th style="width:200px">Not</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $index = 1;
                        foreach ($students as $student): 
                            $currentStatus = $attendanceMap[(int)$student['id']]['status'] ?? 'geldi';
                            $currentNotes = $attendanceMap[(int)$student['id']]['notes'] ?? '';
                            $photo = $student['profile_photo'] ?? 'https://via.placeholder.com/40x40?text=' . strtoupper(substr($student['name'], 0, 1));
                        ?>
                            <tr>
                                <td class="text-center"><?= $index++ ?></td>
                                <td>
                                    <img src="<?= h($photo) ?>" 
                                         class="rounded-circle" 
                                         style="width:40px;height:40px;object-fit:cover;"
                                         alt="<?= h($student['name']) ?>">
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= h($student['name']) ?></div>
                                    <?php if (!empty($student['student_number'])): ?>
                                        <small class="text-muted">No: <?= h($student['student_number']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= h($student['class_name'] ?? '—') ?></td>
                                <td>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" 
                                               class="btn-check" 
                                               name="attendance[<?= (int)$student['id'] ?>][status]" 
                                               id="geldi_<?= (int)$student['id'] ?>" 
                                               value="geldi"
                                               <?= $currentStatus === 'geldi' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-success" for="geldi_<?= (int)$student['id'] ?>">
                                            ✅ Geldi
                                        </label>

                                        <input type="radio" 
                                               class="btn-check" 
                                               name="attendance[<?= (int)$student['id'] ?>][status]" 
                                               id="gelmedi_<?= (int)$student['id'] ?>" 
                                               value="gelmedi"
                                               <?= $currentStatus === 'gelmedi' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-danger" for="gelmedi_<?= (int)$student['id'] ?>">
                                            ❌ Gelmedi
                                        </label>

                                        <input type="radio" 
                                               class="btn-check" 
                                               name="attendance[<?= (int)$student['id'] ?>][status]" 
                                               id="izinli_<?= (int)$student['id'] ?>" 
                                               value="izinli"
                                               <?= $currentStatus === 'izinli' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-info" for="izinli_<?= (int)$student['id'] ?>">
                                            📋 İzinli
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" 
                                           name="attendance[<?= (int)$student['id'] ?>][notes]" 
                                           value="<?= h($currentNotes) ?>" 
                                           class="form-control form-control-sm" 
                                           placeholder="Not...">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button type="button" class="btn btn-outline-secondary" onclick="tumunuIsaretle('geldi')">
                            <i class="fa fa-check-double"></i> Tümünü Geldi
                        </button>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa fa-save"></i> Yoklamayı Kaydet
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function tumunuIsaretle(durum) {
    const radios = document.querySelectorAll(`input[type="radio"][value="${durum}"]`);
    radios.forEach(radio => {
        radio.checked = true;
    });
}
</script>

<style>
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}
.btn-check:checked + .btn-outline-success {
    background-color: #198754;
    color: white;
}
.btn-check:checked + .btn-outline-danger {
    background-color: #dc3545;
    color: white;
}
.btn-check:checked + .btn-outline-info {
    background-color: #0dcaf0;
    color: white;
}
</style>