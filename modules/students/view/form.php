<?php
if (!function_exists('h')) { 
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); } 
}

$errors = $errors ?? [];
$isEdit = $isEdit ?? false;
$formAction = $formAction ?? 'index.php?module=students&action=store';
$role = $role ?? 'guest';
$student = $student ?? [];
$teachers = $teachers ?? [];
$classes = $classes ?? [];
$selTeacher = $selectedTeacherId ?? ($student['teacher_id'] ?? null);
$selClass = $selectedClassId ?? ($student['class_id'] ?? null);

// Varsayılan değerler
$s = array_merge([
    'name'=>'','email'=>'','tc_kimlik'=>'','phone'=>'','phone2'=>'','phone3'=>'',
    'okul'=>'','sinif'=>'','class_id'=>'','teacher_id'=>'',
    'teaching_type'=>'','special_talent'=>0,'enrollment_status'=>'on_kayit','student_number'=>'',
    'birth_place'=>'','birth_date'=>'','gender'=>'',
    'address'=>'','mother_name'=>'','father_name'=>'','guardian_name'=>'',
    'student_note'=>'','note'=>'','profile_photo'=>'','is_active'=>1,'approved'=>1,
    'chronic_condition'=>'','medications'=>'','allergy'=>'','blood_type'=>'','health_notes'=>''
], $student);

$photo = $s['profile_photo'] ?: 'https://via.placeholder.com/100x100?text=📷';
?>

