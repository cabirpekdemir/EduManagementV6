<?php
if (!function_exists('h')) {
    function h($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$evaluation = $evaluation ?? [];
$students = $students ?? [];
$existing_results = $existing_results ?? [];

// Yazılı Sınav mı kontrol et
$is_written_exam = ($evaluation['evaluation_type'] ?? '') === 'Yazılı Sınav';
?>

<div class="mb-3">
    <a href="index.php?module=evaluations&action=index" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left"></i> Listeye Dön
    </a>
    <a href="index.php?module=evaluations&action=edit&id=<?= (int)$evaluation['id'] ?>" class="btn btn-outline-primary btn-sm">
        <i class="fa fa-edit"></i> Değerlendirmeyi Düzenle
    </a>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] === 'saved'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fa fa-check-circle"></i> Sonuçlar başarıyla kaydedildi!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">
            <?= h($evaluation['name'] ?? '') ?> - Sonuç Girişi
        </h5>
        <div class="text-muted small mt-1">
            <strong>Tür:</strong> <?= h($evaluation['evaluation_type'] ?? '') ?> | 
            <strong>Tarih:</strong> <?= h($evaluation['exam_date'] ?? '—') ?> 
            <?php if (!empty($evaluation['exam_time'])): ?>
                <?= date('H:i', strtotime($evaluation['exam_time'])) ?>
            <?php endif; ?>
            | <strong>Maks. Puan:</strong> <?= h($evaluation['max_score'] ?? '100') ?>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($students)): ?>
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle"></i>
                Bu değerlendirme için atanmış öğrenci bulunamadı. 
                Lütfen önce değerlendirmeyi düzenleyerek öğrenci atayın.
            </div>
        <?php else: ?>
            <form action="index.php?module=evaluations&action=store_results" method="post">
                <input type="hidden" name="evaluation_id" value="<?= (int)$evaluation['id'] ?>">
                
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th style="width: 250px;">Öğrenci Adı</th>
                                <th style="width: 100px;">Durum</th>
                                
                                <?php if ($is_written_exam): ?>
                                    <!-- YAZILI SINAV İÇİN ALT PUANLAR -->
                                    <th style="width: 90px;" class="text-center">
                                        Dil<br>
                                        <small class="text-muted">(Puan)</small>
                                    </th>
                                    <th style="width: 90px;" class="text-center">
                                        Şekil Uzay<br>
                                        <small class="text-muted">(Puan)</small>
                                    </th>
                                    <th style="width: 90px;" class="text-center">
                                        Ayırd Etme<br>
                                        <small class="text-muted">(Puan)</small>
                                    </th>
                                    <th style="width: 90px;" class="text-center">
                                        Sayısal<br>
                                        <small class="text-muted">(Puan)</small>
                                    </th>
                                    <th style="width: 90px;" class="text-center">
                                        Akıl Yürütme<br>
                                        <small class="text-muted">(Puan)</small>
                                    </th>
                                    <th style="width: 90px;" class="text-center">
                                        Genel<br>
                                        <small class="text-muted">(Puan)</small>
                                    </th>
                                <?php else: ?>
                                    <!-- DİĞER SINAV TÜRLERİ İÇİN TEK PUAN -->
                                    <th style="width: 120px;" class="text-center">
                                        Puan<br>
                                        <small class="text-muted">(Max: <?= h($evaluation['max_score']) ?>)</small>
                                    </th>
                                <?php endif; ?>
                                
                                <th>Yorum / Not</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $counter = 1;
                            foreach ($students as $student): 
                                $student_id = $student['id'];
                                $current_result = $existing_results[$student_id] ?? null;
                                
                                // Durum badge
                                $status = $student['enrollment_status'] ?? '';
                                $statusColors = [
                                    'on_kayit' => 'secondary',
                                    'sinav_secim' => 'primary',
                                    'aktif' => 'success',
                                    'mezun' => 'info'
                                ];
                                $statusTexts = [
                                    'on_kayit' => 'Ön Kayıt',
                                    'sinav_secim' => 'Sınav Seçim',
                                    'aktif' => 'Aktif',
                                    'mezun' => 'Mezun'
                                ];
                                $statusColor = $statusColors[$status] ?? 'secondary';
                                $statusText = $statusTexts[$status] ?? $status;
                            ?>
                            <tr>
                                <td class="text-center"><?= $counter++ ?></td>
                                <td>
                                    <strong><?= h($student['name']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $statusColor ?> small">
                                        <?= h($statusText) ?>
                                    </span>
                                </td>
                                
                                <?php if ($is_written_exam): ?>
                                    <!-- DİL -->
                                    <td>
                                        <input type="number" 
                                               name="results[<?= $student_id ?>][score_dil]" 
                                               class="form-control form-control-sm text-center"
                                               value="<?= h($current_result['score_dil'] ?? '') ?>"
                                               step="0.01" 
                                               min="0"
                                               placeholder="0">
                                    </td>
                                    
                                    <!-- ŞEKİL UZAY -->
                                    <td>
                                        <input type="number" 
                                               name="results[<?= $student_id ?>][score_sekil_uzay]" 
                                               class="form-control form-control-sm text-center"
                                               value="<?= h($current_result['score_sekil_uzay'] ?? '') ?>"
                                               step="0.01" 
                                               min="0"
                                               placeholder="0">
                                    </td>
                                    
                                    <!-- AYIRD ETME -->
                                    <td>
                                        <input type="number" 
                                               name="results[<?= $student_id ?>][score_ayird_etme]" 
                                               class="form-control form-control-sm text-center"
                                               value="<?= h($current_result['score_ayird_etme'] ?? '') ?>"
                                               step="0.01" 
                                               min="0"
                                               placeholder="0">
                                    </td>
                                    
                                    <!-- SAYISAL -->
                                    <td>
                                        <input type="number" 
                                               name="results[<?= $student_id ?>][score_sayisal]" 
                                               class="form-control form-control-sm text-center"
                                               value="<?= h($current_result['score_sayisal'] ?? '') ?>"
                                               step="0.01" 
                                               min="0"
                                               placeholder="0">
                                    </td>
                                    
                                    <!-- AKIL YÜRÜTME -->
                                    <td>
                                        <input type="number" 
                                               name="results[<?= $student_id ?>][score_akil_yurutme]" 
                                               class="form-control form-control-sm text-center"
                                               value="<?= h($current_result['score_akil_yurutme'] ?? '') ?>"
                                               step="0.01" 
                                               min="0"
                                               placeholder="0">
                                    </td>
                                    
                                    <!-- GENEL -->
                                    <td>
                                        <input type="number" 
                                               name="results[<?= $student_id ?>][score_genel]" 
                                               class="form-control form-control-sm text-center"
                                               value="<?= h($current_result['score_genel'] ?? '') ?>"
                                               step="0.01" 
                                               min="0"
                                               placeholder="0">
                                    </td>
                                    
                                    <!-- Toplam puan hesaplama için gizli input (opsiyonel) -->
                                    <input type="hidden" 
                                           name="results[<?= $student_id ?>][score]" 
                                           value="<?= h($current_result['score'] ?? '') ?>"
                                           class="total-score">
                                    
                                <?php else: ?>
                                    <!-- TEK PUAN (DİĞER SINAV TÜRLERİ) -->
                                    <td>
                                        <input type="number" 
                                               name="results[<?= $student_id ?>][score]" 
                                               class="form-control form-control-sm text-center"
                                               value="<?= h($current_result['score'] ?? '') ?>"
                                               step="0.01" 
                                               max="<?= h($evaluation['max_score']) ?>"
                                               min="0"
                                               placeholder="0">
                                    </td>
                                <?php endif; ?>
                                
                                <!-- YORUM -->
                                <td>
                                    <textarea name="results[<?= $student_id ?>][comments]" 
                                              class="form-control form-control-sm" 
                                              rows="1"
                                              placeholder="Not veya yorum..."><?= h($current_result['comments'] ?? '') ?></textarea>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($is_written_exam): ?>
                    <div class="alert alert-info mt-3">
                        <i class="fa fa-info-circle"></i>
                        <strong>Not:</strong> Yazılı sınav için alt puanlar girilmektedir. 
                        Her alan için sayısal değer giriniz.
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fa fa-save"></i> Tüm Sonuçları Kaydet
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
                        <i class="fa fa-refresh"></i> Sıfırla
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
// Sayısal değer kontrolü - sadece rakam, nokta ve virgül
document.addEventListener('DOMContentLoaded', function() {
    const numberInputs = document.querySelectorAll('input[type="number"]');
    
    numberInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            // Sadece rakam, nokta, virgül, backspace, delete izin ver
            const char = String.fromCharCode(e.which);
            if (!/[\d.,]/.test(char) && e.which !== 8 && e.which !== 0) {
                e.preventDefault();
            }
        });
        
        // Negatif değerleri engelle
        input.addEventListener('input', function() {
            if (parseFloat(this.value) < 0) {
                this.value = 0;
            }
        });
    });
});

// Otomatik toplam hesaplama (Yazılı Sınav için - opsiyonel)
<?php if ($is_written_exam): ?>
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const inputs = row.querySelectorAll('input[type="number"]:not(.total-score)');
        const totalInput = row.querySelector('.total-score');
        
        if (inputs.length > 0 && totalInput) {
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    let sum = 0;
                    inputs.forEach(inp => {
                        const val = parseFloat(inp.value) || 0;
                        sum += val;
                    });
                    totalInput.value = sum.toFixed(2);
                });
            });
        }
    });
});
<?php endif; ?>
</script>