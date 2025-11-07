<?php
// modules/students/view/view.php

// ⭐ GÜVENLİK: Öğrenci verisi kontrolü
if (!isset($student) || empty($student)) {
    die('<div class="alert alert-danger m-5">
        <h4>Hata!</h4>
        <p><strong>Öğrenci verisi bulunamadı.</strong></p>
        <p>Olası sebepler:</p>
        <ul>
            <li>URL\'de öğrenci ID\'si eksik (örn: ?id=123)</li>
            <li>Bu ID\'de bir öğrenci yok</li>
            <li>Controller dosyası güncel değil</li>
        </ul>
        <a href="index.php?module=students&action=list" class="btn btn-primary">Öğrenci Listesine Dön</a>
    </div>');
}

$student = $student ?? [];
$studentCourses = $studentCourses ?? [];

// Telefon Formatlama Fonksiyonu
if (!function_exists('formatPhone')) {
    function formatPhone($phone) {
        if (empty($phone)) return '—';
        
        // Sadece rakamları al
        $phone = preg_replace('/\D/', '', $phone);
        
        // En az 10 haneli olmalı
        if (strlen($phone) < 10) return $phone;
        
        // (538) 470 12 53 formatına çevir
        return '(' . substr($phone, 0, 3) . ') ' . 
               substr($phone, 3, 3) . ' ' . 
               substr($phone, 6, 2) . ' ' . 
               substr($phone, 8, 2);
    }
}
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Öğrenci Profili</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="index.php?module=dashboard">Ana Sayfa</a></li>
                    <li class="breadcrumb-item"><a href="index.php?module=students&action=list">Öğrenciler</a></li>
                    <li class="breadcrumb-item active">Profil</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        
        <!-- Flash Mesajlar -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['flash_error']) ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
        
        <!-- ⭐ YENİ: Üst Butonlar -->
        <div class="mb-3">
            <a href="index.php?module=students&action=list" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Listeye Dön
            </a>
            <a href="index.php?module=students&action=edit&id=<?= $student['id'] ?>" class="btn btn-primary btn-sm">
                <i class="fa fa-pen"></i> Düzenle
            </a>
            
            <!-- Durum Değiştirme Dropdown -->
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-warning btn-sm dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-exchange-alt"></i> Durum Değiştir
                </button>
                <div class="dropdown-menu">
                    <?php 
                    $statusNames = [
                        'on_kayit' => ['text' => 'Ön Kayıt', 'icon' => '📝'],
                        'sinav_secim' => ['text' => 'Sınav Seçim', 'icon' => '📋'],
                        'sinav_secimi_yapti' => ['text' => 'Sınav Seçimi Yaptı', 'icon' => '✅'],
                        'ders_secimi_yapan' => ['text' => 'Ders Seçimi Yapan', 'icon' => '📚'],
                        'sinav_sonuc_girisi' => ['text' => 'Sınav Sonuç Girişi', 'icon' => '📊'],
                        'sinavi_kazanamayan' => ['text' => 'Sınavı Kazanamayan', 'icon' => '❌'],
                        'aktif' => ['text' => 'Aktif Öğrenci', 'icon' => '✅'],
                        'kayit_dondurma' => ['text' => 'Kayıt Dondurma', 'icon' => '⏸️'],
                        'kayit_silinen' => ['text' => 'Kayıt Silinen', 'icon' => '🗑️'],
                        'mezun' => ['text' => 'Mezun', 'icon' => '🎓']
                    ];
                    $currentStatus = $student['enrollment_status'] ?? 'on_kayit';
                    foreach ($statusNames as $statusKey => $statusData): 
                        if ($statusKey !== $currentStatus): ?>
                            <a class="dropdown-item" href="javascript:void(0)" 
                               onclick="changeStatus('<?= $statusKey ?>', '<?= htmlspecialchars($statusData['text']) ?>')">
                                <?= $statusData['icon'] ?> <?= htmlspecialchars($statusData['text']) ?>
                            </a>
                        <?php endif;
                    endforeach; ?>
                </div>
            </div>
            
            <!-- Transkript PDF Butonu -->
            <a href="index.php?module=students&action=transcript_pdf&id=<?= $student['id'] ?>" 
               class="btn btn-danger btn-sm" target="_blank">
                <i class="fa fa-file-pdf"></i> Transkript PDF
            </a>
            
            <!-- ⭐ YENİ: Aktif/Pasif Toggle Butonu -->
            <?php if ($student['is_active']): ?>
                <button type="button" class="btn btn-secondary btn-sm" 
                        onclick="toggleActiveStatus(<?= $student['id'] ?>, 0, 'Pasif')">
                    <i class="fa fa-ban"></i> Pasif Yap
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-success btn-sm" 
                        onclick="toggleActiveStatus(<?= $student['id'] ?>, 1, 'Aktif')">
                    <i class="fa fa-check-circle"></i> Aktif Yap
                </button>
            <?php endif; ?>
        </div>
        
        <div class="row">
            <!-- SOL TARAF - FOTOĞRAF VE TEMEL BİLGİLER (SABİT) -->
            <div class="col-md-3">
                <!-- Profil Kartı -->
                <div class="card shadow-sm sticky-sidebar">
                    <div class="card-body text-center">
                        <img src="<?= htmlspecialchars($student['profile_photo'] ?? 'assets/img/default-avatar.png') ?>" 
                             class="rounded-circle mb-3" 
                             style="width:150px;height:150px;object-fit:cover;border:4px solid #f0f0f0;"
                             alt="<?= htmlspecialchars($student['name']) ?>">
                        
                        <h4 class="mb-1"><?= htmlspecialchars($student['name']) ?></h4>
                        
                        <?php if ($student['student_number']): ?>
                            <p class="text-muted mb-2">
                                <i class="fa fa-id-card"></i> 
                                No: <strong><?= htmlspecialchars($student['student_number']) ?></strong>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($student['sinif'] || $student['class_name']): ?>
                            <span class="badge badge-primary p-2 mb-3" style="font-size:0.95rem;">
                                <i class="fa fa-graduation-cap"></i> 
                                <?php 
                                // Önce sinif (seviye), varsa class_name (şube) göster
                                if ($student['sinif']) {
                                    echo htmlspecialchars($student['sinif']);
                                    if ($student['class_name']) {
                                        echo ' - ' . htmlspecialchars($student['class_name']);
                                    }
                                } else {
                                    echo htmlspecialchars($student['class_name']);
                                }
                                ?>
                            </span>
                        <?php endif; ?>
                        
                        <div class="mt-2 mb-3">
                            <?php if ($student['is_active']): ?>
                                <span class="badge badge-success p-2">
                                    <i class="fa fa-check-circle"></i> Aktif
                                </span>
                            <?php else: ?>
                                <span class="badge badge-secondary p-2">Pasif</span>
                            <?php endif; ?>
                        </div>
                        
                        <hr>
                        
                        <!-- Okul Bilgisi -->
                        <div class="text-left">
                            <p class="mb-1 text-muted small"><i class="fa fa-school"></i> Okul:</p>
                            <p class="mb-2"><strong><?= htmlspecialchars($student['okul'] ?? '—') ?></strong></p>
                            
                            <?php if ($student['birth_date']): ?>
                                <p class="mb-1 text-muted small"><i class="fa fa-birthday-cake"></i> Yaş:</p>
                                <p class="mb-0">
                                    <strong><?= date_diff(date_create($student['birth_date']), date_create('today'))->y ?></strong> yaş
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <hr>
                        
                        <!-- Hızlı İşlemler -->
                        <div class="d-grid gap-2">
                            <a href="index.php?module=students&action=edit&id=<?= $student['id'] ?>" 
                               class="btn btn-primary btn-sm btn-block mb-2">
                                <i class="fa fa-edit"></i> Düzenle
                            </a>
                            <a href="index.php?module=students&action=list" 
                               class="btn btn-secondary btn-sm btn-block">
                                <i class="fa fa-arrow-left"></i> Listeye Dön
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SAĞ TARAF - SEKMELİ İÇERİK -->
            <div class="col-md-9">
                
                <!-- ⭐ SEKMELER - STICKY -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white sticky-tabs">
                        <ul class="nav nav-tabs card-header-tabs" id="studentTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" 
                                   id="general-tab" 
                                   data-toggle="tab" 
                                   href="#general" 
                                   role="tab">
                                    <i class="fa fa-user"></i> Genel Bilgiler
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" 
                                   id="courses-tab" 
                                   data-toggle="tab" 
                                   href="#courses" 
                                   role="tab">
                                    <i class="fa fa-book"></i> Aldığı Dersler
                                    <span class="badge badge-primary ml-1"><?= count($studentCourses) ?></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" 
                                   id="health-tab" 
                                   data-toggle="tab" 
                                   href="#health" 
                                   role="tab">
                                    <i class="fa fa-heartbeat"></i> Sağlık Bilgileri
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" 
                                   id="attendance-tab" 
                                   data-toggle="tab" 
                                   href="#attendance" 
                                   role="tab">
                                    <i class="fa fa-calendar-check"></i> Devamsızlık
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" 
                                   id="exams-tab" 
                                   data-toggle="tab" 
                                   href="#exams" 
                                   role="tab">
                                    <i class="fa fa-graduation-cap"></i> Sınav Sonuçları
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="card-body">
                        <div class="tab-content" id="studentTabsContent">
                            
                            <!-- ========== GENEL BİLGİLER SEKMESİ ========== -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                
                                <!-- Kimlik Bilgileri -->
                                <div class="info-section mb-4">
                                    <h5 class="section-title">
                                        <i class="fa fa-id-card text-primary"></i> Kimlik Bilgileri
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="text-muted small">T.C. Kimlik No</label>
                                            <p class="mb-0 font-weight-bold"><?= htmlspecialchars($student['tc_kimlik'] ?? '—') ?></p>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="text-muted small">Doğum Tarihi</label>
                                            <p class="mb-0 font-weight-bold">
                                                <?php if ($student['birth_date']): ?>
                                                    <?= date('d.m.Y', strtotime($student['birth_date'])) ?>
                                                    <small class="text-muted">(<?= date_diff(date_create($student['birth_date']), date_create('today'))->y ?> yaş)</small>
                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="text-muted small">Doğum Yeri</label>
                                            <p class="mb-0 font-weight-bold"><?= htmlspecialchars($student['birth_place'] ?? '—') ?></p>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="text-muted small">Cinsiyet</label>
                                            <p class="mb-0">
                                                <?php if ($student['gender'] == 'Erkek'): ?>
                                                    <i class="fa fa-mars text-primary"></i> Erkek
                                                <?php elseif ($student['gender'] == 'Kız'): ?>
                                                    <i class="fa fa-venus text-danger"></i> Kız
                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="text-muted small">E-posta</label>
                                            <p class="mb-0">
                                                <?php if ($student['email']): ?>
                                                    <a href="mailto:<?= htmlspecialchars($student['email']) ?>">
                                                        <?= htmlspecialchars($student['email']) ?>
                                                    </a>
                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="text-muted small">Kayıt Tarihi</label>
                                            <p class="mb-0">
                                                <?= $student['created_at'] ? date('d.m.Y H:i', strtotime($student['created_at'])) : '—' ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <!-- İletişim Bilgileri -->
                                <div class="info-section mb-4">
                                    <h5 class="section-title">
                                        <i class="fa fa-phone text-success"></i> İletişim Bilgileri
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="text-muted small">Telefon 1</label>
                                            <p class="mb-0">
                                                <i class="fa fa-phone text-success"></i>
                                                <a href="tel:<?= htmlspecialchars($student['phone'] ?? '') ?>">
                                                    <?= formatPhone($student['phone'] ?? '') ?>
                                                </a>
                                            </p>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="text-muted small">Telefon 2</label>
                                            <p class="mb-0">
                                                <?php if ($student['phone2']): ?>
                                                    <i class="fa fa-phone text-info"></i>
                                                    <a href="tel:<?= htmlspecialchars($student['phone2']) ?>">
                                                        <?= formatPhone($student['phone2']) ?>
                                                    </a>
                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="text-muted small">Telefon 3</label>
                                            <p class="mb-0">
                                                <?php if ($student['phone3']): ?>
                                                    <i class="fa fa-phone text-warning"></i>
                                                    <a href="tel:<?= htmlspecialchars($student['phone3']) ?>">
                                                        <?= formatPhone($student['phone3']) ?>
                                                    </a>
                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="text-muted small">Adres</label>
                                            <p class="mb-0"><?= nl2br(htmlspecialchars($student['address'] ?? '—')) ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <!-- Aile Bilgileri -->
                                <div class="info-section mb-4">
                                    <h5 class="section-title">
                                        <i class="fa fa-users text-info"></i> Aile Bilgileri
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="text-muted small">Anne Adı</label>
                                            <p class="mb-0 font-weight-bold"><?= htmlspecialchars($student['mother_name'] ?? '—') ?></p>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="text-muted small">Baba Adı</label>
                                            <p class="mb-0 font-weight-bold"><?= htmlspecialchars($student['father_name'] ?? '—') ?></p>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="text-muted small">Veli Adı</label>
                                            <p class="mb-0 font-weight-bold"><?= htmlspecialchars($student['guardian_name'] ?? '—') ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Öğretmen Notu -->
                                <?php if (in_array($_SESSION['user']['role'] ?? '', ['admin', 'teacher'])): ?>
                                <hr>
                                <div class="info-section">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="section-title mb-0">
                                            <i class="fa fa-sticky-note text-warning"></i> Öğretmen Notu
                                            <small class="text-muted">(Öğrenci göremez)</small>
                                        </h5>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-warning" 
                                                data-toggle="collapse" 
                                                data-target="#teacherNoteForm">
                                            <i class="fa fa-edit"></i> Düzenle
                                        </button>
                                    </div>
                                    
                                    <div id="teacherNoteDisplay">
                                        <?php if (!empty($student['teacher_note'])): ?>
                                            <div class="alert alert-warning">
                                                <?= nl2br(htmlspecialchars($student['teacher_note'])) ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted"><em>Henüz not eklenmemiş.</em></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="collapse mt-3" id="teacherNoteForm">
                                        <form method="POST" action="index.php?module=students&action=update_teacher_note">
                                            <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                            <textarea class="form-control mb-2" 
                                                      name="teacher_note" 
                                                      rows="4" 
                                                      placeholder="Öğretmen notu yazınız..."><?= htmlspecialchars($student['teacher_note'] ?? '') ?></textarea>
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fa fa-save"></i> Kaydet
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-secondary" 
                                                    data-toggle="collapse" 
                                                    data-target="#teacherNoteForm">
                                                İptal
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                            </div>
                            
                            <!-- ========== ALDIĞI DERSLER SEKMESİ ========== -->
                            <div class="tab-pane fade" id="courses" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <i class="fa fa-book text-primary"></i> Aldığı Dersler
                                        <span class="badge badge-primary ml-2"><?= count($studentCourses) ?></span>
                                    </h5>
                                    <a href="index.php?module=students&action=assign_course&id=<?= $student['id'] ?>" 
                                       class="btn btn-success btn-sm">
                                        <i class="fa fa-plus"></i> Ders Ekle
                                    </a>
                                </div>
                                
                                <?php if (empty($studentCourses)): ?>
                                    <div class="text-center text-muted py-5">
                                        <i class="fa fa-book fa-3x mb-3"></i>
                                        <p class="mb-3">Henüz ders kaydı yapılmamış.</p>
                                        <a href="index.php?module=students&action=assign_course&id=<?= $student['id'] ?>" 
                                           class="btn btn-primary">
                                            <i class="fa fa-plus"></i> İlk Dersi Ekle
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <!-- Responsive Tablo -->
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover table-striped">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th style="width:40px">#</th>
                                                    <th>DERS ADI</th>
                                                    <th>ÖĞRETMEN</th>
                                                    <th class="text-center">KADEME</th>
                                                    <th class="text-center">DÖNEM</th>
                                                    <th class="text-center">YIL</th>
                                                    <th class="text-center">GÜN</th>
                                                    <th class="text-center">SAAT</th>
                                                    <th class="text-center">ÖĞRENCİ<br>SAYISI</th>
                                                    <th class="text-center">İŞLEMLER</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($studentCourses as $index => $course): ?>
                                                    <tr>
                                                        <td class="align-middle"><?= $index + 1 ?></td>
                                                        <td class="align-middle">
                                                            <div class="d-flex align-items-center">
                                                                <div class="rounded mr-2" 
                                                                     style="width:8px;height:35px;background:<?= htmlspecialchars($course['color'] ?? '#667eea') ?>;">
                                                                </div>
                                                                <div>
                                                                    <strong><?= htmlspecialchars($course['name']) ?></strong>
                                                                    <?php if (!empty($course['course_code'])): ?>
                                                                        <br><small class="text-muted"><?= htmlspecialchars($course['course_code']) ?></small>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle">
                                                            <?= htmlspecialchars($course['teacher_name'] ?? '—') ?>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <span class="badge badge-info">
                                                                <?php 
                                                                $categoryNames = [
                                                                    'ilkokul' => 'İlkokul',
                                                                    'ortaokul' => 'Ortaokul',
                                                                    'ortaokul_1' => 'Ortaokul 5-6',
                                                                    'ortaokul_2' => 'Ortaokul 7-8',
                                                                    'lise' => 'Lise'
                                                                ];
                                                                echo $categoryNames[$course['category'] ?? ''] ?? ucfirst($course['category'] ?? 'Diğer');
                                                                ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <span class="badge badge-secondary">
                                                                <?= htmlspecialchars($course['semester'] ?? 'Güz') ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <small class="text-muted">
                                                                <?= htmlspecialchars($course['year'] ?? date('Y')) ?>
                                                            </small>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <small>
                                                                <?= htmlspecialchars($course['day_of_week'] ?? $course['day'] ?? 'Pazartesi') ?>
                                                            </small>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <small>
                                                                <?php 
                                                                $timeSlot = $course['time_slot'] ?? '';
                                                                if (empty($timeSlot) && !empty($course['start_time'])) {
                                                                    $timeSlot = substr($course['start_time'], 0, 5) . '-' . substr($course['end_time'] ?? '', 0, 5);
                                                                }
                                                                echo htmlspecialchars($timeSlot ?: '09:00-10:00');
                                                                ?>
                                                            </small>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <span class="badge badge-pill badge-dark" style="font-size:0.9rem;">
                                                                <?= $course['student_count'] ?? 0 ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <a href="index.php?module=courses&action=show&id=<?= $course['id'] ?>" 
                                                                   class="btn btn-info" 
                                                                   title="Ders Detayı">
                                                                    <i class="fa fa-eye"></i>
                                                                </a>
                                                                <a href="index.php?module=students&action=remove_course_assignment&student_id=<?= $student['id'] ?>&course_id=<?= $course['id'] ?>" 
                                                                   class="btn btn-danger" 
                                                                   onclick="return confirm('Bu dersi kaldırmak istediğinizden emin misiniz?')"
                                                                   title="Dersten Çıkar">
                                                                    <i class="fa fa-trash"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Özet Bilgiler -->
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="card bg-primary text-white">
                                                <div class="card-body text-center py-2">
                                                    <h4 class="mb-0"><?= count($studentCourses) ?></h4>
                                                    <small>Toplam Ders</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-success text-white">
                                                <div class="card-body text-center py-2">
                                                    <h4 class="mb-0">
                                                        <?php 
                                                        $uniqueTeachers = array_unique(array_column($studentCourses, 'teacher_id'));
                                                        echo count(array_filter($uniqueTeachers));
                                                        ?>
                                                    </h4>
                                                    <small>Farklı Öğretmen</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-info text-white">
                                                <div class="card-body text-center py-2">
                                                    <h4 class="mb-0">
                                                        <?php 
                                                        $totalHours = 0;
                                                        foreach ($studentCourses as $course) {
                                                            if (!empty($course['start_time']) && !empty($course['end_time'])) {
                                                                $start = strtotime($course['start_time']);
                                                                $end = strtotime($course['end_time']);
                                                                $totalHours += ($end - $start) / 3600;
                                                            } else {
                                                                $totalHours += 1; // Varsayılan 1 saat
                                                            }
                                                        }
                                                        echo number_format($totalHours, 1);
                                                        ?>
                                                    </h4>
                                                    <small>Haftalık Saat</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- ========== SAĞLIK BİLGİLERİ SEKMESİ ========== -->
                            <div class="tab-pane fade" id="health" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <i class="fa fa-heartbeat text-danger"></i> Sağlık Bilgileri
                                    </h5>
                                    <a href="index.php?module=students&action=edit&id=<?= $student['id'] ?>" 
                                       class="btn btn-outline-danger btn-sm">
                                        <i class="fa fa-edit"></i> Düzenle
                                    </a>
                                </div>
                                
                                <?php if (!empty($student['health_info'])): ?>
                                    <div class="alert alert-danger">
                                        <h6 class="alert-heading">
                                            <i class="fa fa-exclamation-triangle"></i> Önemli Sağlık Bilgisi
                                        </h6>
                                        <?= nl2br(htmlspecialchars($student['health_info'])) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-5">
                                        <i class="fa fa-heartbeat fa-3x mb-3"></i>
                                        <p class="mb-3">Sağlık bilgisi eklenmemiş.</p>
                                        <a href="index.php?module=students&action=edit&id=<?= $student['id'] ?>" 
                                           class="btn btn-outline-danger">
                                            <i class="fa fa-plus"></i> Sağlık Bilgisi Ekle
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            
                            <!-- ========== DEVAMSIZLIK SEKMESİ ========== -->
                            <div class="tab-pane fade" id="attendance" role="tabpanel">
                                <h5 class="mb-4">
                                    <i class="fa fa-calendar-check text-warning"></i> Devamsızlık Bilgileri
                                </h5>
                                
                                <!-- Özet Kartlar -->
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="card bg-danger text-white text-center">
                                            <div class="card-body">
                                                <i class="fa fa-calendar-times fa-3x mb-2"></i>
                                                <h2 class="mb-0">—</h2>
                                                <p class="mb-0">Toplam Devamsızlık</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-warning text-white text-center">
                                            <div class="card-body">
                                                <i class="fa fa-exclamation-triangle fa-3x mb-2"></i>
                                                <h2 class="mb-0">—</h2>
                                                <p class="mb-0">Mazeretli</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-info text-white text-center">
                                            <div class="card-body">
                                                <i class="fa fa-percentage fa-3x mb-2"></i>
                                                <h2 class="mb-0">—%</h2>
                                                <p class="mb-0">Devam Oranı</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Ders Bazlı Devamsızlık -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fa fa-list"></i> Ders Bazlı Devamsızlık
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($studentCourses)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Ders Adı</th>
                                                            <th class="text-center">Toplam Ders</th>
                                                            <th class="text-center">Devamsızlık</th>
                                                            <th class="text-center">Mazeretli</th>
                                                            <th class="text-center">Devam Oranı</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($studentCourses as $course): 
                                                            $totalLessons = 30; // Varsayılan
                                                            $absences = 0; // TODO: Veritabanından çek
                                                            $excused = 0; // TODO: Veritabanından çek
                                                            $attendanceRate = $totalLessons > 0 ? (($totalLessons - $absences) / $totalLessons) * 100 : 0;
                                                        ?>
                                                            <tr>
                                                                <td>
                                                                    <strong><?= htmlspecialchars($course['name']) ?></strong>
                                                                </td>
                                                                <td class="text-center"><?= $totalLessons ?></td>
                                                                <td class="text-center">
                                                                    <span class="badge badge-danger"><?= $absences ?></span>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="badge badge-warning"><?= $excused ?></span>
                                                                </td>
                                                                <td class="text-center">
                                                                    <div class="progress" style="height:20px;width:100px;">
                                                                        <div class="progress-bar <?= $attendanceRate >= 75 ? 'bg-success' : 'bg-danger' ?>" 
                                                                             role="progressbar" 
                                                                             style="width: <?= $attendanceRate ?>%">
                                                                            <?= number_format($attendanceRate, 0) ?>%
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center text-muted py-4">
                                                <i class="fa fa-info-circle fa-2x mb-2"></i>
                                                <p>Henüz ders kaydı olmadığı için devamsızlık bilgisi bulunmamaktadır.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ========== SINAV SONUÇLARI SEKMESİ ========== -->
                            <div class="tab-pane fade" id="exams" role="tabpanel">
                                <h5 class="mb-4">
                                    <i class="fa fa-graduation-cap text-success"></i> Sınav Sonuçları
                                </h5>
                                
                                <!-- Kuruma Kabul Sınavı -->
                                <div class="card mb-3 border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">
                                            <i class="fa fa-trophy"></i> TKT
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 text-center">
                                                <p class="text-muted mb-1">Genel Puan</p>
                                                <h3 class="text-primary mb-0">
                                                    <?= htmlspecialchars($student['entrance_exam_score'] ?? '—') ?>
                                                </h3>
                                            </div>
                                            <div class="col-md-3 text-center">
                                                <p class="text-muted mb-1">Türkçe</p>
                                                <h4 class="mb-0">—</h4>
                                            </div>
                                            <div class="col-md-3 text-center">
                                                <p class="text-muted mb-1">Matematik</p>
                                                <h4 class="mb-0">—</h4>
                                            </div>
                                            <div class="col-md-3 text-center">
                                                <p class="text-muted mb-1">Sıralama</p>
                                                <h4 class="mb-0">—</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Ön Test ve Son Test -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="card border-info">
                                            <div class="card-header bg-info text-white">
                                                <h6 class="mb-0">
                                                    <i class="fa fa-pencil-alt"></i> Ön Test Sonuçları
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-sm mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Türkçe</strong></td>
                                                            <td class="text-right">—</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Matematik</strong></td>
                                                            <td class="text-right">—</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Fen Bilimleri</strong></td>
                                                            <td class="text-right">—</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Sosyal Bilgiler</strong></td>
                                                            <td class="text-right">—</td>
                                                        </tr>
                                                        <tr class="table-info">
                                                            <td><strong>Toplam</strong></td>
                                                            <td class="text-right"><strong>—</strong></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="card border-success">
                                            <div class="card-header bg-success text-white">
                                                <h6 class="mb-0">
                                                    <i class="fa fa-check-circle"></i> Son Test Sonuçları
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-sm mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Türkçe</strong></td>
                                                            <td class="text-right">—</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Matematik</strong></td>
                                                            <td class="text-right">—</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Fen Bilimleri</strong></td>
                                                            <td class="text-right">—</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Sosyal Bilgiler</strong></td>
                                                            <td class="text-right">—</td>
                                                        </tr>
                                                        <tr class="table-success">
                                                            <td><strong>Toplam</strong></td>
                                                            <td class="text-right"><strong>—</strong></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ⭐ YENİ: Durum Değiştirme Formu -->
