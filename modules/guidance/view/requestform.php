<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$studentId = $studentId ?? null;
$studentName = $studentName ?? null;
$children = $children ?? [];
$students = $students ?? [];
$isParent = $isParent ?? false;
$isStaff = $isStaff ?? false;

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

if (isset($_GET['error_message'])):
    $errors = [
        'empty_fields' => 'Lütfen tüm alanları doldurun.',
        'invalid_date' => 'Geçersiz tarih formatı.',
        'past_date' => 'Geçmiş bir tarih seçemezsiniz.',
        'db_error' => 'Randevu talebi kaydedilirken bir hata oluştu.'
    ];
    ?>
    <div class="alert alert-danger">
        <?= h($errors[$_GET['error_message']] ?? $_GET['error_message']) ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fa fa-calendar-plus"></i> Rehberlik Randevu Talebi
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>📌 Bilgilendirme:</strong><br>
                    Randevu talebiniz oluşturulduktan sonra rehber öğretmenimiz en kısa sürede değerlendirecek ve size geri dönüş yapacaktır.
                </div>

                <form method="POST" action="index.php?module=guidance&action=submitRequest">
                    
                    <!-- Öğrenci Seçimi -->
                    <?php if ($isParent): ?>
                        <div class="mb-3">
                            <label class="form-label">Öğrenci <span class="text-danger">*</span></label>
                            <select name="student_id" class="form-select" required>
                                <option value="">— Seçiniz —</option>
                                <?php foreach ($children as $child): ?>
                                    <option value="<?= (int)$child['id'] ?>">
                                        <?= h($child['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php elseif ($isStaff): ?>
                        <div class="mb-3">
                            <label class="form-label">Öğrenci <span class="text-danger">*</span></label>
                            <select name="student_id" class="form-select" required>
                                <option value="">— Seçiniz —</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= (int)$student['id'] ?>">
                                        <?= h($student['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="student_id" value="<?= (int)$studentId ?>">
                        <div class="mb-3">
                            <label class="form-label">Öğrenci</label>
                            <input type="text" class="form-control" value="<?= h($studentName) ?>" readonly>
                        </div>
                    <?php endif; ?>

                    <!-- Talep Edilen Tarih -->
                    <div class="mb-3">
                        <label class="form-label">Talep Edilen Tarih <span class="text-danger">*</span></label>
                        <input type="date" 
                               name="requested_date" 
                               class="form-control" 
                               min="<?= date('Y-m-d') ?>" 
                               required>
                        <small class="text-muted">Randevu tarihi rehber öğretmen tarafından değiştirilebilir.</small>
                    </div>

                    <!-- Talep Edilen Saat -->
                    <div class="mb-3">
                        <label class="form-label">Talep Edilen Saat <span class="text-danger">*</span></label>
                        <select name="requested_time" class="form-select" required>
                            <option value="">— Seçiniz —</option>
                            <option value="09:00">09:00</option>
                            <option value="09:30">09:30</option>
                            <option value="10:00">10:00</option>
                            <option value="10:30">10:30</option>
                            <option value="11:00">11:00</option>
                            <option value="11:30">11:30</option>
                            <option value="12:00">12:00</option>
                            <option value="13:00">13:00</option>
                            <option value="13:30">13:30</option>
                            <option value="14:00">14:00</option>
                            <option value="14:30">14:30</option>
                            <option value="15:00">15:00</option>
                            <option value="15:30">15:30</option>
                            <option value="16:00">16:00</option>
                        </select>
                        <small class="text-muted">Randevu saati rehber öğretmen tarafından değiştirilebilir.</small>
                    </div>

                    <!-- Randevu Sebebi -->
                    <div class="mb-3">
                        <label class="form-label">Randevu Sebebi / Konu <span class="text-danger">*</span></label>
                        <textarea name="reason" 
                                  class="form-control" 
                                  rows="5" 
                                  required
                                  placeholder="Lütfen randevu almak istediğiniz konuyu kısaca açıklayın..."></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-paper-plane"></i> Talep Gönder
                        </button>
                        <a href="index.php?module=guidance&action=myRequests" class="btn btn-outline-secondary">
                            Taleplerim
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>