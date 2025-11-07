<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$teacher = $teacher ?? [];
$isEdit = $isEdit ?? false;
$val = fn($k) => h($teacher[$k] ?? '');

// Oluşturulan şifre varsa göster
$generatedPass = $_SESSION['generated_password'] ?? null;
unset($_SESSION['generated_password']);

// Flash mesajlar
if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?= h($_SESSION['flash']['type']) ?> alert-dismissible fade show">
        <?= h($_SESSION['flash']['msg']) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php unset($_SESSION['flash']); endif;
?>

<?php if ($generatedPass): ?>
    <div class="alert alert-success alert-dismissible">
        <strong>Oluşturulan Şifre:</strong> <code class="fs-5"><?= h($generatedPass) ?></code>
        <br><small>Bu şifreyi güvenli bir yere kaydedin!</small>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0"><?= $isEdit ? 'Öğretmen Düzenle' : 'Yeni Öğretmen' ?></h5>
    </div>
    <div class="card-body">
        <form method="post" action="index.php?module=teachers&action=<?= $isEdit ? 'update' : 'store' ?>" enctype="multipart/form-data">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int)$teacher['id'] ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= $val('name') ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">E-posta <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?= $val('email') ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">TC Kimlik <span class="text-danger">*</span></label>
                    <input type="text" name="tc_kimlik" class="form-control" 
                           value="<?= $val('tc_kimlik') ?>" 
                           pattern="[1-9][0-9]{10}" 
                           maxlength="11" 
                           required
                           placeholder="11 haneli TC">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Telefon</label>
                    <input type="text" name="phone" class="form-control" 
                           value="<?= $val('phone') ?>" 
                           placeholder="5XX XXX XX XX">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Branş</label>
                    <input type="text" name="branch" class="form-control" 
                           value="<?= $val('branch') ?>" 
                           placeholder="Matematik, Fizik...">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Mezun Olduğu Okul</label>
                    <input type="text" name="graduated_school" class="form-control" 
                           value="<?= $val('graduated_school') ?>" 
                           placeholder="Üniversite adı">
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        <?= $isEdit ? 'Yeni Şifre (boş bırakılırsa değişmez)' : 'Şifre (boş bırakılırsa otomatik)' ?>
                    </label>
                    <input type="password" name="password" class="form-control" 
                           placeholder="<?= $isEdit ? 'Değiştirmek için girin' : 'Otomatik oluşturulacak' ?>">
                </div>

                <?php if ($isEdit && !empty($teacher['profile_photo'])): ?>
                <div class="col-md-12">
                    <label class="form-label">Mevcut Profil Fotoğrafı</label>
                    <div class="mb-2">
                        <img src="<?= h($teacher['profile_photo']) ?>" 
                             style="width:100px;height:100px;object-fit:cover;border-radius:50%;">
                    </div>
                </div>
                <?php endif; ?>

                <div class="col-md-6">
                    <label class="form-label">Profil Fotoğrafı</label>
                    <input type="file" name="profile_photo" class="form-control" accept="image/*">
                    <small class="text-muted">JPG, PNG veya WEBP (maks. 2MB)</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label d-block">&nbsp;</label>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" 
                               class="form-check-input" id="isActive"
                               <?= !$isEdit || ($teacher['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isActive">
                            Aktif
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <a href="index.php?module=teachers&action=<?= $isEdit ? 'show&id='.(int)$teacher['id'] : 'index' ?>" 
                   class="btn btn-outline-secondary">
                    <i class="fa fa-times"></i> İptal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> <?= $isEdit ? 'Güncelle' : 'Kaydet' ?>
                </button>
            </div>
        </form>
    </div>
</div>