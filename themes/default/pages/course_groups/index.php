<h2>Ders Grupları Yönetimi</h2>

<!-- Buton AdminLTE stiliyle güncellendi -->
<a href="index.php?module=course_groups&action=create" class="btn btn-primary mb-3">
    <i class="fa fa-plus"></i> Yeni Ders Grubu Ekle
</a>

<!-- Başarı veya Hata Mesajları için Alan -->
<?php if (isset($_GET['status'])): ?>
    <div class="alert alert-success">
        <?php
        $messages = [
            'created' => 'Ders grubu başarıyla oluşturuldu.',
            'updated' => 'Ders grubu başarıyla güncellendi.',
            'deleted' => 'Ders grubu başarıyla silindi.'
        ];
        echo htmlspecialchars($messages[$_GET['status']] ?? 'İşlem başarılı.');
        ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?php
        $errors = [
            'not_found' => 'Hata: Ders grubu bulunamadı.',
            'empty_name' => 'Hata: Ders grubu adı boş bırakılamaz.',
            'missing_id' => 'Hata: Geçersiz ID.'
        ];
        echo htmlspecialchars($errors[$_GET['error']] ?? 'Bilinmeyen bir hata oluştu.');
        ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tüm Ders Grupları</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Grup Adı</th>
                    <th>Açıklama</th>
                    <th>Oluşturan</th>
                    <th>Oluşturma Tarihi</th>
                    <th style="width: 25%;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($course_groups)): ?>
                    <?php foreach ($course_groups as $group): ?>
                    <tr>
                        <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                        <td data-label="Grup Adı"><strong><?= htmlspecialchars($group['name']) ?></strong></td>
                        <td data-label="Açıklama"><?= nl2br(htmlspecialchars($group['description'] ?? '')) ?></td>
                        <td data-label="Oluşturan"><?= htmlspecialchars($group['creator_name']) ?></td>
                        <td data-label="Tarih"><?= htmlspecialchars(date('d.m.Y H:i', strtotime($group['created_at']))) ?></td>
                        <td data-label="İşlemler">
                            <a href="index.php?module=course_groups&action=edit&id=<?= htmlspecialchars($group['id']) ?>" class="btn btn-sm btn-warning">
                                <i class="fa fa-pencil"></i> Düzenle
                            </a>
                            <a href="index.php?module=course_groups&action=list_group_students&group_id=<?= htmlspecialchars($group['id']) ?>" class="btn btn-sm btn-secondary">
                                <i class="fa fa-users"></i> Öğrenciler
                            </a>
                            <a href="index.php?module=course_groups&action=delete&id=<?= htmlspecialchars($group['id']) ?>" class="btn btn-sm btn-danger ml-1" onclick="return confirm('Bu ders grubunu silmek istediğinize emin misiniz? Bu işlem gruptaki tüm ders bağlantılarını da silecektir.')">
                                <i class="fa fa-trash"></i> Sil
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center p-4">Kayıtlı ders grubu bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