<form id="statusChangeForm" method="POST" action="index.php?module=students&action=change_status" style="display:none;">
    <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
    <input type="hidden" name="new_status" id="newStatusInput">
</form>

<script>
// ⭐ Durum Değiştirme Fonksiyonu
function changeStatus(newStatus, statusText) {
    if (confirm('Öğrenci durumunu "' + statusText + '" olarak değiştirmek istediğinizden emin misiniz?')) {
        document.getElementById('newStatusInput').value = newStatus;
        document.getElementById('statusChangeForm').submit();
    }
}
</script>

<style>
/* Sticky Sidebar */
.sticky-sidebar {
    position: sticky;
    top: 70px;
    z-index: 100;
}

/* Sticky Tabs */
.sticky-tabs {
    position: sticky;
    top: 60px;
    z-index: 999;
    background: white;
    border-bottom: 2px solid #e0e0e0;
}

/* Sekme Stilleri */
.nav-tabs {
    border-bottom: none;
}

.nav-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    color: #6c757d;
    font-weight: 500;
    padding: 1rem 1.5rem;
    transition: all 0.3s;
}

.nav-tabs .nav-link:hover {
    color: #495057;
    border-bottom-color: #dee2e6;
}

.nav-tabs .nav-link.active {
    color: #007bff;
    border-bottom-color: #007bff;
    background: transparent;
}

