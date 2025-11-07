<?php
if (isset($_SESSION['temp_password_display'])) {
    $tempData = $_SESSION['temp_password_display'];
    if ($tempData['teacher_id'] == $teacher['id']) {
        ?>
        <div class="alert alert-warning alert-dismissible fade show" style="border-left: 5px solid #ff9800;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h5><i class="fas fa-exclamation-triangle"></i> GEÇİCİ ŞİFRE</h5>
            <p class="mb-2">
                <strong><?= htmlspecialchars($tempData['name']) ?></strong> için oluşturulan giriş bilgileri:
            </p>
            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;">
                <p class="mb-1"><strong>E-posta:</strong> <?= htmlspecialchars($tempData['email']) ?></p>
                <p class="mb-0">
                    <strong>Geçici Şifre:</strong> 
                    <code style="font-size: 18px; background: #fff; padding: 5px 10px; color: #d32f2f; font-weight: bold;">
                        <?= htmlspecialchars($tempData['password']) ?>
                    </code>
                    <button class="btn btn-sm btn-info ml-2" onclick="copyPassword('<?= htmlspecialchars($tempData['password']) ?>')">
                        <i class="fas fa-copy"></i> Kopyala
                    </button>
                </p>
            </div>
            <small class="text-danger">
                <i class="fas fa-info-circle"></i> 
                Bu şifreyi öğretmene iletin. Sayfa yenilendiğinde kaybolacaktır.
            </small>
        </div>
        
        <script>
        function copyPassword(password) {
            navigator.clipboard.writeText(password).then(function() {
                alert('✅ Şifre panoya kopyalandı: ' + password);
            });
        }
        </script>
        <?php
        unset($_SESSION['temp_password_display']); // Bir kez göster
    }
    }
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$teacher = $teacher ?? [];
$courses = $courses ?? [];
$students = $students ?? [];

// Flash mesajlar
if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?= h($_SESSION['flash']['type']) ?> alert-dismissible fade show">
        <?= h($_SESSION['flash']['msg']) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php unset($_SESSION['flash']); endif;
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h4><i class="fa fa-user"></i> <?= h($teacher['name'] ?? 'Öğretmen') ?></h4>
    </div>
    <div class="col-md-4 text-end">
        <a href="index.php?module=teachers&action=index" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Listeye Dön
        </a>
        <a href="index.php?module=teachers&action=edit&id=<?= (int)($teacher['id'] ?? 0) ?>" class="btn btn-primary btn-sm ms-2">
            <i class="fa fa-edit"></i> Düzenle
        </a>
    </div>
</div>

