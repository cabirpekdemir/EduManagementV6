<?php
// /modules/parents_students/index.php
// Bu dosya controller tarafından çağrıldığı için tüm değişkenler ($relations, $csrf_token vb.) zaten mevcuttur.
?>

<?php
// --- Geri bildirim mesajını göster ---
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    // AdminLTE'nin callout bileşenini kullanarak daha şık bir görünüm
    $alert_class = '';
    if ($message['type'] === 'success') $alert_class = 'alert-success';
    if ($message['type'] === 'danger') $alert_class = 'alert-danger';
    if ($message['type'] === 'warning') $alert_class = 'alert-warning';
    
    echo '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert">';
    echo htmlspecialchars($message['message']);
    echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
    echo '</div>';
    
    unset($_SESSION['flash_message']); // Mesajı gösterdikten sonra temizle
}
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Veli - Öğrenci İlişki Listesi</h3>
        <div class="card-tools">
            <a href="?module=parents_students&action=create" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Yeni İlişki Ekle
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($relations)): ?>
            <div class="alert alert-info">Henüz veli-öğrenci ilişkisi eklenmemiş.</div>
        <?php else: ?>
            <form method="POST" action="?module=parents_students&action=delete" id="delete-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 10px"><input type="checkbox" id="select-all-checkbox"></th>
                            <th>Veli Adı</th>
                            <th>Öğrenci Adı</th>
                            <th style="width: 80px">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($relations as $rel): ?>
                        <tr>
                            <td><input type="checkbox" class="delete-checkbox" name="delete_ids[]" value="<?= $rel['parent_id'] ?>-<?= $rel['student_id'] ?>"></td>
                            <td><?= htmlspecialchars($rel['parent_name']) ?></td>
                            <td><?= htmlspecialchars($rel['student_name']) ?></td>
                            <td>
                                <a href="?module=parents_students&action=delete&pid=<?= $rel['parent_id'] ?>&sid=<?= $rel['student_id'] ?>&csrf_token=<?= htmlspecialchars($csrf_token) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bu ilişkiyi silmek istediğinize emin misiniz?')">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <br>
                <button type="submit" class="btn btn-danger" onclick="return confirm('Seçilen tüm ilişkileri silmek istediğinize emin misiniz?')">
                    <i class="fa fa-trash"></i> Seçilenleri Sil
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
// extraFoot değişkenine JS kodunu ekleyerek layout'a gönderiyoruz
$extraFoot = '
<script>
document.addEventListener("DOMContentLoaded", function () {
    const selectAllCheckbox = document.getElementById("select-all-checkbox");
    if(selectAllCheckbox) {
        selectAllCheckbox.addEventListener("click", function () {
            document.querySelectorAll(".delete-checkbox").forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    }
});
</script>
';
?>