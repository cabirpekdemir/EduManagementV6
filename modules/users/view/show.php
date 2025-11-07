<?php
// modules/users/view/show.php
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Kullanıcı Detay</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="index.php?module=dashboard">Ana Sayfa</a></li>
                    <li class="breadcrumb-item"><a href="index.php?module=users&action=index">Kullanıcılar</a></li>
                    <li class="breadcrumb-item active">Detay</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">Ad Soyad:</th>
                                <td><?= htmlspecialchars($user['name'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <th>E-posta:</th>
                                <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <th>Telefon:</th>
                                <td><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Rol:</th>
                                <td>
                                    <?php
                                    $roles = [
                                        'admin' => '👨‍💼 Admin',
                                        'teacher' => '👨‍🏫 Öğretmen',
                                        'student' => '👨‍🎓 Öğrenci',
                                        'parent' => '👨‍👩‍👦 Veli'
                                    ];
                                    echo $roles[$user['role']] ?? $user['role'];
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Durum:</th>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                        <span class="badge badge-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Pasif</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Kayıt Tarihi:</th>
                                <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                            </tr>
                        </table>
                        
                        <div class="mt-3">
                            <a href="index.php?module=users&action=edit&id=<?= $user['id'] ?>" 
                               class="btn btn-warning">
                                <i class="fa fa-edit"></i> Düzenle
                            </a>
                            <a href="index.php?module=users&action=index" 
                               class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Geri
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>