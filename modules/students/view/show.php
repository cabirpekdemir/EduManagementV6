<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$student = $student ?? [];
$teacher = $teacher ?? [];
$courses = $courses ?? [];
$exams = $exams ?? [];
$pretests = $pretests ?? [];
$attendance = $attendance ?? [];
$attendanceStats = $attendanceStats ?? [];
$guidance = $guidance ?? [];
$health = $health ?? [];
$admissionExam = $admissionExam ?? null; // Kabul sınavı sonucu

// Durum isimleri
$statusNames = [
    'on_kayit' => ['text' => 'Ön Kayıt', 'color' => 'secondary', 'icon' => '📝'],
    'sinav_secim' => ['text' => 'Sınav Seçim', 'color' => 'primary', 'icon' => '📋'],
    'sinav_secimi_yapti' => ['text' => 'Sınav Seçimi Yaptı', 'color' => 'info', 'icon' => '✅'],
    'ders_secimi_yapan' => ['text' => 'Ders Seçimi Yapan', 'color' => 'success', 'icon' => '📚'],
    'sinav_sonuc_girisi' => ['text' => 'Sınav Sonuç Girişi', 'color' => 'warning', 'icon' => '📊'],
    'sinavi_kazanamayan' => ['text' => 'Sınavı Kazanamayan', 'color' => 'danger', 'icon' => '❌'],
    'aktif' => ['text' => 'Aktif Öğrenci', 'color' => 'success', 'icon' => '✅'],
    'kayit_dondurma' => ['text' => 'Kayıt Dondurma', 'color' => 'warning', 'icon' => '⏸️'],
    'kayit_silinen' => ['text' => 'Kayıt Silinen', 'color' => 'dark', 'icon' => '🗑️'],
    'mezun' => ['text' => 'Mezun', 'color' => 'primary', 'icon' => '🎓']
];

$teachingTypes = [
    'tam_gun' => ['text' => 'Tam Gün', 'icon' => '🕐'],
    'sabahci' => ['text' => 'Sabahçı', 'icon' => '🌅'],
    'oglenci' => ['text' => 'Öğlenci', 'icon' => '🌆']
];

$status = $student['enrollment_status'] ?? 'on_kayit';
$statusInfo = $statusNames[$status] ?? ['text' => $status, 'color' => 'secondary', 'icon' => '📌'];

$teachingType = $student['teaching_type'] ?? null;
$teachingInfo = $teachingType ? ($teachingTypes[$teachingType] ?? ['text' => $teachingType, 'icon' => '📚']) : null;

$photo = $student['profile_photo'] ?? 'https://via.placeholder.com/120x120?text=👤';
$hasHealthAlert = $health && (!empty($health['chronic_condition']) || !empty($health['allergy']));

