<?php
if (!function_functions('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$requests = $requests ?? [];
$userRole = $userRole ?? 'student';

// Status etiketleri
$statusLabels = [
    'pending' => ['text' => 'Beklemede', 'color' => 'warning'],
    'approved' => ['text' => 'Onaylandı', 'color' => 'success'],
    'rejected' => ['text' => 'Reddedildi', 'color' => 'danger'],
    'completed' => ['text' => 'Tamamlandı', 'color' => 'info'],
    'cancelled' => ['text' => 'İptal Edildi', 'color' => 'secondary']
];

// Success mesaj
if (isset($_GET['success_message'])):
    $messages = [
        'request_submitted' => 'Randevu talebiniz başarıyla gönderildi. En kısa sürede değerlendirilecektir.',
        'request_cancelled' => 'Randevu talebiniz iptal edildi.'
    ];
    ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= h($messages[$_GET['success_message']] ?? $_GET['success_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="mb-3">
    <a href="index.php?module=guidance&action=requestForm" class="btn btn-primary">
        <i class="fa fa-plus"></i> Yeni Randevu Talebi
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Randevu Taleplerim</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($requests)): ?>
            <div class="p-4 text-center text-muted">
                Henüz randevu talebiniz bulunmamaktadır.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Öğrenci</th>
                            <th>Talep Tarihi</th>
                            <th>Talep Saati</th>
                            <th>Durum</th>
                            <th>Onaylanan Tarih</th>
                            <th>Rehber</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $req): 
                            $status = $statusLabels[$req['status']] ?? ['text' => $req['status'], 'color' => 'secondary'];
                        ?>
                            <tr>
                                <td><?= h($req['student_name']) ?></td>
                                <td><?= date('d.m.Y', strtotime($req['requested_date'])) ?></td>
                                <td><?= date('H:i', strtotime($req['requested_time'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $status['color'] ?>">
                                        <?= h($status['text']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($req['appointment_date'])): ?>
                                        <?= date('d.m.Y', strtotime($req['appointment_date'])) ?>
                                        <?= date('H:i', strtotime($req['appointment_time'])) ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td><?= h($req['counselor_name'] ?? '—') ?></td>
                                <td>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detailModal<?= (int)$req['id'] ?>">
                                        Detay
                                    </button>
                                    
                                    <?php if ($req['status'] === 'pending'): ?>
                                        <a href="index.php?module=guidance&action=cancelRequest&id=<?= (int)$req['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Randevu talebini iptal etmek istediğinize emin misiniz?')">
                                            İptal
                                        </a>
                                    <?php endif; ?>

                                    <!-- Detay Modal -->
                                    <div class="modal fade" id="detailModal<?= (int)$req['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Randevu Detayı</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Öğrenci:</strong> <?= h($req['student_name']) ?></p>
                                                    <p><strong>Talep Tarihi:</strong> <?= date('d.m.Y H:i', strtotime($req['requested_date'] . ' ' . $req['requested_time'])) ?></p>
                                                    <p><strong>Durum:</strong> 
                                                        <span class="badge bg-<?= $status['color'] ?>">
                                                            <?= h($status['text']) ?>
                                                        </span>
                                                    </p>
                                                    
                                                    <?php if (!empty($req['appointment_date'])): ?>
                                                        <hr>
                                                        <p><strong>Onaylanan Randevu:</strong></p>
                                                        <p><?= date('d.m.Y', strtotime($req['appointment_date'])) ?> - <?= date('H:i', strtotime($req['appointment_time'])) ?></p>
                                                    <?php endif; ?>

                                                    <hr>
                                                    <p><strong>Konu/Sebep:</strong></p>
                                                    <p><?= nl2br(h($req['reason'])) ?></p>

                                                    <?php if (!empty($req['counselor_notes'])): ?>
                                                        <hr>
                                                        <p><strong>Rehber Notları:</strong></p>
                                                        <p><?= nl2br(h($req['counselor_notes'])) ?></p>
                                                    <?php endif; ?>
                                                </div>
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