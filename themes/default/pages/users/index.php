<h2>Kullanıcı Listesi</h2>

<!-- Buton AdminLTE stiliyle güncellendi -->
<a href="index.php?module=users&action=create" class="btn btn-primary mb-3">
    <i class="fa fa-user-plus"></i> Yeni Kullanıcı Ekle
</a>

<!-- Bildirim Mesajları -->
<?php if (!empty($_GET['success_message'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_GET['success_message']) ?>
    </div>
<?php endif; ?>
<?php if (!empty($_GET['error_message'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_GET['error_message']) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tüm Kullanıcılar</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Ad Soyad</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Sınıf</th>
                    <th style="width: 20%;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="5" class="text-center p-4">Sistemde kayıtlı kullanıcı bulunmamaktadır.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                            <td data-label="Ad Soyad">
                                <strong><?= htmlspecialchars($user['name'] ?? '') ?></strong><br>
                                <small class="text-muted">TC: <?= htmlspecialchars($user['tc_kimlik'] ?? '-') ?></small>
                            </td>
                            <td data-label="Email"><?= htmlspecialchars($user['email'] ?? '') ?></td>
                            <td data-label="Rol"><span class="badge badge-info"><?= htmlspecialchars($user['role'] ?? '') ?></span></td>
                            <td data-label="Sınıf"><?= htmlspecialchars($user['class_name'] ?? '-') ?></td>
                            <td data-label="İşlemler">
                                <?php if (($user['role'] ?? '') !== 'admin'): ?>
                                    <a href="index.php?module=users&action=edit&id=<?= htmlspecialchars($user['id'] ?? '') ?>" class="btn btn-sm btn-warning" title="Düzenle">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <a href="index.php?module=users&action=delete&id=<?= htmlspecialchars($user['id'] ?? '') ?>" class="btn btn-sm btn-danger ml-1" onclick="return confirm('Bu kullanıcıyı silmek ve arşivlemek istediğinize emin misiniz?')" title="Sil">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="badge badge-secondary">🔒 Admin</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
