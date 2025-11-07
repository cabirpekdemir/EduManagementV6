<?php
// modules/student_enrollment/view/history.php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$history = $history ?? [];
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h4><i class="fa fa-history"></i> Ders Geçmişim</h4>
    </div>
    <div class="col-md-4 text-end">
        <a href="index.php?module=student_enrollment&action=index" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Ders Seçimine Dön
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (empty($history)): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> Henüz hiçbir ders kaydınız bulunmuyor.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Dönem</th>
                            <th>Ders Adı</th>
                            <th>Kategori</th>
                            <th>Durum</th>
                            <th>Not</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $h): ?>
                        <tr>
                            <td>
                                <small>
                                    <?= h($h['semester_year']) ?><br>
                                    <?= h($h['semester_period']) ?>. Dönem
                                </small>
                            </td>
                            <td><strong><?= h($h['course_name']) ?></strong></td>
                            <td>
                                <?php if ($h['course_category'] === 'akademi'): ?>
                                    <span class="badge bg-info">Akademi</span>
                                <?php elseif ($h['course_category'] === 'proje'): ?>
                                    <span class="badge bg-warning">Proje</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($h['status'] === 'active'): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php elseif ($h['status'] === 'completed'): ?>
                                    <span class="badge bg-primary">Tamamlandı</span>
                                <?php elseif ($h['status'] === 'cancelled'): ?>
                                    <span class="badge bg-danger">İptal</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= h($h['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($h['grade']): ?>
                                    <strong><?= h($h['grade']) ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>