<h2>Öğrenci Listesi</h2>

<!-- Buton AdminLTE stiliyle güncellendi -->
<!-- Öğrenciler genellikle Kullanıcı Yönetimi'nden eklendiği için bu buton oraya yönlendirebilir -->
<a href="index.php?module=users&action=create&role=student" class="btn btn-primary mb-3">
    <i class="fa fa-user-plus"></i> Yeni Öğrenci Ekle
</a>

<!-- Başarı veya Hata Mesajları için Alan -->
<?php if (isset($status_message)): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($status_message) ?>
    </div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($error_message) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tüm Öğrenciler</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Öğrenci</th>
                    <th>Sınıfı</th>
                    <th>Danışman Öğretmen</th>
                    <th>Velisi</th>
                    <th style="width: 20%;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="5" class="text-center p-4">Sistemde kayıtlı öğrenci bulunmamaktadır.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $student_row): ?>
                        <tr>
                            <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                            <td data-label="Öğrenci">
                                <strong><?= htmlspecialchars($student_row['name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($student_row['email']) ?></small>
                            </td>
                            <td data-label="Sınıfı"><?= htmlspecialchars($student_row['class_name'] ?? 'Atanmamış') ?></td>
                            <td data-label="Danışmanı"><?= htmlspecialchars($student_row['teacher_name'] ?? 'Atanmamış') ?></td>
                            <td data-label="Velisi"><?= htmlspecialchars($student_row['parent_name'] ?? 'Atanmamış') ?></td>
                            <td data-label="İşlemler">
                                <a href="index.php?module=students&action=edit&id=<?= htmlspecialchars($student_row['id']) ?>" class="btn btn-sm btn-info">
                                    <i class="fa fa-pencil"></i> Detay/Ata
                                </a> 
                                <a href="index.php?module=users&action=delete&id=<?= htmlspecialchars($student_row['id']) ?>" class="btn btn-sm btn-danger ml-1" onclick="return confirm('Bu öğrenciyi silip arşive taşımak istediğinize emin misiniz?')">
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