<div class="row">
    <!-- Sol: Profil Bilgileri -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">Profil Bilgileri</h6>
            </div>
            <div class="card-body">
                <div class="mb-3 text-center">
                    <img src="<?= h($teacher['profile_photo'] ?? 'https://via.placeholder.com/120x120?text=User') ?>" 
                         class="rounded-circle" style="width:120px;height:120px;object-fit:cover;border:2px solid #ddd;">
                </div>
                
                <dl class="row mb-0 small">
                    <dt class="col-5">Ad Soyad:</dt>
                    <dd class="col-7"><?= h($teacher['name'] ?? '—') ?></dd>
                    
                    <dt class="col-5">Email:</dt>
                    <dd class="col-7"><?= h($teacher['email'] ?? '—') ?></dd>
                    
                    <dt class="col-5">TC Kimlik:</dt>
                    <dd class="col-7"><?= h($teacher['tc_kimlik'] ?? '—') ?></dd>
                    
                    <dt class="col-5">Telefon:</dt>
                    <dd class="col-7"><?= h($teacher['phone'] ?? '—') ?></dd>
                    
                    <dt class="col-5">Branş:</dt>
                    <dd class="col-7">
                        <?php if (!empty($teacher['branch'])): ?>
                            <span class="badge bg-info"><?= h($teacher['branch']) ?></span>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </dd>
                    
                    <dt class="col-5">Mezun:</dt>
                    <dd class="col-7">
                        <?= h($teacher['graduated_school'] ?? $teacher['mezun_okul'] ?? '—') ?>
                    </dd>
                    
                    <dt class="col-5">Durum:</dt>
                    <dd class="col-7">
                        <?php if (!empty($teacher['is_active'])): ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Pasif</span>
                        <?php endif; ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Sağ: Dersler ve Öğrenciler -->
    <div class="col-md-8">
        <!-- Dersler -->
        <div class="card shadow-sm mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fa fa-book"></i> Dersleri 
                    <span class="badge bg-primary"><?= count($courses) ?></span>
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($courses)): ?>
                    <div class="p-3 text-center text-muted">Henüz ders atanmamış</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Ders Adı</th>
                                    <th>Kategori</th>
                                    <th>Öğrenci Sayısı</th>
                                    <th class="text-end">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $c): ?>
                                <tr>
                                    <td><strong><?= h($c['name'] ?? '') ?></strong></td>
                                    <td>
                                        <?php if (($c['course_category'] ?? '') === 'akademi'): ?>
                                            <span class="badge bg-info">Akademi</span>
                                        <?php elseif (($c['course_category'] ?? '') === 'proje'): ?>
                                            <span class="badge bg-warning">Proje</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Normal</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= (int)($c['student_count'] ?? 0) ?> öğrenci</td>
                                    <td class="text-end">
                                        <a href="index.php?module=courses&action=show&id=<?= (int)$c['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            Detay
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div><!-- Dersler -->
<div class="card shadow-sm mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fa fa-book"></i> Dersleri 
            <span class="badge bg-primary"><?= count($courses) ?></span>
        </h6>
        <a href="index.php?module=teachers&action=assign_course&id=<?= (int)$teacher['id'] ?>" 
           class="btn btn-sm btn-success">
            <i class="fa fa-plus"></i> Ders Ata
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($courses)): ?>
            <div class="p-3 text-center text-muted">Henüz ders atanmamış</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Ders Adı</th>
                            <th>Kategori</th>
                            <th>Öğrenci Sayısı</th>
                            <th class="text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $c): ?>
                        <tr>
                            <td><strong><?= h($c['name'] ?? '') ?></strong></td>
                            <td>
                                <?php if (($c['course_category'] ?? '') === 'akademi'): ?>
                                    <span class="badge bg-info">Akademi</span>
                                <?php elseif (($c['course_category'] ?? '') === 'proje'): ?>
                                    <span class="badge bg-warning">Proje</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td><?= (int)($c['student_count'] ?? 0) ?> öğrenci</td>
                            <td class="text-end">
                                <a href="index.php?module=courses&action=show&id=<?= (int)$c['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    Detay
                                </a>
                                <a href="index.php?module=teachers&action=remove_course&teacher_id=<?= (int)$teacher['id'] ?>&course_id=<?= (int)$c['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Bu dersi öğretmenden kaldırmak istediğinize emin misiniz?')">
                                    Kaldır
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
        </div>

        <!-- Öğrenciler -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fa fa-users"></i> Öğrencileri 
                    <span class="badge bg-success"><?= count($students) ?></span>
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($students)): ?>
                    <div class="p-3 text-center text-muted">Henüz öğrenci kaydı yok</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Öğrenci Adı</th>
                                    <th>Sınıf</th>
                                    <th>Okul</th>
                                    <th class="text-end">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $s): ?>
                                <tr>
                                    <td><?= h($s['name'] ?? '') ?></td>
                                    <td><?= h($s['sinif'] ?? '—') ?></td>
                                    <td><small><?= h($s['okul'] ?? '—') ?></small></td>
                                    <td class="text-end">
                                        <a href="index.php?module=students&action=show&id=<?= (int)$s['id'] ?>" 
                                           class="btn btn-sm btn-outline-info">
                                            Profil
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>