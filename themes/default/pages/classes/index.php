<h2>Sınıflar Yönetimi</h2>

<!-- Buton AdminLTE stiliyle güncellendi -->
<a href="index.php?module=classes&action=create" class="btn btn-primary mb-3">
    <i class="fa fa-plus"></i> Yeni Sınıf Ekle
</a>

<!-- Başarı veya Hata Mesajları için Alan -->
<?php if (isset($_GET['status'])): ?>
    <div class="alert alert-success">
        <?php
        $messages = [
            'created' => 'Sınıf başarıyla oluşturuldu.',
            'updated' => 'Sınıf başarıyla güncellendi.',
            'deleted' => 'Sınıf başarıyla silindi.'
        ];
        echo htmlspecialchars($messages[$_GET['status']] ?? 'İşlem başarılı.');
        ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_GET['error']) // Hata mesajları doğrudan controller'dan gelebilir ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tüm Sınıflar</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th style="width: 10%;">ID</th>
                    <th>Sınıf Adı</th>
                    <th>Açıklama</th>
                    <th style="width: 25%;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($classes)): ?>
                    <?php foreach ($classes as $class_item): ?>
                    <tr>
                        <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                        <td data-label="ID"><?= htmlspecialchars($class_item['id']) ?></td>
                        <td data-label="Sınıf Adı"><strong><?= htmlspecialchars($class_item['name']) ?></strong></td>
                        <td data-label="Açıklama"><?= nl2br(htmlspecialchars($class_item['description'] ?? '')) ?></td>
                        <td data-label="İşlemler">
                            <a href="index.php?module=classes&action=edit&id=<?= htmlspecialchars($class_item['id']) ?>" class="btn btn-sm btn-warning">
                                <i class="fa fa-pencil"></i> Düzenle
                            </a>
                            <a href="index.php?module=exams&action=class_results&class_id=<?= htmlspecialchars($class_item['id']) ?>" class="btn btn-sm btn-info">
                               <i class="fa fa-bar-chart"></i> Sonuçlar
                            </a>
                            <a href="index.php?module=classes&action=delete&id=<?= htmlspecialchars($class_item['id']) ?>" class="btn btn-sm btn-danger ml-1" onclick="return confirm('Bu sınıfı silmek istediğinize emin misiniz?')">
                                <i class="fa fa-trash"></i> Sil
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center p-4">Kayıtlı sınıf bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
