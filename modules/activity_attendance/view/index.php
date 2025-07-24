<h2>Etkinlik Yoklaması</h2>
<p class="lead">Yoklama almak için lütfen listeden bir etkinlik seçin.</p>

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
        <h3 class="card-title">Etkinlik Seçim Formu</h3>
    </div>
    <form method="GET" action="index.php">
        <div class="card-body">
            <input type="hidden" name="module" value="activity_attendance">
            <input type="hidden" name="action" value="take">

            <div class="form-group">
                <label for="activity_id_select"><b>Etkinlik Seçin:</b></label>
                <select name="activity_id" id="activity_id_select" required class="form-control">
                    <option value="">-- Etkinlik Seçiniz --</option>
                    <?php if(!empty($activities)): ?>
                        <?php foreach($activities as $activity): ?>
                            <option value="<?= htmlspecialchars($activity['id']) ?>">
                                <?= htmlspecialchars($activity['name']) ?> 
                                (<?= htmlspecialchars(date('d.m.Y', strtotime($activity['activity_date']))) ?>)
                                <?= !empty($activity['category_name']) ? ' - ' . htmlspecialchars($activity['category_name']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>Yoklaması alınacak aktif etkinlik bulunmamaktadır.</option>
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
        <p>Tüm etkinlikler için detaylı katılım raporlarını görüntüleyin.</p>
        <a href="index.php?module=activity_attendance&action=report" class="btn btn-info">
            <i class="fa fa-bar-chart"></i> Detaylı Etkinlik Yoklama Raporları
        </a>
    </div>
</div>
