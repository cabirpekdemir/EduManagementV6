<h2>Etkinlik Kategorileri Yönetimi</h2>

<!-- Buton AdminLTE stiliyle güncellendi -->
<a href="index.php?module=activity_categories&action=create" class="btn btn-primary mb-3">
    <i class="fa fa-plus"></i> Yeni Kategori Ekle
</a>

<!-- Bildirim Mesajları -->
<?php if (isset($_GET['status'])): ?>
    <div class="alert alert-success">
        <?php
        $messages = [
            'created' => 'Kategori başarıyla oluşturuldu.',
            'updated' => 'Kategori başarıyla güncellendi.',
            'deleted' => 'Kategori başarıyla silindi.'
        ];
        echo htmlspecialchars($messages[$_GET['status']] ?? 'İşlem başarılı.');
        ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?php
        $errors = [
            'not_found' => 'Hata: Kategori bulunamadı.',
            'empty_name' => 'Hata: Kategori adı boş bırakılamaz.',
            'name_exists' => 'Hata: Bu kategori adı zaten mevcut.',
            'missing_id' => 'Hata: Geçersiz ID.',
            'category_in_use' => 'Hata: Bu kategori etkinliklerde kullanıldığı için silinemez.'
        ];
        echo htmlspecialchars($errors[$_GET['error']] ?? 'Bilinmeyen bir hata oluştu.');
        ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tüm Kategoriler</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th style="width: 10%;">ID</th>
                    <th>Kategori Adı</th>
                    <th>Açıklama</th>
                    <th style="width: 15%;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                        <td data-label="ID"><?= htmlspecialchars($category['id']) ?></td>
                        <td data-label="Kategori Adı"><strong><?= htmlspecialchars($category['name']) ?></strong></td>
                        <td data-label="Açıklama"><?= nl2br(htmlspecialchars($category['description'] ?? '')) ?></td>
                        <td data-label="İşlemler">
                            <a href="index.php?module=activity_categories&action=edit&id=<?= htmlspecialchars($category['id']) ?>" class="btn btn-sm btn-warning">
                                <i class="fa fa-pencil"></i>
                            </a>
                            <a href="index.php?module=activity_categories&action=delete&id=<?= htmlspecialchars($category['id']) ?>" class="btn btn-sm btn-danger ml-1" 
                               onclick="return confirm('Bu kategoriyi silmek istediğinize emin misiniz? Bu kategoriye bağlı etkinliklerin kategorisi kaldırılacaktır.')">
                               <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center p-4">Kayıtlı etkinlik kategorisi bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
