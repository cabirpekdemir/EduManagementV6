<?php
if (!function_exists('e')) { 
    function e($v) { 
        return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); 
    } 
}

$base = e($_SERVER['PHP_SELF'] ?? 'index.php');
$isEdit = isset($user) && isset($user['id']);
$action = $isEdit ? 'update' : 'store';
$u = $user ?? [];
$val = fn($k) => e($u[$k] ?? '');
$role = strtolower($u['role'] ?? 'student');

// Oluşturulan şifreyi göster
$generatedPass = $_SESSION['generated_password'] ?? null;
unset($_SESSION['generated_password']);
?>

<?php if ($generatedPass): ?>
<div class="alert alert-success">
    <strong>Oluşturulan Şifre:</strong> <?= e($generatedPass) ?>
    <br><small>Bu şifreyi güvenli bir yere not edin!</small>
</div>
<?php endif; ?>

<form method="post" action="<?= $base ?>?module=users&action=<?= $action ?>">
  <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
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
      <input type="text" name="tc_kimlik" class="form-control" value="<?= $val('tc_kimlik') ?>" 
             pattern="[1-9][0-9]{10}" maxlength="11" required
             placeholder="11 haneli TC">
    </div>
    
    <div class="col-md-4">
      <label class="form-label">Rol <span class="text-danger">*</span></label>
      <select name="role" id="roleSelect" class="form-control" required>
        <?php foreach([
          'student' => 'Öğrenci', 
          'teacher' => 'Öğretmen', 
          'guidance' => 'Rehber Öğretmen / Psikolog', 
          'parent' => 'Veli', 
          'admin' => 'Admin'
        ] as $k => $t): ?>
          <option value="<?= e($k) ?>" <?= $role === $k ? 'selected' : '' ?>><?= e($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label">
        <?= $isEdit ? 'Yeni Şifre (boş bırakılırsa değişmez)' : 'Şifre (boş bırakılırsa otomatik oluşturulur)' ?>
      </label>
      <input type="password" name="password" class="form-control" 
             <?= $isEdit ? '' : 'placeholder="Otomatik oluşturulacak"' ?>>
    </div>

    <div class="col-md-4">
      <label class="form-label">Telefon</label>
      <input type="text" name="telefon" class="form-control" 
             value="<?= $val('telefon') ?: $val('tel') ?: $val('phone') ?>">
    </div>

    <div class="col-md-4">
      <label class="form-label">Okul</label>
      <input type="text" name="okul" class="form-control" value="<?= $val('okul') ?: $val('school') ?>">
    </div>

    <div class="col-md-4">
      <label class="form-label">
        <input type="checkbox" name="is_active" value="1" <?= !$isEdit || ($u['is_active'] ?? 1) ? 'checked' : '' ?>>
        Aktif
      </label>
    </div>

    <!-- ROL: ÖĞRETMEN -->
    <div class="col-12 role-teacher" style="display:none;">
      <h6 class="border-bottom pb-2">Öğretmen Bilgileri</h6>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Branş</label>
          <input type="text" name="brans" class="form-control" 
                 value="<?= $val('brans') ?: $val('branch') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Mezun Olduğu Okul</label>
          <input type="text" name="mezun_okul" class="form-control" 
                 value="<?= $val('mezun_okul') ?: $val('graduation_school') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Kademe</label>
          <input type="text" name="kademe" class="form-control" 
                 value="<?= $val('kademe') ?: $val('level') ?>" 
                 placeholder="İlkokul/Ortaokul">
        </div>
      </div>
    </div>

    <!-- ROL: REHBER ÖĞRETMEN / PSİKOLOG -->
    <div class="col-12 role-guidance" style="display:none;">
      <h6 class="border-bottom pb-2">
        <i class="fa fa-user-md"></i> Rehber Öğretmen / Psikolog Bilgileri
      </h6>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Uzmanlık Alanı</label>
          <select name="brans" class="form-control">
            <option value="">Seçiniz</option>
            <option value="Rehber Öğretmen" <?= $val('brans') === 'Rehber Öğretmen' ? 'selected' : '' ?>>Rehber Öğretmen</option>
            <option value="Psikolog" <?= $val('brans') === 'Psikolog' ? 'selected' : '' ?>>Psikolog</option>
            <option value="Psikolojik Danışman" <?= $val('brans') === 'Psikolojik Danışman' ? 'selected' : '' ?>>Psikolojik Danışman</option>
            <option value="Özel Eğitim Uzmanı" <?= $val('brans') === 'Özel Eğitim Uzmanı' ? 'selected' : '' ?>>Özel Eğitim Uzmanı</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Mezun Olduğu Okul</label>
          <input type="text" name="mezun_okul" class="form-control" 
                 value="<?= $val('mezun_okul') ?: $val('graduation_school') ?>"
                 placeholder="Üniversite">
        </div>
        <div class="col-md-4">
          <label class="form-label">Görev Yılı</label>
          <input type="number" name="kademe" class="form-control" 
                 value="<?= $val('kademe') ?: $val('level') ?>" 
                 placeholder="Örn: 2018">
        </div>
      </div>
    </div>

    <!-- ROL: VELİ -->
    <div class="col-12 role-parent" style="display:none;">
      <h6 class="border-bottom pb-2">Veli Bilgileri</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">İşyeri</label>
          <input type="text" name="isyeri" class="form-control" 
                 value="<?= $val('isyeri') ?: $val('employer') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Meslek</label>
          <input type="text" name="meslek" class="form-control" 
                 value="<?= $val('meslek') ?: $val('job_title') ?>">
        </div>
      </div>
    </div>

    <!-- ROL: ÖĞRENCİ -->
    <div class="col-12 role-student" style="display:none;">
      <h6 class="border-bottom pb-2">Öğrenci Bilgileri</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Sınıf</label>
          <input type="text" name="sinif" class="form-control" 
                 value="<?= $val('sinif') ?: $val('class_name') ?>">
        </div>
      </div>
    </div>

    <div class="col-12">
      <label class="form-label">Not</label>
      <textarea name="note" class="form-control" rows="3"><?= $val('note') ?: $val('admin_note') ?></textarea>
    </div>
  </div>

  <div class="mt-3 d-flex gap-2">
    <a class="btn btn-outline-secondary" href="<?= $base ?>?module=users&action=index">İptal</a>
    <button class="btn btn-primary"><?= $isEdit ? 'Güncelle' : 'Kaydet' ?></button>
  </div>
</form>

<script>
(function() {
  const roleSel = document.getElementById('roleSelect');
  const blocks = {
    teacher: document.querySelectorAll('.role-teacher'),
    guidance: document.querySelectorAll('.role-guidance'),
    parent:  document.querySelectorAll('.role-parent'),
    student: document.querySelectorAll('.role-student')
  };
  
  function apply(role) {
    ['teacher', 'guidance', 'parent', 'student'].forEach(r => {
      blocks[r].forEach(el => el.style.display = (r === role) ? 'block' : 'none');
    });
  }
  
  apply(roleSel.value || 'student');
  roleSel.addEventListener('change', e => apply(e.target.value || 'student'));
})();
</script>
