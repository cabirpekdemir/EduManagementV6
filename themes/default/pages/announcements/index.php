<h2>Duyurular</h2>

<!-- Buton AdminLTE stiliyle güncellendi -->
<a href="index.php?module=announcements&action=create" class="btn btn-primary mb-3">
    <i class="fa fa-bullhorn"></i> Yeni Duyuru Ekle
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
        <h3 class="card-title">Son Duyurular</h3>
    </div>
    <!--
        Basit bir <ul> yerine AdminLTE'nin .list-group yapısını kullanmak
        hem daha şık durur hem de doğal olarak mobil uyumludur.
    -->
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            <?php if (isset($announcements) && !empty($announcements)): ?>
                <?php foreach ($announcements as $a): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <a href="index.php?module=announcements&action=view&id=<?= htmlspecialchars($a['id']) ?>">
                                <strong><?= htmlspecialchars($a['title']) ?></strong>
                            </a>
                            <br>
                            <small class="text-muted">
                                Yayınlanma Tarihi: <?= htmlspecialchars(date('d.m.Y H:i', strtotime($a['created_at']))) ?>
                            </small>
                        </div>
                        <div>
                            <!-- Adminler için düzenle/sil butonları -->
                            <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
                                <a href="index.php?module=announcements&action=edit&id=<?= htmlspecialchars($a['id']) ?>" class="btn btn-sm btn-warning">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="index.php?module=announcements&action=delete&id=<?= htmlspecialchars($a['id']) ?>" class="btn btn-sm btn-danger ml-1" onclick="return confirm('Bu duyuruyu silmek istediğinize emin misiniz?')">
                                    <i class="fa fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="list-group-item text-center p-4">Henüz görüntülenecek duyuru bulunmamaktadır.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>
