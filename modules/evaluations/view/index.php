<?php
if (!function_exists('h')) {
    function h($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$evaluations = $evaluations ?? [];
?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Değerlendirme Yönetimi</h5>
        <a href="index.php?module=evaluations&action=create" class="btn btn-primary">
            <i class="fa fa-plus"></i> Yeni Değerlendirme Ekle
        </a>
    </div>
    
    <div class="card-body">
        <p class="text-muted mb-3">
            Oluşturulmuş sınavları, denemeleri veya diğer değerlendirmeleri yönetin.
        </p>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fa fa-check-circle"></i> Değerlendirme başarıyla oluşturuldu!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Değerlendirme Adı</th>
                        <th style="width: 130px;">Türü</th>
                        <th style="width: 150px;">Tarih & Saat</th>
                        <th style="width: 150px;">Sınıf</th>
                        <th style="width: 100px;">Durum</th>
                        <th style="width: 250px;" class="text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($evaluations)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fa fa-inbox fa-3x mb-3"></i>
                                <p>Henüz oluşturulmuş bir değerlendirme bulunmamaktadır.</p>
                                <a href="index.php?module=evaluations&action=create" class="btn btn-primary btn-sm">
                                    İlk Değerlendirmeyi Oluştur
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($evaluations as $evaluation): 
                            $status = $evaluation['status'] ?? 'draft';
                            $statusColors = [
                                'draft' => 'secondary',
                                'active' => 'success',
                                'completed' => 'info',
                                'cancelled' => 'danger'
                            ];
                            $statusTexts = [
                                'draft' => 'Taslak',
                                'active' => 'Aktif',
                                'completed' => 'Tamamlandı',
                                'cancelled' => 'İptal'
                            ];
                            $statusColor = $statusColors[$status] ?? 'secondary';
                            $statusText = $statusTexts[$status] ?? ucfirst($status);
                            
                            // Tarih formatla
                            $dateTime = '';
                            if (!empty($evaluation['exam_date'])) {
                                $dateTime = date('d.m.Y', strtotime($evaluation['exam_date']));
                                if (!empty($evaluation['exam_time'])) {
                                    $dateTime .= ' ' . date('H:i', strtotime($evaluation['exam_time']));
                                }
                            }
                        ?>
                            <tr>
                                <td><?= (int)$evaluation['id'] ?></td>
                                <td>
                                    <strong><?= h($evaluation['name'] ?? '') ?></strong>
                                    <?php if (!empty($evaluation['description'])): ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= h(mb_substr($evaluation['description'], 0, 60)) ?>...
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?= h($evaluation['evaluation_type'] ?? '—') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($dateTime): ?>
                                        <i class="fa fa-calendar"></i> <?= $dateTime ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($evaluation['class_name'])): ?>
                                        <span class="badge bg-info">
                                            <?= h($evaluation['class_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $statusColor ?>">
                                        <?= h($statusText) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="index.php?module=evaluations&action=results&id=<?= (int)$evaluation['id'] ?>" 
                                           class="btn btn-sm btn-success" 
                                           title="Sonuçları Gir">
                                            <i class="fa fa-edit"></i> Sonuçlar
                                        </a>
                                        <a href="index.php?module=evaluations&action=edit&id=<?= (int)$evaluation['id'] ?>" 
                                           class="btn btn-sm btn-warning" 
                                           title="Düzenle">
                                            <i class="fa fa-pencil"></i> Düzenle
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($evaluations)): ?>
        <div class="card-footer bg-light">
            <div class="text-muted small">
                Toplam <strong><?= count($evaluations) ?></strong> değerlendirme
            </div>
        </div>
    <?php endif; ?>
</div>