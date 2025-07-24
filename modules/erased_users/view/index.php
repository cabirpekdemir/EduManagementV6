<h2>Arşivlenmiş Kullanıcılar</h2>
<p class="lead">Sistemden silinmiş ve arşive taşınmış kullanıcıların listesi. Buradan kullanıcıları geri yükleyebilir veya kalıcı olarak silebilirsiniz.</p>

<!-- Bildirim Mesajları -->
<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Arşivdeki Kullanıcılar</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Ad Soyad</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Silinme Tarihi</th>
                    <th style="width: 25%;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($erased_users)): ?>
                    <tr>
                        <td colspan="5" class="text-center p-4">Arşivde hiç kullanıcı bulunmamaktadır.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($erased_users as $u): ?>
                        <tr>
                            <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                            <td data-label="Ad Soyad"><strong><?= htmlspecialchars($u['name'] ?? '') ?></strong></td>
                            <td data-label="Email"><?= htmlspecialchars($u['email'] ?? '') ?></td>
                            <td data-label="Rol"><span class="badge badge-secondary"><?= htmlspecialchars($u['role'] ?? '') ?></span></td>
                            <td data-label="Silinme Tarihi"><?= htmlspecialchars(date('d.m.Y H:i', strtotime($u['erased_at'] ?? ''))) ?></td>
                            <td data-label="İşlemler">
                                <a href="index.php?module=erased_users&action=restore&id=<?= htmlspecialchars($u['id']) ?>" class="btn btn-sm btn-info" onclick="return confirm('Bu kullanıcıyı sisteme geri yüklemek istediğinize emin misiniz?')">
                                    <i class="fa fa-undo"></i> Geri Yükle
                                </a>
                                <a href="index.php?module=erased_users&action=delete&id=<?= htmlspecialchars($u['id']) ?>" class="btn btn-sm btn-danger ml-1" onclick="return confirm('DİKKAT! Bu kullanıcı tüm verileriyle birlikte kalıcı olarak silinecektir. Bu işlem geri alınamaz! Emin misiniz?')">
                                    <i class="fa fa-times-circle"></i> Kalıcı Sil
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
