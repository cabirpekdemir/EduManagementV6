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
        
        <!-- RANDEVU WİDGET'LARI (MEVCUT - KORUNDU) -->
        <?php if (!empty($appointmentData)): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <h4 class="mb-3">Rehberlik Randevularım</h4>
                </div>
            </div>
            
            <div class="row">
                <!-- Bekleyen Talepler -->
                <?php if (!empty($appointmentData['pendingRequests'])): ?>
                    <div class="col-md-6">
                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fa fa-clock"></i> Bekleyen Taleplerim</h3>
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    <?php foreach ($appointmentData['pendingRequests'] as $req): ?>
                                        <li class="list-group-item">
                                            <div class="mb-1">
                                                <span class="badge badge-warning">
                                                    <?= date('d.m.Y', strtotime($req['requested_date'])) ?>
                                                </span>
                                                <span class="badge badge-secondary">
                                                    <?= date('H:i', strtotime($req['requested_time'])) ?>
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                Gönderim: <?= date('d.m.Y H:i', strtotime($req['created_at'])) ?>
                                            </small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="card-footer text-center">
                                <a href="index.php?module=guidance&action=myRequests">Tüm Taleplerim</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Onaylanan Randevular -->
                <div class="col-md-6">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-calendar-check"></i> Yaklaşan Randevularım</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($appointmentData['upcomingAppointments'])): ?>
                                <div class="text-center text-muted py-4">
                                    <i class="fa fa-calendar-alt fa-3x mb-3"></i>
                                    <p class="mb-0">Onaylanmış randevunuz bulunmuyor.</p>
                                    <a href="index.php?module=guidance&action=requestForm" 
                                       class="btn btn-sm btn-primary mt-2">
                                        <i class="fa fa-plus"></i> Randevu Talebi Oluştur
                                    </a>
                                </div>
                            <?php else: ?>
                                <ul class="list-group">
                                    <?php foreach ($appointmentData['upcomingAppointments'] as $apt): ?>
                                        <li class="list-group-item">
                                            <div class="mb-1">
                                                <span class="badge badge-success">
                                                    <?= date('d.m.Y', strtotime($apt['appointment_date'])) ?>
                                                </span>
                                                <span class="badge badge-primary">
                                                    <?= date('H:i', strtotime($apt['appointment_time'])) ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($apt['counselor_name'])): ?>
                                                <small class="text-muted">
                                                    <i class="fa fa-user"></i> <?= htmlspecialchars($apt['counselor_name']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer text-center">
                            <a href="index.php?module=guidance&action=myRequests">Tüm Randevularım</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hızlı Erişim -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <a href="index.php?module=guidance&action=requestForm" class="btn btn-primary btn-lg mr-2">
                                <i class="fa fa-calendar-plus"></i> Yeni Randevu Talebi
                            </a>
                            <a href="index.php?module=guidance&action=myRequests" class="btn btn-info btn-lg mr-2">
                                <i class="fa fa-list"></i> Taleplerim
                            </a>
                            <a href="index.php?module=guidance&action=index" class="btn btn-success btn-lg">
                                <i class="fa fa-user-friends"></i> Rehberlik Seansları
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>