<?php
// /modules/parents_students/create.php
// Controller tarafından çağrıldığı için değişkenler ($veliler, $ogrenciler, $csrf_token) zaten mevcuttur.
?>

<?php
// --- Geri bildirim mesajını göster ---
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $alert_class = ($message['type'] === 'danger' || $message['type'] === 'warning') ? 'alert-warning' : 'alert-info';
    
    echo '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert">';
    echo htmlspecialchars($message['message']);
    echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
    echo '</div>';
    
    unset($_SESSION['flash_message']);
}
?>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Yeni Veli - Öğrenci İlişkisi Tanımla</h3>
    </div>
    <form method="POST" action="?module=parents_students&action=create">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="card-body">
            <div class="form-group">
                <label for="parent_id">Veli Seçin</label>
                <select name="parent_id" id="parent_id" class="form-control" required>
                    <option value="">-- Lütfen bir veli seçin --</option>
                    <?php foreach ($veliler as $veli): ?>
                    <option value="<?= $veli['id'] ?>"><?= htmlspecialchars($veli['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Öğrencileri Seçin (Birden fazla seçilebilir)</label>
                <?php if (empty($ogrenciler)): ?>
                    <p class="text-muted">Sistemde ilişkilendirilecek öğrenci bulunamadı.</p>
                <?php else: ?>
                    <div style="height: 300px; overflow-y: scroll; border: 1px solid #ced4da; padding: 10px; border-radius: .25rem;">
                        <?php foreach ($ogrenciler as $ogr): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="student_ids[]" value="<?= $ogr['id'] ?>" id="student_<?= $ogr['id'] ?>">
                            <label class="form-check-label" for="student_<?= $ogr['id'] ?>">
                                <?= htmlspecialchars($ogr['name']) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success">
                <i class="fa fa-check"></i> Kaydet
            </button>
            <a href="?module=parents_students" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>