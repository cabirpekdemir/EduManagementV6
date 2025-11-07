<?php
// modules/students/view/create.php - TAM KAPSAMLI VERSİYON
$classes = $classes ?? [];
$teachers = $teachers ?? [];
$formData = $formData ?? [];
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Yeni Öğrenci Ekle</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="index.php?module=dashboard">Ana Sayfa</a></li>
                    <li class="breadcrumb-item"><a href="index.php?module=students&action=list">Öğrenciler</a></li>
                    <li class="breadcrumb-item active">Yeni Ekle</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        
        <!-- Flash Mesajlar -->
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['flash_error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
        
        <form method="POST" action="index.php?module=students&action=save" enctype="multipart/form-data">
            
            <!-- SEKMELİ MENÜ -->
            <ul class="nav nav-tabs nav-justified mb-3" id="studentFormTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="personal-tab" data-toggle="tab" href="#personal" role="tab">
                        <i class="fa fa-user"></i> Kişisel
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab">
                        <i class="fa fa-phone"></i> İletişim
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="family-tab" data-toggle="tab" href="#family" role="tab">
                        <i class="fa fa-users"></i> Aile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="education-tab" data-toggle="tab" href="#education" role="tab">
                        <i class="fa fa-graduation-cap"></i> Eğitim
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="health-tab" data-toggle="tab" href="#health" role="tab">
                        <i class="fa fa-heartbeat"></i> Sağlık
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="other-tab" data-toggle="tab" href="#other" role="tab">
                        <i class="fa fa-info-circle"></i> Diğer
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="photo-tab" data-toggle="tab" href="#photo" role="tab">
                        <i class="fa fa-camera"></i> Fotoğraf
                    </a>
                </li>
            </ul>

            <!-- SEKME İÇERİKLERİ -->
            <div class="tab-content" id="studentFormTabsContent">
                
                <!-- ============ 1. KİŞİSEL BİLGİLER SEKMESİ ============ -->
                <div class="tab-pane fade show active" id="personal" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fa fa-user"></i> Kişisel Bilgiler</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Ad Soyad -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Ad Soyad <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="name" 
                                           value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
                                           required>
                                </div>
                                
                                <!-- T.C. Kimlik No -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        T.C. Kimlik No <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="tc_kimlik" 
                                           value="<?= htmlspecialchars($formData['tc_kimlik'] ?? '') ?>"
                                           maxlength="11"
                                           pattern="[0-9]{11}"
                                           title="11 haneli T.C. Kimlik No giriniz"
                                           required>
                                </div>
                                
                                <!-- Cinsiyet -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        Cinsiyet <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" name="gender" required>
                                        <option value="">Seçiniz...</option>
                                        <option value="Erkek" <?= ($formData['gender'] ?? '') == 'Erkek' ? 'selected' : '' ?>>Erkek</option>
                                        <option value="Kız" <?= ($formData['gender'] ?? '') == 'Kız' ? 'selected' : '' ?>>Kız</option>
                                    </select>
                                </div>
                                
                                <!-- Doğum Tarihi -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        Doğum Tarihi <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control" 
                                           name="birth_date" 
                                           value="<?= htmlspecialchars($formData['birth_date'] ?? '') ?>"
                                           required>
                                </div>
                                
                                <!-- Doğum Yeri -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Doğum Yeri</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="birth_place" 
                                           value="<?= htmlspecialchars($formData['birth_place'] ?? '') ?>">
                                </div>
                                
                                <!-- Öğrenci No -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Öğrenci No</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="student_number" 
                                           value="<?= htmlspecialchars($formData['student_number'] ?? '') ?>">
                                </div>
                                
                                <!-- E-posta -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">E-posta</label>
                                    <input type="email" 
                                           class="form-control" 
                                           name="email" 
                                           value="<?= htmlspecialchars($formData['email'] ?? '') ?>">
                                    <small class="text-muted">Boş bırakılırsa TC No ile oluşturulur</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- ============ 2. İLETİŞİM BİLGİLERİ SEKMESİ ============ -->
                <div class="tab-pane fade" id="contact" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fa fa-phone"></i> İletişim Bilgileri</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Telefon 1 -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        Telefon 1 <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" 
                                           class="form-control phone-mask" 
                                           name="phone" 
                                           value="<?= htmlspecialchars($formData['phone'] ?? '') ?>"
                                           placeholder="(5__) ___ __ __"
                                           maxlength="15"
                                           required>
                                </div>
                                
                                <!-- Telefon 2 -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Telefon 2</label>
                                    <input type="tel" 
                                           class="form-control phone-mask" 
                                           name="phone2" 
                                           value="<?= htmlspecialchars($formData['phone2'] ?? '') ?>"
                                           placeholder="(5__) ___ __ __"
                                           maxlength="15">
                                </div>
                                
                                <!-- Telefon 3 -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Telefon 3</label>
                                    <input type="tel" 
                                           class="form-control phone-mask" 
                                           name="phone3" 
                                           value="<?= htmlspecialchars($formData['phone3'] ?? '') ?>"
                                           placeholder="(5__) ___ __ __"
                                           maxlength="15">
                                </div>
                                
                                <!-- Acil Durum Kişi Adı -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fa fa-ambulance text-danger"></i> Acil Durum Kişi Adı
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="emergency_contact_name" 
                                           value="<?= htmlspecialchars($formData['emergency_contact_name'] ?? '') ?>">
                                </div>
                                
                                <!-- Acil Durum Telefon -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fa fa-ambulance text-danger"></i> Acil Durum Telefon
                                    </label>
                                    <input type="tel" 
                                           class="form-control phone-mask" 
                                           name="emergency_contact_phone" 
                                           value="<?= htmlspecialchars($formData['emergency_contact_phone'] ?? '') ?>"
                                           placeholder="(5__) ___ __ __"
                                           maxlength="15">
                                </div>
                                
                                <!-- Adres -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Adres</label>
                                    <textarea class="form-control" 
                                              name="address" 
                                              rows="3"><?= htmlspecialchars($formData['address'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- ============ 3. AİLE BİLGİLERİ SEKMESİ ============ -->
                <div class="tab-pane fade" id="family" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fa fa-users"></i> Aile Bilgileri</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Anne Adı -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Anne Adı <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="mother_name" 
                                           value="<?= htmlspecialchars($formData['mother_name'] ?? '') ?>"
                                           required>
                                </div>
                                
                                <!-- Baba Adı -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Baba Adı</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="father_name" 
                                           value="<?= htmlspecialchars($formData['father_name'] ?? '') ?>">
                                </div>
                                
                                <!-- Vasi/Veli Adı -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Vasi/Veli Adı</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="guardian_name" 
                                           value="<?= htmlspecialchars($formData['guardian_name'] ?? '') ?>">
                                </div>
                                
                                <!-- Veli İsmi (parent_name) -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Veli İsmi (Alternatif)</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="parent_name" 
                                           value="<?= htmlspecialchars($formData['parent_name'] ?? '') ?>">
                                    <small class="text-muted">Velinin tam adı soyadı</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- ============ 4. EĞİTİM BİLGİLERİ SEKMESİ ============ -->
                <div class="tab-pane fade" id="education" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fa fa-graduation-cap"></i> Eğitim Bilgileri</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Okul -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Şu Anki Okul</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="okul" 
                                           value="<?= htmlspecialchars($formData['okul'] ?? '') ?>"
                                           placeholder="Örn: Atatürk İlkokulu">
                                </div>
                                
                                <!-- Mezun Olduğu Okul -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mezun Olduğu Okul (Önceki)</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="graduated_school" 
                                           value="<?= htmlspecialchars($formData['graduated_school'] ?? '') ?>"
                                           placeholder="Önceki okul adı">
                                </div>
                                
                                <!-- Sınıf (seviye) -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Sınıf (Seviye)<span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="sinif" 
                                           value="<?= htmlspecialchars($formData['sinif'] ?? '') ?>"
                                           placeholder="Örn: 3-Sınıf, 5-Sınıf"
                                           required>
                                           
                                </div>
                                
                                <!-- Şube/Sınıf -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Şube/Sınıf</label>
                                    <select class="form-select" name="class_id">
                                        <option value="">Seçiniz...</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?= $class['id'] ?>" 
                                                    <?= ($formData['class_id'] ?? '') == $class['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($class['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Öğretmen -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Öğretmen</label>
                                    <select class="form-select" name="teacher_id">
                                        <option value="">Seçiniz...</option>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?= $teacher['id'] ?>" 
                                                    <?= ($formData['teacher_id'] ?? '') == $teacher['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($teacher['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Öğretim Tipi -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Öğretim Tipi</label>
                                    <select class="form-select" name="teaching_type">
                                        <option value="">Seçiniz...</option>
                                        <option value="tam_gun" <?= ($formData['teaching_type'] ?? '') == 'tam_gun' ? 'selected' : '' ?>>🕐 Tam Gün</option>
                                        <option value="sabahci" <?= ($formData['teaching_type'] ?? '') == 'sabahci' ? 'selected' : '' ?>>🌅 Sabahçı</option>
                                        <option value="oglenci" <?= ($formData['teaching_type'] ?? '') == 'oglenci' ? 'selected' : '' ?>>🌆 Öğlenci</option>
                                    </select>
                                </div>
                                
                                <!-- Okula Giriş Saati -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        <i class="fa fa-clock text-success"></i> Okula Giriş Saati
                                    </label>
                                    <input type="time" 
                                           class="form-control" 
                                           name="school_entry_time" 
                                           value="<?= htmlspecialchars($formData['school_entry_time'] ?? '') ?>">
                                    <small class="text-muted">Bilim Merkezine gelme saati</small>
                                </div>
                                
                                <!-- Okuldan Çıkış Saati -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        <i class="fa fa-clock text-danger"></i> Okuldan Çıkış Saati
                                    </label>
                                    <input type="time" 
                                           class="form-control" 
                                           name="school_exit_time" 
                                           value="<?= htmlspecialchars($formData['school_exit_time'] ?? '') ?>">
                                    <small class="text-muted">Bilim Merkezinden ayrılma saati</small>
                                </div>
                                
                                <!-- Kabul Sınavı Puanı -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Kabul Sınavı Puanı</label>
                                    <input type="number" 
                                           class="form-control" 
                                           name="entrance_exam_score" 
                                           value="<?= htmlspecialchars($formData['entrance_exam_score'] ?? '') ?>"
                                           step="0.01"
                                           min="0"
                                           max="100"
                                           placeholder="0.00">
                                </div>
                                
                                <!-- Tercih ID -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tercih ID</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="preference_id" 
                                           value="<?= htmlspecialchars($formData['preference_id'] ?? '') ?>">
                                </div>
                                
                                <!-- Tercih Sırası -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tercih Sırası</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="choice_number" 
                                           value="<?= htmlspecialchars($formData['choice_number'] ?? '') ?>"
                                           placeholder="Örn: 1. Tercih">
                                </div>
                                
                                <!-- Özel Yetenek -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="special_talent" 
                                               id="special_talent" 
                                               value="1"
                                               <?= !empty($formData['special_talent']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="special_talent">
                                            ⭐ Özel Yetenek Sınavından Geldi
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Kayıt Durumu -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kayıt Durumu</label>
                                    <select class="form-select" name="enrollment_status">
                                        <option value="on_kayit" <?= ($formData['enrollment_status'] ?? 'on_kayit') == 'on_kayit' ? 'selected' : '' ?>>📝 Ön Kayıt</option>
                                        <option value="sinav_secim" <?= ($formData['enrollment_status'] ?? '') == 'sinav_secim' ? 'selected' : '' ?>>📋 Sınav Seçim</option>
                                        <option value="sinav_secimi_yapti" <?= ($formData['enrollment_status'] ?? '') == 'sinav_secimi_yapti' ? 'selected' : '' ?>>✅ Sınav Seçimi Yaptı</option>
                                        <option value="ders_secimi_yapan" <?= ($formData['enrollment_status'] ?? '') == 'ders_secimi_yapan' ? 'selected' : '' ?>>📚 Ders Seçimi Yapan</option>
                                        <option value="sinav_sonuc_girisi" <?= ($formData['enrollment_status'] ?? '') == 'sinav_sonuc_girisi' ? 'selected' : '' ?>>📊 Sınav Sonuç Girişi</option>
                                        <option value="sinavi_kazanamayan" <?= ($formData['enrollment_status'] ?? '') == 'sinavi_kazanamayan' ? 'selected' : '' ?>>❌ Sınavı Kazanamayan</option>
                                        <option value="aktif" <?= ($formData['enrollment_status'] ?? '') == 'aktif' ? 'selected' : '' ?>>✅ Aktif Öğrenci</option>
                                        <option value="kayit_dondurma" <?= ($formData['enrollment_status'] ?? '') == 'kayit_dondurma' ? 'selected' : '' ?>>⏸️ Kayıt Dondurma</option>
                                        <option value="kayit_silinen" <?= ($formData['enrollment_status'] ?? '') == 'kayit_silinen' ? 'selected' : '' ?>>🗑️ Kayıt Silinen</option>
                                        <option value="mezun" <?= ($formData['enrollment_status'] ?? '') == 'mezun' ? 'selected' : '' ?>>🎓 Mezun</option>
                                    </select>
                                </div>
                                
                                <!-- Aktif/Onaylı Checkboxlar -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label d-block">Durum</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="is_active" 
                                               id="is_active" 
                                               value="1"
                                               <?= !empty($formData['is_active']) || !isset($formData['is_active']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_active">
                                            <i class="fa fa-check-circle text-success"></i> Aktif
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="approved" 
                                               id="approved" 
                                               value="1"
                                               <?= !empty($formData['approved']) || !isset($formData['approved']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="approved">
                                            <i class="fa fa-check-circle text-primary"></i> Onaylı
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- ============ 5. SAĞLIK BİLGİLERİ SEKMESİ ============ -->
                <div class="tab-pane fade" id="health" role="tabpanel">
                    <div class="card shadow-sm border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fa fa-heartbeat"></i> Sağlık Bilgileri</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle"></i> 
                                <strong>Önemli:</strong> Bu bilgiler acil durumlarda kullanılacaktır. Lütfen eksiksiz doldurunuz.
                            </div>
                            
                            <div class="row">
                                <!-- Kan Grubu -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">
                                        <i class="fa fa-tint text-danger"></i> Kan Grubu
                                    </label>
                                    <select class="form-select" name="blood_type">
                                        <option value="">Seçiniz...</option>
                                        <?php 
                                        $bloodTypes = ['A Rh+', 'A Rh-', 'B Rh+', 'B Rh-', 'AB Rh+', 'AB Rh-', '0 Rh+', '0 Rh-'];
                                        foreach ($bloodTypes as $bt): 
                                        ?>
                                            <option value="<?= $bt ?>" <?= ($formData['blood_type'] ?? '') == $bt ? 'selected' : '' ?>>
                                                <?= $bt ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Kronik Rahatsızlık -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">
                                        <i class="fa fa-stethoscope text-danger"></i> Kronik Rahatsızlık
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="chronic_condition" 
                                           value="<?= htmlspecialchars($formData['chronic_condition'] ?? '') ?>"
                                           placeholder="Örn: Astım, Diyabet">
                                </div>
                                
                                <!-- Alerji -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">
                                        <i class="fa fa-allergies text-warning"></i> Alerji
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="allergy" 
                                           value="<?= htmlspecialchars($formData['allergy'] ?? '') ?>"
                                           placeholder="Örn: Polen, Fıstık">
                                </div>
                                
                                <!-- Kullandığı İlaçlar -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">
                                        <i class="fa fa-pills text-info"></i> Kullandığı İlaçlar
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="medications" 
                                           value="<?= htmlspecialchars($formData['medications'] ?? '') ?>"
                                           placeholder="İlaç isimleri">
                                </div>
                                
                                <!-- Engellilik Durumu -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Engellilik Durumu</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="health_disabilities" 
                                           value="<?= htmlspecialchars($formData['health_disabilities'] ?? '') ?>"
                                           placeholder="Varsa engellilik durumu belirtiniz">
                                </div>
                                
                                <!-- Sağlık Notları -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Sağlık Notları / Özel Durumlar</label>
                                    <textarea class="form-control" 
                                              name="health_notes" 
                                              rows="4"
                                              placeholder="Öğretmenlerin bilmesi gereken özel sağlık durumları..."><?= htmlspecialchars($formData['health_notes'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- ============ 6. DİĞER BİLGİLER SEKMESİ ============ -->
                <div class="tab-pane fade" id="other" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="fa fa-info-circle"></i> Diğer Bilgiler</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Hobiler / İlgi Alanları -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">
                                        <i class="fa fa-star text-warning"></i> Hobiler / İlgi Alanları
                                    </label>
                                    <textarea class="form-control" 
                                              name="hobbies" 
                                              rows="3"
                                              placeholder="Örn: Futbol, Resim, Müzik, Robotik..."><?= htmlspecialchars($formData['hobbies'] ?? '') ?></textarea>
                                    <small class="text-muted">Öğrencinin ilgi alanları ve hobilerini belirtiniz</small>
                                </div>
                                
                                <!-- Öğrenci Notu -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fa fa-comment"></i> Öğrenci Notu
                                    </label>
                                    <textarea class="form-control" 
                                              name="student_note" 
                                              rows="4"
                                              placeholder="Öğrenci hakkında genel notlar..."><?= htmlspecialchars($formData['student_note'] ?? '') ?></textarea>
                                </div>
                                
                                <!-- Öğretmen Notu -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fa fa-chalkboard-teacher"></i> Öğretmen Notu
                                    </label>
                                    <textarea class="form-control" 
                                              name="teacher_note" 
                                              rows="4"
                                              placeholder="Öğretmen notları..."><?= htmlspecialchars($formData['teacher_note'] ?? '') ?></textarea>
                                </div>
                                
                                <!-- Genel Not -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">
                                        <i class="fa fa-sticky-note"></i> Genel Not
                                    </label>
                                    <textarea class="form-control" 
                                              name="note" 
                                              rows="3"
                                              placeholder="Diğer notlar ve açıklamalar..."><?= htmlspecialchars($formData['note'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- ============ 7. FOTOĞRAF SEKMESİ ============ -->
                <div class="tab-pane fade" id="photo" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fa fa-camera"></i> Profil Fotoğrafı</h5>
                        </div>
                        <div class="card-body">
                            
                            <!-- Fotoğraf Yükleme Sekmeleri -->
                            <ul class="nav nav-pills mb-3" id="photoTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="upload-tab" data-toggle="pill" href="#upload" role="tab">
                                        <i class="fa fa-upload"></i> Dosya Yükle
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="camera-tab" data-toggle="pill" href="#camera" role="tab">
                                        <i class="fa fa-camera"></i> Kamera ile Çek
                                    </a>
                                </li>
                            </ul>
                            
                            <div class="tab-content">
                                <!-- Dosya Yükleme -->
                                <div class="tab-pane fade show active" id="upload" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Fotoğraf Seçin</label>
                                                <input type="file" 
                                                       class="form-control" 
                                                       id="photoFile"
                                                       name="profile_photo" 
                                                       accept="image/*">
                                                <small class="text-muted">JPG, PNG veya GIF (Max: 2MB)</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="preview-container text-center p-3 border rounded bg-light">
                                                <div id="uploadPreview" style="display:none;">
                                                    <img id="uploadPreviewImg" src="" style="max-width:100%; max-height:300px; border-radius:8px;">
                                                </div>
                                                <div id="uploadPlaceholder">
                                                    <i class="fa fa-image fa-3x text-muted"></i>
                                                    <p class="text-muted mt-2 mb-0">Önizleme</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Kamera ile Çekme -->
                                <div class="tab-pane fade" id="camera" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="camera-container mb-3">
                                                <div id="cameraPlaceholder" class="text-center p-5 bg-light border rounded">
                                                    <i class="fa fa-camera fa-4x text-muted mb-3"></i>
                                                    <p class="text-muted">Kamerayı başlatmak için butona tıklayın</p>
                                                </div>
                                                <video id="cameraVideo" 
                                                       autoplay 
                                                       playsinline 
                                                       style="width:100%; display:none; border-radius:8px;">
                                                </video>
                                                <canvas id="cameraCanvas" style="display:none; width:100%;"></canvas>
                                            </div>
                                            
                                            <div class="btn-group w-100" role="group">
                                                <button type="button" 
                                                        id="startCamera" 
                                                        class="btn btn-primary">
                                                    <i class="fa fa-video"></i> Kamerayı Başlat
                                                </button>
                                                <button type="button" 
                                                        id="capturePhoto" 
                                                        class="btn btn-success" 
                                                        style="display:none;">
                                                    <i class="fa fa-camera"></i> Fotoğraf Çek
                                                </button>
                                                <button type="button" 
                                                        id="retakePhoto" 
                                                        class="btn btn-warning" 
                                                        style="display:none;">
                                                    <i class="fa fa-redo"></i> Yeniden Çek
                                                </button>
                                                <button type="button" 
                                                        id="stopCamera" 
                                                        class="btn btn-danger" 
                                                        style="display:none;">
                                                    <i class="fa fa-stop"></i> Kamerayı Kapat
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="preview-container text-center p-3 border rounded bg-light">
                                                <div id="photoPreview">
                                                    <i class="fa fa-image fa-3x text-muted"></i>
                                                    <p class="text-muted mt-2 mb-0">Çekilen fotoğraf burada görünecek</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Hidden inputs for camera photo -->
                                    <input type="hidden" name="photo_data" id="photoData">
                                    <input type="hidden" name="photo_source" id="photoSource" value="upload">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- BUTONLAR -->
            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="index.php?module=students&action=list" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Geri Dön
                        </a>
                        <div>
                            <button type="reset" class="btn btn-outline-secondary me-2">
                                <i class="fa fa-undo"></i> Temizle
                            </button>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa fa-save"></i> Kaydet
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<style>
.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}
.text-danger {
    font-weight: bold;
}
.nav-tabs .nav-link {
    color: #6c757d;
}
.nav-tabs .nav-link.active {
    color: #495057;
    font-weight: 600;
}
.camera-container {
    position: relative;
    background: #f8f9fa;
    border-radius: 8px;
    overflow: hidden;
}
#cameraVideo, #cameraCanvas {
    border-radius: 8px;
    max-width: 100%;
}
.preview-container img {
    max-width: 100%;
    border-radius: 8px;
}
</style>

<script>
// Telefon Formatlama: (538) 470 12 53
document.addEventListener('DOMContentLoaded', function() {
    // Telefon maskesi
    document.querySelectorAll('.phone-mask').forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
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

// Kamera Modülü
(function() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const placeholder = document.getElementById('cameraPlaceholder');
    const startBtn = document.getElementById('startCamera');
    const captureBtn = document.getElementById('capturePhoto');
    const retakeBtn = document.getElementById('retakePhoto');
    const stopBtn = document.getElementById('stopCamera');
    const photoPreview = document.getElementById('photoPreview');
    const photoData = document.getElementById('photoData');
    const photoSource = document.getElementById('photoSource');
    const fileInput = document.getElementById('photoFile');
    const uploadPreview = document.getElementById('uploadPreview');
    const uploadPreviewImg = document.getElementById('uploadPreviewImg');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    
    let videoStream = null;
    let capturedPhoto = null;
    
    // Kamerayı başlat
    startBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                width: { ideal: 640 },
                height: { ideal: 480 },
                facingMode: 'user'
            } 
        })
        .then(function(stream) {
            videoStream = stream;
            video.srcObject = stream;
            
            placeholder.style.display = 'none';
            video.style.display = 'block';
            
            startBtn.style.display = 'none';
            captureBtn.style.display = 'inline-block';
            stopBtn.style.display = 'inline-block';
        })
        .catch(function(error) {
            console.error('Kamera erişim hatası:', error);
            let errorMsg = 'Kameraya erişilemedi. ';
            
            if (error.name === 'NotAllowedError') {
                errorMsg += 'Lütfen tarayıcı ayarlarından kamera iznini verin.';
            } else if (error.name === 'NotFoundError') {
                errorMsg += 'Kamera bulunamadı.';
            } else {
                errorMsg += 'Hata: ' + error.message;
            }
            
            alert(errorMsg);
        });
    });
    
    // Fotoğraf çek
    captureBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        capturedPhoto = canvas.toDataURL('image/jpeg', 0.8);
        
        video.style.display = 'none';
        canvas.style.display = 'block';
        
        photoPreview.innerHTML = '<img src="' + capturedPhoto + '" style="max-width:100%;border-radius:8px;">';
        
        photoData.value = capturedPhoto;
        photoSource.value = 'camera';
        
        captureBtn.style.display = 'none';
        retakeBtn.style.display = 'inline-block';
    });
    
    // Yeniden çek
    retakeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        video.style.display = 'block';
        canvas.style.display = 'none';
        
        retakeBtn.style.display = 'none';
        captureBtn.style.display = 'inline-block';
        
        photoData.value = '';
        photoPreview.innerHTML = '<i class="fa fa-image fa-3x text-muted"></i><p class="text-muted mt-2 mb-0">Yeniden çekin</p>';
    });
    
    // Kamerayı kapat
    stopBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
        }
        
        video.style.display = 'none';
        canvas.style.display = 'none';
        placeholder.style.display = 'block';
        
        startBtn.style.display = 'inline-block';
        captureBtn.style.display = 'none';
        retakeBtn.style.display = 'none';
        stopBtn.style.display = 'none';
    });
    
    // Dosya yükleme önizlemesi
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    uploadPreviewImg.src = event.target.result;
                    uploadPreview.style.display = 'block';
                    uploadPlaceholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Sayfa kapatılınca kamerayı kapat
    window.addEventListener('beforeunload', function() {
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
        }
    });
})();
</script>