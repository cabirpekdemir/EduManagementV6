<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><?= $pageTitle ?></h1>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- ⭐ DİNAMİK WIDGET SİSTEMİ ⭐ -->
        <?php if (!empty($widgets)): ?>
            <div class="row">
                <?php 
                // Controller'ı bir kere oluştur (loop dışında)
                $widgetController = new DashboardController();
                
                // Toplam widget sayısını hesapla
                $totalWidgets = count($widgets);
                
                foreach ($widgets as $widget): 
                    // Her widget'ı render et (widget sayısını da gönder)
                    echo $widgetController->renderWidget($widget, $totalWidgets);
                endforeach; 
                ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                Henüz aktif widget bulunmuyor.
            </div>
        <?php endif; ?>

        <!-- RANDEVU WIDGET'LARI (MEVCUT - KORUNDU) -->
        <?php if (isset($appointmentData) && $appointmentData['isStaff']): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <h4 class="mb-3">Randevu Yönetimi</h4>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock"></i> Bekleyen Randevu Talepleri
                                <span class="badge badge-light ml-2"><?= $appointmentData['pendingCount'] ?></span>
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if ($appointmentData['pendingCount'] > 0): ?>
                                <a href="/?module=guidance&action=requests" class="btn btn-warning btn-block">
                                    <i class="fas fa-list"></i> Talepleri Görüntüle
                                </a>
                            <?php else: ?>
                                <p class="text-muted text-center">Bekleyen talep bulunmuyor.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-check"></i> Bugünkü Randevular
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($appointmentData['todayAppointments'])): ?>
                                <ul class="list-group">
                                    <?php foreach ($appointmentData['todayAppointments'] as $apt): ?>
                                        <li class="list-group-item">
                                            <strong><?= htmlspecialchars($apt['student_name']) ?></strong>
                                            <span class="badge badge-success float-right">
                                                <?= date('H:i', strtotime($apt['appointment_time'])) ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted text-center">Bugün randevu bulunmuyor.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- BUGÜNKÜ DERSLER (MEVCUT - KORUNDU) -->
        <?php if (!empty($todayCourses)): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <h4 class="mb-3">Bugünkü Derslerim</h4>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Bugünkü Ders Yoklamaları</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($todayCourses as $course): ?>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-header bg-<?= $course['color'] ?? 'primary' ?>">
                                        <h3 class="card-title text-white">
                                            <?= htmlspecialchars($course['name']) ?>
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Öğretmen:</strong> <?= htmlspecialchars($course['teacher_name']) ?></p>
                                        <p><strong>Öğrenci:</strong> <?= $course['student_count'] ?></p>
                                        <?php if ($course['today_attendance_count'] > 0): ?>
                                            <span class="badge badge-success">✓ Yoklama Alındı</span>
                                        <?php else: ?>
                                            <a href="/?module=attendance&action=take&course_id=<?= $course['id'] ?>" 
                                               class="btn btn-sm btn-warning btn-block">
                                                Yoklama Al
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>