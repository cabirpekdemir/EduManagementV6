<h2>Ödev Listesi</h2>

<!-- Buton AdminLTE stiliyle güncellendi -->
<a href="index.php?module=assignments&action=create" class="btn btn-primary mb-3">
    <i class="fa fa-upload"></i> Yeni Ödev Yükle
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
        <h3 class="card-title">Tüm Ödevler</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Başlık</th>
                    <th>Ders</th>
                    <th>Oluşturulma Tarihi</th>
                    <th>Dosya</th>
                    <th>Not</th>
                    <th style="width: 20%;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assignments)): ?>
                    <tr>
                        <td colspan="6" class="text-center p-4">Henüz yüklenmiş bir ödev bulunmamaktadır.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assignments as $a): ?>
                        <tr>
                            <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                            <td data-label="Başlık"><strong><?= htmlspecialchars($a['title']) ?></strong></td>
                            <td data-label="Ders"><?= htmlspecialchars($a['course_name'] ?? ('Ders #' . $a['course_id'])) ?></td>
                            <td data-label="Tarih"><?= htmlspecialchars(date('d.m.Y H:i', strtotime($a['created_at']))) ?></td>
                            <td data-label="Dosya">
                                <?php if (!empty($a['filename'])): ?>
                                    <a href="<?= htmlspecialchars($a['filename']) ?>" class="btn btn-sm btn-info" target="_blank">
                                        <i class="fa fa-download"></i> Görüntüle/İndir
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Dosya Yok</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Not">
                                <span class="badge badge-light" style="font-size: 1em;">
                                    <?= htmlspecialchars($a['grade'] ?? '-') ?>
                                </span>
                            </td>
                            <td data-label="İşlemler">
                                <a href="index.php?module=assignments&action=edit&id=<?= htmlspecialchars($a['id']) ?>" class="btn btn-sm btn-warning">
                                    <i class="fa fa-pencil"></i> Düzenle
                                </a>
                                <a href="index.php?module=assignments&action=delete&id=<?= htmlspecialchars($a['id']) ?>" class="btn btn-sm btn-danger ml-1" onclick="return confirm('Bu ödevi silmek istediğinizden emin misiniz?')">
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
