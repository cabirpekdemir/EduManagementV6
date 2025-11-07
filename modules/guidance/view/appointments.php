<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$appointments = $appointments ?? [];
$stats = $stats ?? [];
$filterStatus = $filterStatus ?? '';
$filterDate = $filterDate ?? '';

// Status etiketleri
$statusLabels = [
    'pending' => ['text' => 'Beklemede', 'color' => 'warning', 'icon' => '⏳'],
    'approved' => ['text' => 'Onaylandı', 'color' => 'success', 'icon' => '✅'],
    'rejected' => ['text' => 'Reddedildi', 'color' => 'danger', 'icon' => '❌'],
    'completed' => ['text' => 'Tamamlandı', 'color' => 'info', 'icon' => '✔️'],
    'cancelled' => ['text' => 'İptal Edildi', 'color' => 'secondary', 'icon' => '🚫']
];

// Success/Error mesajları
if (isset($_GET['success_message'])):
    $messages = [
        'appointment_updated' => 'Randevu başarıyla güncellendi.'
    ];
    ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= h($messages[$_GET['success_message']] ?? $_GET['success_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif;

if (isset($_GET['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= h($_GET['error_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- İstatistikler -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <h3 class="text-primary mb-0"><?= (int)$stats['total'] ?></h3>
                <small class="text-muted">Toplam Talep</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h3 class="text-warning mb-0"><?= (int)$stats['pending'] ?></h3>
                <small class="text-muted">Bekleyen</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <h3 class="text-success mb-0"><?= (int)$stats['approved'] ?></h3>
                <small class="text-muted">Onaylanan</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <h3 class="text-info mb-0"><?= (int)$stats['completed'] ?></h3>
                <small class="text-muted">Tamamlanan</small>
            </div>
        </div>
    </div>
</div>

<!-- Filtreler -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="mb-0">🔍 Filtreler</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="index.php">
            <input type="hidden" name="module" value="guidance">
            <input type="hidden" name="action" value="appointments">
            
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Durum</label>
                    <select name="filter_status" class="form-select">
                        <option value="">Tümü</option>
                        <?php foreach ($statusLabels as $key => $label): ?>
                            <option value="<?= h($key) ?>" <?= $filterStatus === $key ? 'selected' : '' ?>>
                                <?= h($label['text']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Talep Tarihi</label>
                    <input type="date" name="filter_date" class="form-control" value="<?= h($filterDate) ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fa fa-filter"></i> Filtrele
                    </button>
                    <a href="index.php?module=guidance&action=appointments" class="btn btn-outline-secondary">
                        Temizle
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Randevu Listesi -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">📅 Randevu Talepleri</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($appointments)): ?>
            <div class="p-4 text-center text-muted">
                Randevu talebi bulunamadı.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Öğrenci</th>
                            <th>Talep Eden</th>
                            <th>İletişim</th>
                            <th>Talep Tarihi/Saat</th>
                            <th style="width:150px">Durum</th>
                            <th>Onaylanan Randevu</th>
                            <th class="text-end" style="width:200px">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $apt): 
                            $status = $statusLabels[$apt['status']] ?? ['text' => $apt['status'], 'color' => 'secondary', 'icon' => ''];
                        ?>
                            <tr>
                                <td><?= (int)$apt['id'] ?></td>
                                <td>
                                    <div class="fw-semibold"><?= h($apt['student_name']) ?></div>
                                    <?php if (!empty($apt['student_number'])): ?>
                                        <small class="text-muted">No: <?= h($apt['student_number']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($apt['parent_name'])): ?>
                                        <small class="text-muted">Veli: <?= h($apt['parent_name']) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">Öğrenci</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($apt['phone'])): ?>
                                        <small><?= h($apt['phone']) ?></small>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?= date('d.m.Y', strtotime($apt['requested_date'])) ?></div>
                                    <small class="text-muted"><?= date('H:i', strtotime($apt['requested_time'])) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $status['color'] ?>">
                                        <?= $status['icon'] ?> <?= h($status['text']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($apt['appointment_date'])): ?>
                                        <div><?= date('d.m.Y', strtotime($apt['appointment_date'])) ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($apt['appointment_time'])) ?></small>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewModal<?= (int)$apt['id'] ?>">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    
                                    <?php if ($apt['status'] === 'pending' || $apt['status'] === 'approved'): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal<?= (int)$apt['id'] ?>">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    <?php endif; ?>

                                    <!-- Görüntüleme Modal -->
                                    <div class="modal fade" id="viewModal<?= (int)$apt['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Randevu Detayı</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <strong>Öğrenci:</strong><br>
                                                            <?= h($apt['student_name']) ?>
                                                            <?php if (!empty($apt['student_number'])): ?>
                                                                <br><small class="text-muted">No: <?= h($apt['student_number']) ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Talep Eden:</strong><br>
                                                            <?= !empty($apt['parent_name']) ? h($apt['parent_name']) . ' (Veli)' : 'Öğrenci' ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>İletişim:</strong><br>
                                                            <?= h($apt['phone'] ?? '—') ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Durum:</strong><br>
                                                            <span class="badge bg-<?= $status['color'] ?>">
                                                                <?= $status['icon'] ?> <?= h($status['text']) ?>
                                                            </span>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Talep Edilen:</strong><br>
                                                            <?= date('d.m.Y H:i', strtotime($apt['requested_date'] . ' ' . $apt['requested_time'])) ?>
                                                        </div>
                                                        <?php if (!empty($apt['appointment_date'])): ?>
                                                            <div class="col-md-6">
                                                                <strong>Onaylanan Randevu:</strong><br>
                                                                <?= date('d.m.Y H:i', strtotime($apt['appointment_date'] . ' ' . $apt['appointment_time'])) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="col-12">
                                                            <hr>
                                                            <strong>Randevu Sebebi / Konu:</strong><br>
                                                            <div class="bg-light p-3 rounded mt-2">
                                                                <?= nl2br(h($apt['reason'])) ?>
                                                            </div>
                                                        </div>
                                                        <?php if (!empty($apt['counselor_notes'])): ?>
                                                            <div class="col-12">
                                                                <hr>
                                                                <strong>Rehber Notları:</strong><br>
                                                                <div class="bg-light p-3 rounded mt-2">
                                                                    <?= nl2br(h($apt['counselor_notes'])) ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($apt['counselor_name'])): ?>
                                                            <div class="col-12">
                                                                <small class="text-muted">
                                                                    İşlem Yapan: <?= h($apt['counselor_name']) ?>
                                                                </small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Düzenleme Modal -->
                                    <div class="modal fade" id="editModal<?= (int)$apt['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form method="POST" action="index.php?module=guidance&action=updateAppointment">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Randevuyu Güncelle</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="appointment_id" value="<?= (int)$apt['id'] ?>">

                                                        <div class="alert alert-info">
                                                            <strong>Öğrenci:</strong> <?= h($apt['student_name']) ?><br>
                                                            <strong>Talep:</strong> <?= date('d.m.Y H:i', strtotime($apt['requested_date'] . ' ' . $apt['requested_time'])) ?>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Durum <span class="text-danger">*</span></label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="approved" <?= $apt['status'] === 'approved' ? 'selected' : '' ?>>
                                                                    ✅ Onayla
                                                                </option>
                                                                <option value="rejected" <?= $apt['status'] === 'rejected' ? 'selected' : '' ?>>
                                                                    ❌ Reddet
                                                                </option>
                                                                <option value="completed" <?= $apt['status'] === 'completed' ? 'selected' : '' ?>>
                                                                    ✔️ Tamamlandı
                                                                </option>
                                                            </select>
                                                        </div>

                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Randevu Tarihi</label>
                                                                <input type="date" 
                                                                       name="appointment_date" 
                                                                       class="form-control" 
                                                                       value="<?= h($apt['appointment_date'] ?? $apt['requested_date']) ?>">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Randevu Saati</label>
                                                                <input type="time" 
                                                                       name="appointment_time" 
                                                                       class="form-control" 
                                                                       value="<?= h($apt['appointment_time'] ?? $apt['requested_time']) ?>">
                                                            </div>
                                                        </div>

                                                        <div class="mb-3 mt-3">
                                                            <label class="form-label">Rehber Notları</label>
                                                            <textarea name="counselor_notes" 
                                                                      class="form-control" 
                                                                      rows="4"
                                                                      placeholder="Randevu hakkında notlarınızı yazın..."><?= h($apt['counselor_notes'] ?? '') ?></textarea>
                                                        </div>

                                                        <div class="alert alert-secondary">
                                                            <strong>Randevu Sebebi:</strong><br>
                                                            <?= nl2br(h($apt['reason'])) ?>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fa fa-save"></i> Kaydet
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>