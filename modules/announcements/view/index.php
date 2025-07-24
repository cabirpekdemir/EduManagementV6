<?php
// Gerekli değişkenler ($announcements, $userRole vb.) controller tarafından sağlanır.
?>
<h2>Duyurular</h2>

<!-- Rol kontrolü ile "Yeni Duyuru" butonu gösterilir -->
<?php if (in_array($userRole ?? 'guest', ['admin', 'teacher'])): ?>
    <a href="index.php?module=announcements&action=create" class="btn btn-primary mb-3">
        <i class="fa fa-bullhorn"></i> Yeni Duyuru Ekle
    </a>
<?php endif; ?>


<!-- Bildirim Mesajları -->
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
        <h3 class="card-title">Tüm Duyurular</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Başlık</th>
                    <th>Yayınlayan</th>
                    <th>Hedef Kitle</th>
                    <th>Durum</th>
                    <th style="width: 25%;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($announcements)): ?>
                    <tr><td colspan="5" class="text-center p-4">Görüntülenecek duyuru bulunmamaktadır.</td></tr>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <tr>
                            <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                            <td data-label="Başlık">
                                <strong><?= htmlspecialchars($announcement['title']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars(date('d.m.Y H:i', strtotime($announcement['created_at']))) ?></small>
                            </td>
                            <td data-label="Yayınlayan"><?= htmlspecialchars($announcement['creator_name']) ?></td>
                            <td data-label="Hedef Kitle"><span class="badge badge-light"><?= htmlspecialchars(ucfirst($announcement['target_role'])) ?></span></td>
                            <td data-label="Durum">
                                <?php
                                    $status = $announcement['status'] ?? 'bilinmiyor';
                                    $status_map = [
                                        'pending' => ['class' => 'badge-warning', 'text' => 'Onay Bekliyor'],
                                        'approved' => ['class' => 'badge-success', 'text' => 'Onaylandı'],
                                        'rejected' => ['class' => 'badge-danger', 'text' => 'Reddedildi']
                                    ];
                                    $s = $status_map[$status] ?? ['class' => 'badge-secondary', 'text' => ucfirst($status)];
                                ?>
                                <span class="badge <?= $s['class'] ?>"><?= $s['text'] ?></span>
                            </td>
                            <td data-label="İşlemler">
                                <a href="index.php?module=announcements&action=view&id=<?= htmlspecialchars($announcement['id']) ?>" class="btn btn-sm btn-info" title="Görüntüle">
                                    <i class="fa fa-eye"></i>
                                </a>

                                <!-- Admin için Onay/Reddet butonları -->
                                <?php if ($userRole === 'admin' && $announcement['status'] === 'pending'): ?>
                                    <a href="index.php?module=announcements&action=approve&id=<?= htmlspecialchars($announcement['id']) ?>" class="btn btn-sm btn-success" title="Onayla" onclick="return confirm('Bu duyuruyu onaylamak istediğinize emin misiniz?')">
                                        <i class="fa fa-check"></i>
                                    </a>
                                    <a href="index.php?module=announcements&action=reject&id=<?= htmlspecialchars($announcement['id']) ?>" class="btn btn-sm btn-danger" title="Reddet" onclick="return confirm('Bu duyuruyu reddetmek istediğinize emin misiniz?')">
                                        <i class="fa fa-times"></i>
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
