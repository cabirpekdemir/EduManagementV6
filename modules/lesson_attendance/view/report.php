<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$allCourses = $allCourses ?? [];
$allClasses = $allClasses ?? [];
$allStudents = $allStudents ?? [];
$attendanceRecords = $attendanceRecords ?? [];
$filters = $filters ?? [];
$availableStatuses = $availableStatuses ?? ['Geldi', 'Gelmedi', 'Geç Geldi', 'İzinli'];
?>

<div class="mb-3">
    <a href="index.php?module=lesson_attendance&action=index" class="btn btn-outline-secondary">
        <i class="fa fa-arrow-left"></i> Yoklama Ana Sayfasına Dön
    </a>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header">
        <h5 class="mb-0">Filtreler</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="index.php">
            <input type="hidden" name="module" value="lesson_attendance">
            <input type="hidden" name="action" value="report">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Ders</label>
                    <select name="filter_course_id" class="form-select">
                        <option value="">Tümü</option>
                        <?php foreach ($allCourses as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" 
                                    <?= (($filters['filter_course_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                                <?= h($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Sınıf</label>
                    <select name="filter_class_id" class="form-select">
                        <option value="">Tümü</option>
                        <?php foreach ($allClasses as $cl): ?>
                            <option value="<?= (int)$cl['id'] ?>" 
                                    <?= (($filters['filter_class_id'] ?? '') == $cl['id']) ? 'selected' : '' ?>>
                                <?= h($cl['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Öğrenci</label>
                    <select name="filter_student_id" class="form-select">
                        <option value="">Tümü</option>
                        <?php foreach ($allStudents as $s): ?>
                            <option value="<?= (int)$s['id'] ?>" 
                                    <?= (($filters['filter_student_id'] ?? '') == $s['id']) ? 'selected' : '' ?>>
                                <?= h($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Durum</label>
                    <select name="filter_status" class="form-select">
                        <option value="">Tümü</option>
                        <?php foreach ($availableStatuses as $status): ?>
                            <option value="<?= h($status) ?>" 
                                    <?= (($filters['filter_status'] ?? '') === $status) ? 'selected' : '' ?>>
                                <?= h($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Başlangıç Tarihi</label>
                    <input type="date" name="filter_date_start" class="form-control" 
                           value="<?= h($filters['filter_date_start'] ?? '') ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Bitiş Tarihi</label>
                    <input type="date" name="filter_date_end" class="form-control" 
                           value="<?= h($filters['filter_date_end'] ?? '') ?>">
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-filter"></i> Raporu Getir
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Yoklama Kayıtları (<?= count($attendanceRecords) ?>)</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($attendanceRecords)): ?>
            <div class="p-3 text-center text-muted">
                Belirtilen kriterlere uygun yoklama kaydı bulunamadı.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tarih</th>
                            <th>Ders</th>
                            <th>Ders Saati</th>
                            <th>Sınıf</th>
                            <th>Öğrenci</th>
                            <th>Durum</th>
                            <th>Notlar</th>
                            <th>Yoklamayı Giren</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendanceRecords as $record): 
                            $statusColor = match($record['status']) {
                                'Geldi' => 'success',
                                'Gelmedi' => 'danger',
                                'Geç Geldi' => 'warning',
                                'İzinli' => 'info',
                                default => 'secondary'
                            };
                        ?>
                            <tr>
                                <td><?= date('d.m.Y', strtotime($record['lesson_date'])) ?></td>
                                <td><?= h($record['course_name']) ?></td>
                                <td>
                                    <?= h($record['lesson_day'] ?? '') ?> 
                                    <?= h($record['lesson_start'] ?? '') ?>
                                    <?php if ($record['lesson_start'] && $record['lesson_end']): ?>
                                        - <?= h($record['lesson_end']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= h($record['class_name'] ?? 'N/A') ?></td>
                                <td><?= h($record['student_name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $statusColor ?>">
                                        <?= h($record['status']) ?>
                                    </span>
                                </td>
                                <td><?= h($record['notes'] ?? '') ?></td>
                                <td><?= h($record['entry_teacher_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>