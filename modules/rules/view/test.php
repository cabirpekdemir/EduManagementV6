<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$students = $students ?? [];
$courses = $courses ?? [];
$studentId = $studentId ?? 0;
$courseId = $courseId ?? 0;
$result = $result ?? null;

// Flash mesajlar
if (isset($_SESSION['flash_warning'])): ?>
    <div class="alert alert-warning alert-dismissible fade show">
        <?= h($_SESSION['flash_warning']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_warning']); endif;
?>

<div class="mb-3">
    <a href="index.php?module=rules&action=list" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left"></i> Kurallara Dön
    </a>
    <?php if (empty($courses)): ?>
        <a href="index.php?module=courses&action=create" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Önce Ders Ekleyin
        </a>
    <?php endif; ?>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">
            <i class="fa fa-flask"></i> Kural Motoru Test Aracı
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($students)): ?>
            <div class="alert alert-danger">
                <strong>Hata:</strong> Sistemde öğrenci bulunamadı. 
                <a href="index.php?module=students&action=create">Önce öğrenci ekleyin</a>.
            </div>
        <?php elseif (empty($courses)): ?>
            <div class="alert alert-danger">
                <strong>Hata:</strong> Sistemde ders bulunamadı. 
                <a href="index.php?module=courses&action=create">Önce ders ekleyin</a>.
            </div>
        <?php else: ?>
            <p class="text-muted mb-4">
                Bir öğrencinin belirli bir derse kayıt olup olamayacağını test edin.
            </p>

            <form method="get" action="index.php">
                <input type="hidden" name="module" value="rules">
                <input type="hidden" name="action" value="test">
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Öğrenci <span class="text-danger">*</span></label>
                        <select name="student_id" class="form-select" required>
                            <option value="">— Öğrenci Seçin —</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= (int)$student['id'] ?>" 
                                        <?= (int)$studentId === (int)$student['id'] ? 'selected' : '' ?>>
                                    <?= h($student['name']) ?>
                                    <?php if (!empty($student['sinif'])): ?>
                                        - Sınıf: <?= h($student['sinif']) ?>
                                    <?php elseif (!empty($student['class_name'])): ?>
                                        - <?= h($student['class_name']) ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted"><?= count($students) ?> öğrenci mevcut</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Ders <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-select" required>
                            <option value="">— Ders Seçin —</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= (int)$course['id'] ?>" 
        <?= (int)$courseId === (int)$course['id'] ? 'selected' : '' ?>>
    <?= h($course['name']) ?>
    <?php if (isset($course['code']) && !empty($course['code'])): ?>
        (<?= h($course['code']) ?>)
    <?php endif; ?>
</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted"><?= count($courses) ?> ders mevcut</small>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-play"></i> Test Et
                        </button>
                        <a href="index.php?module=rules&action=test" class="btn btn-outline-secondary">
                            Temizle
                        </a>
                    </div>
                </div>
            </form>
        <?php endif; ?>

        <?php if ($result): ?>
            <hr class="my-4">
            
            <h5 class="mb-3">📊 Test Sonucu:</h5>
            
            <?php if ($result['can_enroll']): ?>
                <div class="alert alert-success">
                    <h5 class="alert-heading">
                        <i class="fa fa-check-circle"></i> Başarılı!
                    </h5>
                    <p class="mb-0">
                        Öğrenci bu derse kayıt olabilir. Kural ihlali tespit edilmedi.
                    </p>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <h5 class="alert-heading">
                        <i class="fa fa-times-circle"></i> Kayıt Yapılamaz
                    </h5>
                    <p><strong>Tespit edilen ihlaller:</strong></p>
                    <ul class="mb-0">
                        <?php foreach ($result['violations'] as $violation): ?>
                            <li><?= h($violation) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($result['warnings'])): ?>
                <div class="alert alert-warning">
                    <h6 class="alert-heading">
                        <i class="fa fa-exclamation-triangle"></i> Uyarılar
                    </h6>
                    <ul class="mb-0">
                        <?php foreach ($result['warnings'] as $warning): ?>
                            <li><?= h($warning) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($students) && !empty($courses)): ?>
<div class="card shadow-sm mt-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="fa fa-info-circle"></i> Bilgi</h6>
    </div>
    <div class="card-body">
        <p class="mb-2"><strong>Sistem Durumu:</strong></p>
        <ul class="mb-0">
            <li>✅ <?= count($students) ?> öğrenci kayıtlı</li>
            <li>✅ <?= count($courses) ?> ders mevcut</li>
            <li>✅ Kural motoru aktif</li>
        </ul>
    </div>
</div>
<?php endif; ?>
<?php if ($result): ?>
    <hr class="my-4">
    
    <!-- DEBUG BILGISI EKLE -->
    <div class="alert alert-info">
        <h6>🔍 Öğrenci Analizi:</h6>
        <?php 
        require_once __DIR__ . '/../ruleengine.php';
        $debugEngine = new RuleEngine();
        $context = $debugEngine->getStudentContext($studentId);
        ?>
        <ul class="mb-0">
            <li><strong>Öğrenci:</strong> <?= h($context['student']['name'] ?? 'Bulunamadı') ?></li>
            <li><strong>Sınıf Bilgisi:</strong> <?= h($context['student']['sinif'] ?? 'Yok') ?></li>
            <li><strong>Class Name:</strong> <?= h($context['student']['class_name'] ?? 'Yok') ?></li>
            <li><strong>Tespit Edilen Kademe:</strong> 
                <?php if ($context['category']): ?>
                    <span class="badge bg-success"><?= h($context['category']) ?></span>
                <?php else: ?>
                    <span class="badge bg-danger">Belirlenemedi</span>
                <?php endif; ?>
            </li>
            <li><strong>Sınıf Seviyesi:</strong> 
                <?= $context['grade'] ? $context['grade'] . '. Sınıf' : 'Belirlenemedi' ?>
            </li>
        </ul>
    </div>
    
    <h5 class="mb-3">📊 Test Sonucu:</h5>
    
    <?php if ($result['can_enroll']): ?>
        <div class="alert alert-success">
            <h5 class="alert-heading">
                <i class="fa fa-check-circle"></i> Başarılı!
            </h5>
            <p class="mb-0">
                Öğrenci bu derse kayıt olabilir. Kural ihlali tespit edilmedi.
            </p>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <h5 class="alert-heading">
                <i class="fa fa-times-circle"></i> Kayıt Yapılamaz
            </h5>
            <p><strong>Tespit edilen ihlaller:</strong></p>
            <ul class="mb-0">
                <?php foreach ($result['violations'] as $violation): ?>
                    <li><?= h($violation) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($result['warnings'])): ?>
        <div class="alert alert-warning">
            <h6 class="alert-heading">
                <i class="fa fa-exclamation-triangle"></i> Uyarılar
            </h6>
            <ul class="mb-0">
                <?php foreach ($result['warnings'] as $warning): ?>
                    <li><?= h($warning) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
<?php endif; ?>