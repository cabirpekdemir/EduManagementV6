<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$courses = $courses ?? [];

// Flash mesajlar
if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= h($_SESSION['flash_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); endif;

if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= h($_SESSION['flash_error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); endif;
?>

<!-- Üst Bilgi Kartı -->
<div class="card shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="card-body text-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">📋 Ders Yoklaması</h4>
                <p class="mb-0">Derslerin yoklamasını alın ve raporları görüntüleyin</p>
            </div>
            <div>
                <a href="index.php?module=lesson_attendance&action=report" class="btn btn-light">
                    <i class="fa fa-bar-chart"></i> Raporlar
                </a>
            </div>
        </div>
    </div>
</div>

<!-- İstatistik Kartları -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card hover-lift">
            <div class="card-body text-center">
                <h3 class="text-primary mb-0"><?= count($courses) ?></h3>
                <p class="text-muted mb-0">Toplam Ders</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card hover-lift">
            <div class="card-body text-center">
                <h3 class="text-success mb-0"><?= date('d.m.Y') ?></h3>
                <p class="text-muted mb-0">Bugün</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card hover-lift">
            <div class="card-body text-center">
                <h3 class="text-info mb-0">
                    <?php
                    $days = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
                    echo $days[date('w')];
                    ?>
                </h3>
                <p class="text-muted mb-0">Bugün</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card hover-lift">
            <div class="card-body text-center">
                <h3 class="text-warning mb-0"><?= date('H:i') ?></h3>
                <p class="text-muted mb-0">Saat</p>
            </div>
        </div>
    </div>
</div>

<!-- Ders Listesi -->
<div class="card shadow-sm">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Dersler</h5>
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text"><i class="fa fa-search"></i></span>
                <input type="text" class="form-control" id="searchCourse" placeholder="Ders ara...">
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($courses)): ?>
            <div class="text-center text-muted py-5">
                <i class="fa fa-book fa-3x mb-3"></i>
                <p>Kayıtlı ders bulunamadı.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle" id="coursesTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Ders Adı</th>
                            <th style="width:120px">Kademe</th>
                            <th>Öğretmen</th>
                            <th style="width:120px" class="text-center">Öğrenci</th>
                            <th style="width:150px" class="text-center">Bugün</th>
                            <th class="text-end" style="width:200px">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $index = 1;
                        foreach ($courses as $course): 
                            $catColors = [
                                'ilkokul' => 'primary',
                                'ortaokul' => 'info',
                                'lise' => 'danger'
                            ];
                            $catNames = [
                                'ilkokul' => 'İlkokul',
                                'ortaokul' => 'Ortaokul',
                                'lise' => 'Lise'
                            ];
                            $catColor = $catColors[$course['category'] ?? ''] ?? 'secondary';
                            $catName = $catNames[$course['category'] ?? ''] ?? 'Diğer';
                        ?>
                            <tr class="course-row">
                                <td><?= $index++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle me-2" 
                                             style="width:40px;height:40px;background:<?= h($course['color'] ?? '#667eea') ?>;
                                                    display:flex;align-items:center;justify-content:center;
                                                    color:white;font-weight:bold;">
                                            <?= strtoupper(substr($course['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?= h($course['name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $catColor ?>">
                                        <?= $catName ?>
                                    </span>
                                </td>
                                <td><?= h($course['teacher_name'] ?? '—') ?></td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">
                                        <?= (int)($course['student_count'] ?? 0) ?> Öğrenci
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($course['today_attendance_count'])): ?>
                                        <span class="badge bg-success">
                                            <i class="fa fa-check"></i> Alındı
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="fa fa-clock-o"></i> Bekliyor
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="index.php?module=lesson_attendance&action=take&course_id=<?= (int)$course['id'] ?>&date=<?= date('Y-m-d') ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fa fa-list-ul"></i> Yoklama Al
                                    </a>
                                    <a href="index.php?module=lesson_attendance&action=report&filter_course_id=<?= (int)$course['id'] ?>" 
                                       class="btn btn-sm btn-outline-info">
                                        <i class="fa fa-bar-chart"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Ders arama
document.getElementById('searchCourse').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('.course-row');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<style>
.hover-lift {
    transition: all 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}
</style>