<h2>Değerlendirme Yoklaması</h2>
<p class="lead">Yoklama almak için lütfen listeden bir değerlendirme (sınav/deneme) seçin.</p>

<!-- Bildirim Mesajları -->
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger">
        <strong>Hata:</strong> <?= htmlspecialchars($error_message) ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['status']) && $_GET['status'] === 'saved'): ?>
    <div class="alert alert-success">Yoklama başarıyla kaydedildi.</div>
<?php endif; ?>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Değerlendirme Seçim Formu</h3>
    </div>
    <form method="GET" action="index.php">
        <div class="card-body">
            <input type="hidden" name="module" value="exam_attendance">
            <input type="hidden" name="action" value="take">

            <div class="form-group">
                <label for="exam_id_select"><b>Değerlendirme Seçin:</b></label>
                <select name="exam_id" id="exam_id_select" required class="form-control">
                    <option value="">-- Değerlendirme Seçiniz --</option>
                    <?php 
                    // Controller'dan gelen 'evaluations' değişkeni kullanılıyor
                    if(!empty($evaluations)): 
                        foreach($evaluations as $evaluation): ?>
                            <option value="<?= htmlspecialchars($evaluation['id']) ?>">
                                <?= htmlspecialchars($evaluation['name']) ?> (Tarih: <?= htmlspecialchars(!empty($evaluation['exam_date']) ? date('d.m.Y', strtotime($evaluation['exam_date'])) : 'N/A') ?>)
                            </option>
                        <?php endforeach; 
                    else: ?>
                         <option value="" disabled>Yoklaması alınacak aktif değerlendirme bulunmamaktadır.</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success">
                <i class="fa fa-list-ul"></i> Yoklama Listesini Getir
            </button>
        </div>
    </form>
</div>

<hr>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Raporlar</h3>
    </div>
    <div class="card-body">
        <p>Tüm değerlendirmeler için detaylı katılım raporlarını görüntüleyin.</p>
        <a href="index.php?module=exam_attendance&action=report" class="btn btn-info">
            <i class="fa fa-bar-chart"></i> Detaylı Değerlendirme Yoklama Raporları
        </a>
    </div>
</div>
