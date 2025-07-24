<?php
// Gerekli değişkenler ($userRole, $stats vb.) controller'dan geliyor.
?>
<!-- Bu sayfaya özel stiller yerine AdminLTE'nin kendi sınıflarını kullanacağız. -->

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2>Ana Sayfa</h2>
            <p class="lead">Sisteme genel bakış ve son güncellemeler.</p>
        </div>
    </div>

    <!-- İSTATİSTİK KARTLARI -->
    <div class="row">
        <?php if ($userRole === 'admin' && isset($stats)): ?>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= htmlspecialchars($stats['students'] ?? 0) ?></h3>
                        <p>Toplam Öğrenci</p>
                    </div>
                    <div class="icon"><i class="fa fa-users"></i></div>
                    <a href="index.php?module=users&action=index&role_filter=student" class="small-box-footer">Detaylar <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= htmlspecialchars($stats['teachers'] ?? 0) ?></h3>
                        <p>Toplam Öğretmen</p>
                    </div>
                    <div class="icon"><i class="fa fa-user-tie"></i></div>
                    <a href="index.php?module=users&action=index&role_filter=teacher" class="small-box-footer">Detaylar <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= htmlspecialchars($stats['pending_requests'] ?? 0) ?></h3>
                        <p>Bekleyen Talepler</p>
                    </div>
                    <div class="icon"><i class="fa fa-tasks"></i></div>
                    <a href="index.php?module=course_requests&action=index" class="small-box-footer">Detaylar <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= htmlspecialchars($stats['pending_activities'] ?? 0) ?></h3>
                        <p>Onay Bekleyen Etkinlikler</p>
                    </div>
                    <div class="icon"><i class="fa fa-calendar-check-o"></i></div>
                    <a href="index.php?module=activities&action=index&status_filter=pending" class="small-box-footer">Detaylar <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        <?php elseif ($userRole === 'teacher' && isset($stats)): ?>
             <div class="col-lg-4 col-6">
                <div class="small-box bg-primary">
                    <div class="inner"><h3><?= htmlspecialchars($stats['my_students_count'] ?? 0) ?></h3><p>Sorumlu Öğrencilerim</p></div>
                    <div class="icon"><i class="fa fa-graduation-cap"></i></div>
                    <a href="index.php?module=students&action=index" class="small-box-footer">Detaylar <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
             <div class="col-lg-4 col-6">
                <div class="small-box bg-olive">
                    <div class="inner"><h3><?= htmlspecialchars($stats['my_courses_count'] ?? 0) ?></h3><p>Verdiğim Dersler</p></div>
                    <div class="icon"><i class="fa fa-book"></i></div>
                    <a href="index.php?module=courses&action=index" class="small-box-footer">Detaylar <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
             <div class="col-lg-4 col-6">
                <div class="small-box bg-orange">
                    <div class="inner"><h3><?= htmlspecialchars($stats['pending_course_requests_for_me'] ?? 0) ?></h3><p>Onayımı Bekleyen Talepler</p></div>
                    <div class="icon"><i class="fa fa-check-square-o"></i></div>
                    <a href="index.php?module=course_requests&action=index" class="small-box-footer">Detaylar <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        <?php elseif ($userRole === 'student' && isset($stats)): ?>
             <div class="col-lg-6 col-6">
                <div class="small-box bg-info">
                    <div class="inner"><h3><?= htmlspecialchars($stats['my_active_courses_count'] ?? 0) ?></h3><p>Kayıtlı Derslerim</p></div>
                    <div class="icon"><i class="fa fa-book"></i></div>
                    <a href="index.php?module=profile&action=index" class="small-box-footer">Detaylar <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
             <div class="col-lg-6 col-6">
                <div class="small-box bg-warning">
                    <div class="inner"><h3><?= htmlspecialchars($stats['my_pending_requests_count'] ?? 0) ?></h3><p>Bekleyen Ders İsteklerim</p></div>
                    <div class="icon"><i class="fa fa-hourglass-half"></i></div>
                    <a href="index.php?module=student_enrollment&action=index" class="small-box-footer">Detaylar <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- DUYURU VE ETKİNLİK KARTLARI -->
    <div class="row">
        <section class="col-lg-6 connectedSortable">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-bullhorn mr-1"></i> Son Duyurular</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_announcements)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach($recent_announcements as $announcement): ?>
                                <li class="list-group-item">
                                    <a href="index.php?module=announcements&action=view&id=<?= htmlspecialchars($announcement['id']) ?>"><?= htmlspecialchars($announcement['title']) ?></a>
                                    <span class="float-right text-muted text-sm"><?= htmlspecialchars(date('d.m.Y', strtotime($announcement['created_at']))) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Yeni duyuru bulunmamaktadır.</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center">
                    <a href="index.php?module=announcements&action=index">Tüm Duyuruları Görüntüle</a>
                </div>
            </div>
        </section>

        <section class="col-lg-6 connectedSortable">
             <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-calendar-alt mr-1"></i> Yaklaşan Etkinlikler</h3>
                </div>
                <div class="card-body">
                     <?php if (!empty($upcoming_activities)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach($upcoming_activities as $activity): ?>
                                <li class="list-group-item">
                                    <a href="index.php?module=activities&action=calendar#activity-<?= htmlspecialchars($activity['id']) ?>"><?= htmlspecialchars($activity['title']) ?></a>
                                    <span class="float-right badge bg-primary"><?= htmlspecialchars(date('d.m.Y H:i', strtotime($activity['activity_date']))) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Yaklaşan etkinlik bulunmamaktadır.</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center">
                    <a href="index.php?module=activities&action=calendar">Tüm Etkinlikleri Gör (Takvim)</a>
                </div>
            </div>
        </section>
    </div>

</div>