<div class="mb-2">
    <a href="index.php?module=students&action=list" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left"></i> Listeye Dön
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <h6 class="alert-heading mb-2">Lütfen aşağıdaki hataları düzeltin:</h6>
        <ul class="mb-0 small">
            <?php foreach ($errors as $error): ?>
                <li><?= h($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form action="<?= h($formAction) ?>" method="post" enctype="multipart/form-data">
    <div class="card shadow-sm">
        <div class="card-header">
            <h6 class="mb-0"><?= $isEdit ? 'Öğrenciyi Düzenle' : 'Yeni Öğrenci' ?></h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Profil Fotoğrafı - Sol -->
                <div class="col-md-2">
                    <div class="text-center">
                        <img id="photoPreview" src="<?= h($photo) ?>" 
                             class="rounded-circle border mb-2" 
                             style="width:100px;height:100px;object-fit:cover;">
                        <input type="file" name="profile_photo" accept="image/*" 
                               class="form-control form-control-sm" onchange="previewPhoto(this)">
                        <div class="form-check mt-2">
                            <input type="checkbox" name="is_active" value="1" 
                                   class="form-check-input" id="is_active"
                                   <?= !empty($s['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="is_active">Aktif</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="approved" value="1" 
                                   class="form-check-input" id="approved"
                                   <?= !empty($s['approved']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="approved">Onaylı</label>
                        </div>
                    </div>
                </div>

                <!-- Form Alanları - Sağ -->
                <div class="col-md-10">
                    <div class="row g-2">
                        <!-- 1. SATIR -->
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Ad Soyad <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" 
                                   required value="<?= h($s['name']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">E-posta <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control form-control-sm" 
                                   required value="<?= h($s['email']) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">TC Kimlik <span class="text-danger">*</span></label>
                            <input type="text" name="tc_kimlik" class="form-control form-control-sm" 
                                   maxlength="11" required value="<?= h($s['tc_kimlik']) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Öğrenci No</label>
                            <input type="text" name="student_number" class="form-control form-control-sm" 
                                   value="<?= h($s['student_number']) ?>">
                        </div>

                        <!-- 2. SATIR -->
                        <!-- 2. SATIR -->
<div class="col-md-2">
    <label class="form-label small mb-1">Telefon</label>
    <input type="tel" name="phone" class="form-control form-control-sm phone-mask" 
           placeholder="(5__) ___ __ __" maxlength="15"
           value="<?= h($s['phone']) ?>">
</div>
<div class="col-md-2">
    <label class="form-label small mb-1">Telefon 2</label>
    <input type="tel" name="phone2" class="form-control form-control-sm phone-mask" 
           placeholder="(5__) ___ __ __" maxlength="15"
           value="<?= h($s['phone2']) ?>">
</div>
<div class="col-md-2">
    <label class="form-label small mb-1">Telefon 3</label>
    <input type="tel" name="phone3" class="form-control form-control-sm phone-mask" 
           placeholder="(5__) ___ __ __" maxlength="15"
           value="<?= h($s['phone3']) ?>">
</div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Doğum Tarihi</label>
                            <input type="date" name="birth_date" class="form-control form-control-sm" 
                                   value="<?= h($s['birth_date']) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Doğum Yeri</label>
                            <input type="text" name="birth_place" class="form-control form-control-sm" 
                                   value="<?= h($s['birth_place']) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Cinsiyet</label>
                            <select name="gender" class="form-select form-select-sm">
                                <option value="">—</option>
                                <option value="Erkek" <?= $s['gender']==='Erkek' ? 'selected' : '' ?>>Erkek</option>
                                <option value="Kadın" <?= $s['gender']==='Kadın' ? 'selected' : '' ?>>Kadın</option>
                            </select>
                        </div>

                        <!-- 3. SATIR -->
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Okul</label>
                            <input type="text" name="okul" class="form-control form-control-sm" 
                                   value="<?= h($s['okul']) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Sınıf (metin)</label>
                            <input type="text" name="sinif" class="form-control form-control-sm" 
                                   value="<?= h($s['sinif']) ?>" placeholder="5/A">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Sınıf (liste) <span class="text-danger">*</span></label>
                            <select name="class_id" class="form-select form-select-sm" required>
                                <option value="">Seçiniz...</option>
                                <?php foreach ($classes as $cl): ?>
                                    <option value="<?= (int)$cl['id'] ?>" 
                                            <?= ((string)$selClass === (string)$cl['id']) ? 'selected' : '' ?>>
                                        <?= h($cl['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Sorumlu Öğretmen</label>
                            <?php if ($role === 'teacher'): ?>
                                <input type="hidden" name="teacher_id" value="<?= (int)($_SESSION['user']['id'] ?? 0) ?>">
                                <input class="form-control form-control-sm" value="<?= h($_SESSION['user']['name'] ?? 'Siz') ?>" disabled>
                            <?php else: ?>
                                <select name="teacher_id" class="form-select form-select-sm">
                                    <option value="">—</option>
                                    <?php foreach ($teachers as $t): ?>
                                        <option value="<?= (int)$t['id'] ?>" 
                                                <?= ((int)$selTeacher === (int)$t['id']) ? 'selected' : '' ?>>
                                            <?= h($t['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Öğretim Türü</label>
                            <select name="teaching_type" class="form-select form-select-sm">
                                <option value="">—</option>
                                <option value="tam_gun" <?= $s['teaching_type']==='tam_gun' ? 'selected' : '' ?>>Tam Gün</option>
                                <option value="sabahci" <?= $s['teaching_type']==='sabahci' ? 'selected' : '' ?>>Sabahçı</option>
                                <option value="oglenci" <?= $s['teaching_type']==='oglenci' ? 'selected' : '' ?>>Öğlenci</option>
                            </select>
                        </div>

                        <!-- 4. SATIR -->
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Öğrenci Durumu</label>
                            <select name="enrollment_status" class="form-select form-select-sm">
                                <option value="on_kayit" <?= $s['enrollment_status']==='on_kayit' ? 'selected' : '' ?>>Ön Kayıt</option>
                                <option value="sinav_secim" <?= $s['enrollment_status']==='sinav_secim' ? 'selected' : '' ?>>Sınav Seçim</option>
                                <option value="sinav_secimi_yapti" <?= $s['enrollment_status']==='sinav_secimi_yapti' ? 'selected' : '' ?>>Sınav Seçimi Yaptı</option>
                                <option value="ders_secimi_yapan" <?= $s['enrollment_status']==='ders_secimi_yapan' ? 'selected' : '' ?>>Ders Seçimi Yapan</option>
                                <option value="aktif" <?= $s['enrollment_status']==='aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="mezun" <?= $s['enrollment_status']==='mezun' ? 'selected' : '' ?>>Mezun</option>
                            </select>
                        </div>
                        <div class="col-md-3">
    <label class="form-label small mb-1">Anne Adı <span class="text-danger">*</span></label>
    <input type="text" name="mother_name" class="form-control form-control-sm" 
           required value="<?= h($s['mother_name']) ?>">
</div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Baba Adı</label>
                            <input type="text" name="father_name" class="form-control form-control-sm" 
                                   value="<?= h($s['father_name']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Vasi/Veli Adı</label>
                            <input type="text" name="guardian_name" class="form-control form-control-sm" 
                                   value="<?= h($s['guardian_name']) ?>">
                        </div>

                        <!-- Adres -->
                        <div class="col-12">
                            <label class="form-label small mb-1">Adres</label>
                            <textarea name="address" class="form-control form-control-sm" rows="2"><?= h($s['address']) ?></textarea>
                        </div>

                        <!-- Özel Yetenek -->
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                       name="special_talent" id="special_talent" value="1"
                                       <?= !empty($s['special_talent']) ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="special_talent">
                                    ⭐ Özel Yetenek Sınavından Geldi
                                </label>
                            </div>
                        </div>

                        <!-- SAĞLIK BİLGİLERİ -->
                        <div class="col-12">
                            <hr class="my-2">
                            <h6 class="text-danger mb-2"><i class="fa fa-heartbeat"></i> Sağlık Bilgileri</h6>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Kronik Rahatsızlık</label>
                            <input type="text" name="chronic_condition" class="form-control form-control-sm" 
                                   value="<?= h($s['chronic_condition']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Kullandığı İlaçlar</label>
                            <input type="text" name="medications" class="form-control form-control-sm" 
                                   value="<?= h($s['medications']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Alerji</label>
                            <input type="text" name="allergy" class="form-control form-control-sm" 
                                   value="<?= h($s['allergy']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Kan Grubu</label>
                            <select name="blood_type" class="form-select form-select-sm">
                                <option value="">—</option>
                                <?php foreach (['A Rh+','A Rh-','B Rh+','B Rh-','AB Rh+','AB Rh-','0 Rh+','0 Rh-'] as $bt): ?>
                                    <option value="<?= h($bt) ?>" <?= ($s['blood_type']??'')===$bt ? 'selected' : '' ?>>
                                        <?= h($bt) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small mb-1">Sağlık Notları</label>
                            <textarea name="health_notes" class="form-control form-control-sm" rows="2"><?= h($s['health_notes']) ?></textarea>
                        </div>

                        <!-- Notlar -->
                        <div class="col-md-6">
                            <label class="form-label small mb-1">Öğrenci Notu</label>
                            <textarea name="student_note" class="form-control form-control-sm" rows="2"><?= h($s['student_note']) ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1">Genel Not</label>
                            <textarea name="note" class="form-control form-control-sm" rows="2"><?= h($s['note']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa fa-save"></i> Kaydet
            </button>
            <a href="index.php?module=students&action=list" class="btn btn-outline-secondary btn-sm">İptal</a>
        </div>
    </div>
</form>

<script>
function previewPhoto(input){
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('photoPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}

// Telefon Formatlama: (538) 470 12 53
document.querySelectorAll('.phone-mask').forEach(input => {
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Sadece rakamlar
        
        if (value.length > 0) {
            let formatted = '';
            if (value.length <= 3) {
                formatted = '(' + value;
            } else if (value.length <= 6) {
                formatted = '(' + value.substring(0, 3) + ') ' + value.substring(3);
            } else if (value.length <= 8) {
                formatted = '(' + value.substring(0, 3) + ') ' + value.substring(3, 6) + ' ' + value.substring(6);
            } else {
                formatted = '(' + value.substring(0, 3) + ') ' + value.substring(3, 6) + ' ' + value.substring(6, 8) + ' ' + value.substring(8, 10);
            }
            e.target.value = formatted;
        }
    });
});
// Telefon Formatlama: (538) 470 12 53
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.phone-mask').forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Sadece rakamlar
            
            if (value.length > 0) {
                let formatted = '';
                if (value.length <= 3) {
                    formatted = '(' + value;
                } else if (value.length <= 6) {
                    formatted = '(' + value.substring(0, 3) + ') ' + value.substring(3);
                } else if (value.length <= 8) {
                    formatted = '(' + value.substring(0, 3) + ') ' + value.substring(3, 6) + ' ' + value.substring(6);
                } else {
                    formatted = '(' + value.substring(0, 3) + ') ' + value.substring(3, 6) + ' ' + value.substring(6, 8) + ' ' + value.substring(8, 10);
                }
                e.target.value = formatted;
            }
        });
    });
});
</script>