<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><?= $pageTitle ?></h1>
            </div>
            <div class="col-sm-6">
                <div class="float-right">
                   <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>?page=dashboard&action=manage" class="btn btn-sm btn-primary">
                        <i class="fas fa-cog"></i> Widget Yönetimi
                    </a>
                </div>
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
                <a href="index.php?module=dashboard&action=manage" class="alert-link">Widget Yönetimi</a>'nden widget ekleyebilirsiniz.
            </div>
        <?php endif; ?>

        <!-- ESKİ İSTATİSTİKLER (İSTERSENİZ KALDIRAB İLİRSİNİZ) -->
        <!-- 
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $stats['student_count'] ?></h3>
                        <p>Toplam Öğrenci</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
            </div>
            ... diğer statlar ...
        </div>
        -->

        <!-- RANDEVU WIDGET'LARI (MEVCUT - AYNI KALDI) -->
        <?php if (isset($appointmentData) && $appointmentData['isStaff']): ?>
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

       

    </div>
</section>