// Toplam devamsızlık hesapla
$totalAbsence = 0;
if (!empty($attendanceStats)) {
    foreach ($attendanceStats as $stat) {
        $totalAbsence += (int)($stat['absent_count'] ?? 0);
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
        
        <div class="mb-2">
            <a href="index.php?module=students&action=list" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Listeye Dön
            </a>
            <a href="index.php?module=students&action=edit&id=<?= (int)$student['id'] ?>" class="btn btn-primary btn-sm">
                <i class="fa fa-pen"></i> Düzenle
            </a>
            
            <!-- ⭐ YENİ: Durum Değiştirme Dropdown -->
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-warning btn-sm dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-exchange-alt"></i> Durum Değiştir
                </button>
                <div class="dropdown-menu">
                    <?php foreach ($statusNames as $statusKey => $statusData): ?>
                        <?php if ($statusKey !== $status): ?>
                            <a class="dropdown-item" href="javascript:void(0)" 
                               onclick="changeStatus('<?= $statusKey ?>', '<?= h($statusData['text']) ?>')">
                                <?= $statusData['icon'] ?> <?= h($statusData['text']) ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- ⭐ YENİ: Transkript PDF Butonu -->
            <a href="index.php?module=students&action=transcript_pdf&id=<?= (int)$student['id'] ?>" 
               class="btn btn-danger btn-sm" target="_blank">
                <i class="fa fa-file-pdf"></i> Transkript PDF
            </a>
        </div>

<div class="row">
    <!-- SOL: Profil Kartı -->
    <div class="col-md-2">
        <div class="card shadow-sm">
            <div class="card-body text-center p-2">
                <img src="<?= h($photo) ?>" class="rounded-circle border mb-2" 
                     style="width:120px;height:120px;object-fit:cover;">
                
                <h6 class="mb-1"><?= h($student['name'] ?? '') ?></h6>
                
                <?php if (!empty($student['student_number'])): ?>
                    <div class="mb-2">
                        <span class="badge bg-dark"><?= h($student['student_number']) ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="mb-2">
                    <span class="badge badge-<?= $statusInfo['color'] ?> px-2 py-1 small">
                        <?= $statusInfo['icon'] ?> <?= h($statusInfo['text']) ?>
                    </span>
                </div>
                
                <div class="text-muted small mb-1">
                    Sınıf: <?= h($student['class_name'] ?? $student['sinif'] ?? '—') ?>
                </div>
                
                <?php if (!empty($teacher['name'])): ?>
                    <div class="text-muted small mb-2">
                        Öğretmen: <?= h($teacher['name']) ?>
                    </div>
                <?php endif; ?>

                <?php if ($teachingInfo): ?>
                    <div class="mb-2">
                        <span class="badge badge-info small">
                            <?= $teachingInfo['icon'] ?> <?= h($teachingInfo['text']) ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($student['special_talent'])): ?>
                    <div class="alert alert-warning py-1 mb-2 small">
                        <strong>⭐ Özel Yetenek</strong>
                    </div>
                <?php endif; ?>
                
                <!-- ⭐ YENİ: Toplam Devamsızlık Göstergesi -->
                <?php if ($totalAbsence > 0): ?>
                    <div class="alert alert-danger py-1 mb-0 small">
                        <i class="fa fa-calendar-times"></i> 
                        <strong><?= $totalAbsence ?></strong> Devamsızlık
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($hasHealthAlert): ?>
            <div class="alert alert-danger p-2 mt-2 small">
                <h6 class="small mb-1">
                    <i class="fa fa-exclamation-triangle"></i> Sağlık Uyarısı
                </h6>
                <?php if (!empty($health['chronic_condition'])): ?>
                    <strong>Kronik:</strong> <?= h($health['chronic_condition']) ?><br>
                <?php endif; ?>
                <?php if (!empty($health['allergy'])): ?>
                    <strong>Alerji:</strong> <?= h($health['allergy']) ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- SAĞ: Sekmeler -->
    <div class="col-md-10">
        <ul class="nav nav-tabs nav-tabs-sm mb-2" id="studentTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active small py-2" href="#info" data-toggle="tab">
                    📋 Bilgiler
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link small py-2" href="#courses" data-toggle="tab">
                    📚 Dersler
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link small py-2" href="#attendance" data-toggle="tab">
                    📅 Devamsızlık
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link small py-2" href="#admission" data-toggle="tab">
                    🎯 Kabul Sınavı
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link small py-2" href="#pretests" data-toggle="tab">
                    📊 Öntest-Sontest
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link small py-2" href="#exams" data-toggle="tab">
                    📝 TKD Sınavlar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link small py-2" href="#guidance" data-toggle="tab">
                    🧭 Rehberlik
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger small py-2" href="#health" data-toggle="tab">
                    💊 Sağlık
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Bilgiler Sekmesi -->
            <div class="tab-pane fade show active" id="info">
                <div class="card shadow-sm">
                    <div class="card-body p-3">
                        <div class="row g-2 small">
                            <div class="col-md-3">
                                <strong class="text-muted">E-posta</strong>
                                <div><?= h($student['email'] ?? '—') ?></div>
                            </div>
                            <div class="col-md-3">
                                <strong class="text-muted">TC Kimlik</strong>
                                <div><?= h($student['tc_kimlik'] ?? '—') ?></div>
                            </div>
                            <div class="col-md-3">
                                <strong class="text-muted">Doğum Tarihi</strong>
                                <div><?= h($student['birth_date'] ?? '—') ?></div>
                            </div>
                            <div class="col-md-3">
                                <strong class="text-muted">Doğum Yeri</strong>
                                <div><?= h($student['birth_place'] ?? '—') ?></div>
                            </div>
                            <div class="col-md-3">
                                <strong class="text-muted">Telefon 1</strong>
                                <div><?= h($student['phone'] ?? '—') ?></div>
                            </div>
                            <div class="col-md-3">
                                <strong class="text-muted">Telefon 2</strong>
                                <div><?= h($student['phone2'] ?? '—') ?></div>
                            </div>
                            <div class="col-md-3">
                                <strong class="text-muted">Telefon 3</strong>
                                <div><?= h($student['phone3'] ?? '—') ?></div>
                            </div>
                            <div class="col-md-3">
                                <strong class="text-muted">Okul</strong>
                                <div><?= h($student['okul'] ?? '—') ?></div>
                            </div>
                            <div class="col-md-3">
                                <strong class="text-muted">Anne Adı</strong>
                                <div><?= h($student['anne_adi'] ?? '—') ?></div>
                            </div>
                            <div class="col-md-3">
                                <strong class="text-muted">Baba Adı</strong>
                                <div><?= h($student['baba_adi'] ?? '—') ?></div>
                            </div>
                            <div class="col-md-6">
                                <strong class="text-muted">Adres</strong>
                                <div><?= h($student['adres'] ?? '—') ?></div>
                            </div>
                            <?php if (!empty($student['durum_aciklama'])): ?>
                                <div class="col-md-12">
                                    <strong class="text-muted">Durum Açıklama</strong>
                                    <div><?= nl2br(h($student['durum_aciklama'])) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ⭐ GELİŞTİRİLMİŞ: Dersler Sekmesi -->
            <div class="tab-pane fade" id="courses">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fa fa-book"></i> Kayıtlı Dersler
                                <span class="badge badge-light text-dark"><?= count($courses) ?></span>
                            </h6>
                            <a href="index.php?module=students&action=assign_course&id=<?= $student['id'] ?>" 
                               class="btn btn-light btn-sm">
                                <i class="fa fa-plus"></i> Ders Ekle
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($courses)): ?>
                            <div class="alert alert-info mb-0 small m-2">Henüz ders kaydı yapılmamış.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ders Adı</th>
                                            <th>Öğretmen</th>
                                            <th>Dönem</th>
                                            <th>Yıl</th>
                                            <th>Gün</th>
                                            <th>Saat</th>
                                            <th>Kademe</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($courses as $course): ?>
                                            <tr>
                                                <td><strong><?= h($course['name']) ?></strong></td>
                                                <td><?= h($course['teacher_name'] ?? '—') ?></td>
                                                <td>
                                                    <?php if (!empty($course['term'])): ?>
                                                        <span class="badge badge-info">
                                                            <?= h($course['term']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= h($course['year'] ?? '—') ?></td>
                                                <td>
                                                    <?php if (!empty($course['day'])): ?>
                                                        <span class="badge badge-secondary">
                                                            <?= h($course['day']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($course['time'])): ?>
                                                        <small class="text-primary">
                                                            <i class="fa fa-clock"></i> <?= h($course['time']) ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">
                                                        <?= ucfirst(h($course['category'] ?? 'Diğer')) ?>
                                                    </span>
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

            <!-- ⭐ GELİŞTİRİLMİŞ: Devamsızlık Sekmesi (Ders Bazlı + Toplam) -->
            <div class="tab-pane fade" id="attendance">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark p-2">
                        <h6 class="mb-0">
                            <i class="fa fa-calendar-times"></i> Devamsızlık Bilgileri
                            <span class="badge badge-danger"><?= $totalAbsence ?> Toplam</span>
                        </h6>
                    </div>
                    <div class="card-body p-2">
                        <?php if (empty($attendanceStats)): ?>
                            <div class="alert alert-success mb-0 small">
                                <i class="fa fa-check-circle"></i> Devamsızlık kaydı bulunmuyor.
                            </div>
                        <?php else: ?>
                            <!-- Ders Bazlı Devamsızlık -->
                            <h6 class="small text-muted mb-2">📚 Ders Bazlı Devamsızlık</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ders Adı</th>
                                            <th class="text-center">Devamsızlık Sayısı</th>
                                            <th class="text-center">Durum</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($attendanceStats as $stat): 
                                            $absentCount = (int)($stat['absent_count'] ?? 0);
                                            $statusBadge = 'success';
                                            $statusText = 'İyi';
                                            
                                            if ($absentCount >= 10) {
                                                $statusBadge = 'danger';
                                                $statusText = 'Kritik';
                                            } elseif ($absentCount >= 5) {
                                                $statusBadge = 'warning';
                                                $statusText = 'Dikkat';
                                            }
                                        ?>
                                            <tr>
                                                <td><strong><?= h($stat['course_name'] ?? 'Bilinmeyen Ders') ?></strong></td>
                                                <td class="text-center">
                                                    <span class="badge badge-<?= $statusBadge ?> px-3">
                                                        <?= $absentCount ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-<?= $statusBadge ?>">
                                                        <?= $statusText ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td><strong>TOPLAM DEVAMSIZLIK</strong></td>
                                            <td class="text-center">
                                                <span class="badge badge-danger px-3 py-2">
                                                    <strong><?= $totalAbsence ?></strong>
                                                </span>
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <!-- Detaylı Devamsızlık Geçmişi -->
                            <?php if (!empty($attendance)): ?>
                                <hr class="my-3">
                                <h6 class="small text-muted mb-2">📅 Devamsızlık Geçmişi</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Tarih</th>
                                                <th>Ders</th>
                                                <th>Durum</th>
                                                <th>Not</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($attendance as $att): ?>
                                                <tr>
                                                    <td><?= date('d.m.Y', strtotime($att['date'])) ?></td>
                                                    <td><?= h($att['course_name'] ?? '—') ?></td>
                                                    <td>
                                                        <span class="badge badge-danger">
                                                            <?= h($att['status'] ?? 'Devamsız') ?>
                                                        </span>
                                                    </td>
                                                    <td><small><?= h($att['note'] ?? '') ?></small></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ⭐ YENİ: Kabul Sınavı Sekmesi -->
            <div class="tab-pane fade" id="admission">
                <div class="card border-primary shadow-sm">
                    <div class="card-header bg-primary text-white p-2">
                        <h6 class="mb-0">
                            <i class="fa fa-graduation-cap"></i> TKT
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <?php if (empty($admissionExam)): ?>
                            <div class="alert alert-info mb-0">
                                TKT sonucu henüz girilmemiş.
                            </div>
                        <?php else: 
                            $score = (int)($admissionExam['score'] ?? 0);
                            $maxScore = (int)($admissionExam['max_score'] ?? 100);
                            $percentage = $maxScore > 0 ? round(($score / $maxScore) * 100, 1) : 0;
                            
                            $badgeClass = 'secondary';
                            if ($percentage >= 85) $badgeClass = 'success';
                            elseif ($percentage >= 70) $badgeClass = 'primary';
                            elseif ($percentage >= 50) $badgeClass = 'warning';
                            elseif ($percentage > 0) $badgeClass = 'danger';
                        ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6 class="text-muted small mb-1">Sınav Tarihi</h6>
                                        <div class="h5">
                                            <?= date('d.m.Y', strtotime($admissionExam['exam_date'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6 class="text-muted small mb-1">Puan</h6>
                                        <div class="h5">
                                            <?= $score ?> / <?= $maxScore ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6 class="text-muted small mb-1">Yüzde</h6>
                                        <div>
                                            <span class="badge badge-<?= $badgeClass ?> px-3 py-2 h5">
                                                %<?= $percentage ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6 class="text-muted small mb-1">Durum</h6>
                                        <div>
                                            <?php if ($percentage >= 50): ?>
                                                <span class="badge badge-success px-3 py-2">
                                                    <i class="fa fa-check-circle"></i> Başarılı
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-danger px-3 py-2">
                                                    <i class="fa fa-times-circle"></i> Başarısız
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($admissionExam['note'])): ?>
                                    <div class="col-12 mt-3">
                                        <hr>
                                        <h6 class="text-muted small mb-1">Not</h6>
                                        <p class="mb-0"><?= nl2br(h($admissionExam['note'])) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Öntest-Sontest Sekmesi -->
            <div class="tab-pane fade" id="pretests">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white p-2">
                        <h6 class="mb-0">
                            <i class="fa fa-chart-line"></i> Öntest - Sontest Sonuçları
                        </h6>
                    </div>
                    <div class="card-body p-2">
                        <?php if (empty($pretests)): ?>
                            <div class="alert alert-info mb-0 small">Ön/Son test kaydı bulunamadı.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tarih</th>
                                            <th>Ders</th>
                                            <th>Tür</th>
                                            <th>Başlık</th>
                                            <th>Puan</th>
                                            <th>Yüzde</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pretests as $pt): 
                                            $percentage = 0;
                                            if (isset($pt['score'], $pt['max_score']) && $pt['max_score'] > 0) {
                                                $percentage = round(($pt['score'] / $pt['max_score']) * 100, 1);
                                            }
                                            
                                            $percentClass = 'secondary';
                                            if ($percentage >= 85) $percentClass = 'success';
                                            elseif ($percentage >= 70) $percentClass = 'primary';
                                            elseif ($percentage >= 50) $percentClass = 'warning';
                                            elseif ($percentage > 0) $percentClass = 'danger';
                                        ?>
                                            <tr>
                                                <td><?= date('d.m.Y', strtotime($pt['date'])) ?></td>
                                                <td><?= h($pt['course_name'] ?? '—') ?></td>
                                                <td>
                                                    <span class="badge badge-<?= $pt['type'] === 'ontest' ? 'info' : 'success' ?>">
                                                        <?= $pt['type'] === 'ontest' ? 'Öntest' : 'Sontest' ?>
                                                    </span>
                                                </td>
                                                <td><?= h($pt['title']) ?></td>
                                                <td><?= h($pt['score']) ?> / <?= h($pt['max_score']) ?></td>
                                                <td>
                                                    <span class="badge badge-<?= $percentClass ?>">
                                                        %<?= $percentage ?>
                                                    </span>
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

            

            <!-- Rehberlik Sekmesi -->
            <div class="tab-pane fade" id="guidance">
                <div class="card shadow-sm">
                    <div class="card-body p-2">
                        <?php if (empty($guidance)): ?>
                            <div class="alert alert-info mb-0 small">Rehberlik kaydı bulunamadı.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tarih</th>
                                            <th>Danışman</th>
                                            <th>Konu</th>
                                            <th>Not</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($guidance as $g): ?>
                                            <tr>
                                                <td><?= h($g['date'] ?? '—') ?></td>
                                                <td><?= h($g['counselor'] ?? '—') ?></td>
                                                <td><?= h($g['topic'] ?? '—') ?></td>
                                                <td><?= h($g['note'] ?? '') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sağlık Sekmesi -->
            <div class="tab-pane fade" id="health">
                <div class="card border-danger shadow-sm">
                    <div class="card-body p-3">
                        <div class="row g-2 small">
                            <div class="col-md-4">
                                <strong class="text-danger">Kronik Rahatsızlık</strong>
                                <p><?= h($health['chronic_condition'] ?? '—') ?></p>
                            </div>
                            <div class="col-md-4">
                                <strong class="text-danger">Kan Grubu</strong>
                                <p><?= h($health['blood_type'] ?? '—') ?></p>
                            </div>
                            <div class="col-md-4">
                                <strong class="text-warning">Kullandığı İlaçlar</strong>
                                <p><?= h($health['medications'] ?? '—') ?></p>
                            </div>
                            <div class="col-md-6">
                                <strong class="text-warning">Alerji</strong>
                                <p><?= h($health['allergy'] ?? '—') ?></p>
                            </div>
                            <div class="col-md-6">
                                <strong>Sağlık Notları</strong>
                                <p><?= nl2br(h($health['notes'] ?? '—')) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ⭐ DURUM DEĞİŞTİRME MODAL -->
<form id="statusChangeForm" method="POST" action="index.php?module=students&action=change_status">
    <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
    <input type="hidden" name="new_status" id="newStatusInput">
</form>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bootstrap tab navigation
    const triggerTabList = document.querySelectorAll('#studentTabs a[data-toggle="tab"]');
    triggerTabList.forEach(triggerEl => {
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            $(this).tab('show');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
});

// ⭐ DURUM DEĞİŞTİRME FONKSİYONU
function changeStatus(newStatus, statusText) {
    if (confirm('Öğrenci durumunu "' + statusText + '" olarak değiştirmek istediğinizden emin misiniz?')) {
        document.getElementById('newStatusInput').value = newStatus;
        document.getElementById('statusChangeForm').submit();
    }
}
</script>

<style>
.nav-tabs-sm .nav-link {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}
</style>
