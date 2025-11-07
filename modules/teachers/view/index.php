<h2>Öğretmen Listesi</h2>

<!-- Butonlar AdminLTE stiliyle güncellendi -->
<!-- Not: Öğretmenler, kullanıcı yönetimi ekranından eklendiği için burada ayrı bir "ekle" butonu olmayabilir. -->
<!-- Eğer gerekliyse, aşağıdaki satırın yorumunu kaldırabilirsiniz. -->
<!-- <a href="index.php?module=users&action=create&role=teacher" class="btn btn-primary mb-3"><i class="fa fa-plus"></i> Yeni Öğretmen Ekle</a> -->


<!-- Başarı veya Hata Mesajları için Alan -->
<?php if (!empty($_GET['status_message'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_GET['status_message']) ?>
    </div>
<?php endif; ?>
<?php if (!empty($_GET['error_message'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_GET['error_message']) ?>
    </div>
<?php endif; ?>

<div class="card">
    
  <div class="card-header d-flex justify-content-between align-items-right">
    <h3 class="card-title mb-0">Tüm Öğretmenler</h3>
    <a href="index.php?module=users&action=create" class="btn btn-primary">
      <i class="fa fa-plus"></i> Yeni Öğretmen Ekle
    </a>
  </div>
  

    <div class="card-body p-0">
        <table class="table table-striped projects">
            <thead>
                <tr>
                    <th style="width: 1%">#</th>
                    <th style="width: 30%">Öğretmen</th>
                    <th>İletişim</th>
                    <th>Verdiği Dersler</th>
                    <th style="width: 20%">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($teachers)): ?>
                    <tr>
                        <td colspan="5" class="text-center p-4">Sistemde kayıtlı öğretmen bulunmamaktadır.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($teachers as $teacher): ?>
                        <tr>
                            <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                            <td data-label="#">
                                <img alt="Avatar" class="table-avatar" src="<?= htmlspecialchars(!empty($teacher['profile_photo']) ? $teacher['profile_photo'] : 'uploads/defaultuser.png') ?>" style="width: 40px; height: 40px; border-radius: 50%;">
                            </td>
                            <td data-label="Öğretmen">
                                <strong><?= htmlspecialchars($teacher['name']) ?></strong>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($teacher['branch'] ?? 'Branş Belirtilmemiş') ?></small>
                            </td>
                            <td data-label="İletişim"><?= htmlspecialchars($teacher['email']) ?></td>
                            <td data-label="Verdiği Dersler">
                                <?php
                                // Bu kısım için controller'da öğretmenin derslerinin çekilmesi gerekir.
                                // Örnek bir gösterim:
                                if (!empty($teacher['courses'])) {
                                    foreach($teacher['courses'] as $course) {
                                        echo '<span class="badge badge-info mr-1">' . htmlspecialchars($course) . '</span>';
                                    }
                                } else {
                                    echo '<span class="text-muted">Ders atanmamış</span>';
                                }
                                ?>
                            </td>
                            <td data-label="İşlemler" class="project-actions text-right">
                                <a class="btn btn-primary btn-sm" href="index.php?module=teachers&action=show&id=<?= htmlspecialchars($teacher['id']) ?>">
                                    <i class="fa fa-folder"></i> Görüntüle
                                </a>
                                <a class="btn btn-warning btn-sm" href="index.php?module=teachers&action=edit&id=<?= htmlspecialchars($teacher['id']) ?>">
                                    <i class="fa fa-pencil"></i> Düzenle
                                </a>
                                <a class="btn btn-danger btn-sm" href="index.php?module=teachers&action=delete&id=<?= htmlspecialchars($teacher['id']) ?>" onclick="return confirm('Bu öğretmeni silmek istediğinize emin misiniz?')">
                                    <i class="fa fa-trash"></i> Sil
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
