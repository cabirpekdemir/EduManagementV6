<?php
// modules/users/view/index.php
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Tüm Kullanıcılar</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="index.php?module=dashboard">Ana Sayfa</a></li>
                    <li class="breadcrumb-item active">Kullanıcılar</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        
        <!-- Kullanıcı Listesi -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kullanıcı Listesi</h3>
                <div class="card-tools">
                    <a href="index.php?module=users&action=create" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Yeni Kullanıcı
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Filtreler -->
                <form method="GET" class="mb-3">
                    <input type="hidden" name="module" value="users">
                    <input type="hidden" name="action" value="index">
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="btn-group btn-group-toggle mb-2" data-toggle="buttons">
                                <label class="btn btn-outline-primary <?= empty($_GET['role']) ? 'active' : '' ?>">
                                    <input type="radio" name="role" value="" <?= empty($_GET['role']) ? 'checked' : '' ?>> Tümü
                                </label>
                                <label class="btn btn-outline-primary <?= ($_GET['role'] ?? '') == 'teacher' ? 'active' : '' ?>">
                                    <input type="radio" name="role" value="teacher" <?= ($_GET['role'] ?? '') == 'teacher' ? 'checked' : '' ?>> Öğretmenler
                                </label>
                                <label class="btn btn-outline-primary <?= ($_GET['role'] ?? '') == 'student' ? 'active' : '' ?>">
                                    <input type="radio" name="role" value="student" <?= ($_GET['role'] ?? '') == 'student' ? 'checked' : '' ?>> Öğrenciler
                                </label>
                                <label class="btn btn-outline-primary <?= ($_GET['role'] ?? '') == 'parent' ? 'active' : '' ?>">
                                    <input type="radio" name="role" value="parent" <?= ($_GET['role'] ?? '') == 'parent' ? 'checked' : '' ?>> Veliler
                                </label>
                                <label class="btn btn-outline-primary <?= ($_GET['role'] ?? '') == 'admin' ? 'active' : '' ?>">
                                    <input type="radio" name="role" value="admin" <?= ($_GET['role'] ?? '') == 'admin' ? 'checked' : '' ?>> Adminler
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" name="q" 
                                       placeholder="Ad soyad / e-posta ara..." 
                                       value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-search"></i> Filtre
                                    </button>
                                    <a href="index.php?module=users&action=index" class="btn btn-secondary">
                                        Sıfırla
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                
                <!-- Tablo -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th width="50">ID</th>
                                <th>AD SOYAD</th>
                                <th>EMAIL</th>
                                <th>ROL</th>
                                <th width="100">SINIF</th>
                                <th>TELEFON</th>
                                <th>OKUL</th>
                                <th width="80">NOT</th>
                                <th width="150" class="text-center">İŞLEMLER</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        Kayıt bulunamadı
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>#<?= $user['id'] ?></td>
                                        <td><?= htmlspecialchars($user['name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                        <td>
                                            <?php
                                            $roles = [
                                                'admin' => '<span class="badge badge-danger">Admin</span>',
                                                'teacher' => '<span class="badge badge-primary">Öğretmen</span>',
                                                'student' => '<span class="badge badge-info">Öğrenci</span>',
                                                'parent' => '<span class="badge badge-warning">Veli</span>'
                                            ];
                                            echo $roles[$user['role']] ?? $user['role'];
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($user['sinif'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($user['phone'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($user['okul'] ?? '—') ?></td>
                                        <td class="text-center">
                                            <?php if ($user['is_active']): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <!-- ⭐ DİKKAT: $user['id'] kullan -->
                                            <a href="index.php?module=users&action=show&id=<?= $user['id'] ?>" 
                                               class="btn btn-sm btn-success" 
                                               title="Görüntüle">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="index.php?module=users&action=edit&id=<?= $user['id'] ?>" 
                                               class="btn btn-sm btn-warning" 
                                               title="Düzenle">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="index.php?module=users&action=delete&id=<?= $user['id'] ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('<?= htmlspecialchars($user['name']) ?> kullanıcısını silmek istediğinizden emin misiniz?')"
                                               title="Sil">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-2">
                    <small class="text-muted">
                        Toplam <?= count($users) ?> kullanıcı
                    </small>
                </div>
            </div>
        </div>
    </div>
</section>