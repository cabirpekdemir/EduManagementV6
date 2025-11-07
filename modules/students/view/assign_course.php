<?php
// modules/students/view/assign_course.php
$student = $student ?? [];
$allCourses = $allCourses ?? [];
$studentCourses = $studentCourses ?? [];
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Ders Kaydı: <?= htmlspecialchars($student['name'] ?? '') ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="index.php?module=dashboard">Ana Sayfa</a></li>
                    <li class="breadcrumb-item"><a href="index.php?module=students&action=list">Öğrenciler</a></li>
                    <li class="breadcrumb-item active">Ders Kaydı</li>
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
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['flash_error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
        
        <div class="row">
            <!-- Öğrenci Bilgisi -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <img src="<?= htmlspecialchars($student['profile_photo'] ?? 'assets/img/default-avatar.png') ?>" 
                             class="rounded-circle mb-2" 
                             style="width:100px;height:100px;object-fit:cover;"
                             alt="<?= htmlspecialchars($student['name']) ?>">
                        <h5><?= htmlspecialchars($student['name']) ?></h5>
                        <?php if ($student['student_number']): ?>
                            <p class="text-muted mb-0">No: <?= htmlspecialchars($student['student_number']) ?></p>
                        <?php endif; ?>
                        <?php if ($student['class_name']): ?>
                            <span class="badge bg-primary mt-2">
                                <?= htmlspecialchars($student['class_name']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Ders Ekleme -->
            <div class="col-md-8">
                <!-- Yeni Ders Ekle -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fa fa-plus"></i> Yeni Ders Ekle</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="index.php?module=students&action=save_course_assignment">
                            <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                            <div class="row">
                                <div class="col-md-9">
                                    <select class="form-select" name="course_id" required>
                                        <option value="">Ders Seçiniz...</option>
                                        <?php foreach ($allCourses as $course): ?>
                                            <option value="<?= $course['id'] ?>">
                                                <?= htmlspecialchars($course['name']) ?>
                                                <?php if ($course['teacher_name']): ?>
                                                    - (<?= htmlspecialchars($course['teacher_name']) ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fa fa-plus"></i> Ekle
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Mevcut Dersler -->
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fa fa-book"></i> Kayıtlı Dersler
                            <span class="badge bg-light text-dark"><?= count($studentCourses) ?></span>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($studentCourses)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fa fa-book fa-3x mb-2"></i>
                                <p>Henüz ders kaydı yapılmamış.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ders Adı</th>
                                            <th>Öğretmen</th>
                                            <th>Kademe</th>
                                            <th class="text-end">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($studentCourses as $course): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($course['name']) ?></strong>
                                                </td>
                                                <td><?= htmlspecialchars($course['teacher_name'] ?? '—') ?></td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?= ucfirst($course['category'] ?? 'Diğer') ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="index.php?module=students&action=remove_course_assignment&student_id=<?= $student['id'] ?>&course_id=<?= $course['id'] ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Bu dersi kaldırmak istediğinizden emin misiniz?')">
                                                        <i class="fa fa-trash"></i> Kaldır
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
                
                <!-- Geri Dön -->
                <div class="mt-3">
                    <a href="index.php?module=students&action=view&id=<?= $student['id'] ?>" 
                       class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Geri Dön
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>