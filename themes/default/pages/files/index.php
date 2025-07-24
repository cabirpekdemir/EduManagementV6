<h2>Paylaşılan Dosyalar</h2>

<!-- Buton AdminLTE stiliyle güncellendi -->
<a href="index.php?module=files&action=create" class="btn btn-primary mb-3">
    <i class="fa fa-upload"></i> Yeni Dosya Paylaş
</a>

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
    <div class="card-header">
        <h3 class="card-title">Tüm Dosyalar</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Dosya Adı</th>
                    <th>Açıklama</th>
                    <th>Yüklenme Tarihi</th>
                    <th style="width: 20%;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($files)): ?>
                    <tr>
                        <td colspan="4" class="text-center p-4">Henüz paylaşılmış bir dosya bulunmamaktadır.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($files as $file): ?>
                        <tr>
                            <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                            <td data-label="Dosya Adı">
                                <i class="fa fa-file-o" style="margin-right: 8px;"></i>
                                <a href="<?= htmlspecialchars($file['filename']) ?>" target="_blank">
                                    <?= htmlspecialchars(basename($file['filename'])) ?>
                                </a>
                            </td>
                            <td data-label="Açıklama"><?= htmlspecialchars($file['description']) ?></td>
                            <td data-label="Tarih"><?= htmlspecialchars(date('d.m.Y H:i', strtotime($file['uploaded_at']))) ?></td>
                            <td data-label="İşlem">
                                <a href="<?= htmlspecialchars($file['filename']) ?>" class="btn btn-sm btn-info" download>
                                    <i class="fa fa-download"></i> İndir
                                </a>
                                <!-- Adminler için silme butonu eklenebilir -->
                                <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
                                    <a href="index.php?module=files&action=delete&id=<?= htmlspecialchars($file['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu dosyayı kalıcı olarak silmek istediğinize emin misiniz?')">
                                        <i class="fa fa-trash"></i> Sil
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