/* Bölüm Başlıkları */
.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #f0f0f0;
}

.info-section label {
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
}

/* Kartlar */
.card {
    border: none;
    border-radius: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .sticky-sidebar {
        position: relative;
        top: 0;
    }
    
    .sticky-tabs {
        position: relative;
        top: 0;
    }
    
    .nav-tabs .nav-link {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
}
</style>

<!-- ⭐ Aktif/Pasif Durum Değiştirme Formu -->
<form id="toggleActiveForm" method="POST" action="index.php?module=students&action=toggle_active_status" style="display:none;">
    <input type="hidden" name="student_id" id="toggleStudentId">
    <input type="hidden" name="is_active" id="toggleIsActive">
</form>

<!-- ⭐ Enrollment Status Değiştirme Formu -->
<form id="statusForm" method="POST" action="index.php?module=students&action=change_status" style="display:none;">
    <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
    <input type="hidden" name="new_status" id="newStatus">
</form>

<script>
// Aktif/Pasif Toggle
function toggleActiveStatus(studentId, newStatus, statusText) {
    if (confirm('Öğrenciyi "' + statusText + '" yapmak istediğinizden emin misiniz?')) {
        document.getElementById('toggleStudentId').value = studentId;
        document.getElementById('toggleIsActive').value = newStatus;
        document.getElementById('toggleActiveForm').submit();
    }
}

// Enrollment Status Değiştir
function changeStatus(status, statusLabel) {
    if (confirm('Öğrenci durumunu "' + statusLabel + '" olarak değiştirmek istediğinizden emin misiniz?')) {
        document.getElementById('newStatus').value = status;
        document.getElementById('statusForm').submit();
    }
}
</script>