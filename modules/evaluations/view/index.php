<h2>Değerlendirme Yönetimi</h2>
<p class="lead">Oluşturulmuş sınavları, denemeleri veya diğer değerlendirmeleri yönetin.</p>

<!-- Buton AdminLTE stiliyle güncellendi -->
<a href="index.php?module=evaluations&action=create" class="btn btn-primary mb-3">
    <i class="fa fa-plus"></i> Yeni Değerlendirme Ekle
</a>

<!-- Başarı veya Hata Mesajları için Alan -->
<?php if (!empty($_GET['status_message'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_GET['status_message']) ?>
    </div>
<?php endif; ?>
<?php if (!empty($_GET['error_message'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_GET['error_message']) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tüm Değerlendirmeler</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th style="width: 10%;">ID</th>
                    <th>Değerlendirme Adı</th>
                    <th>Türü</th>
                    <th>Tarih</th>
                    <th>Durum</th>
                    <th style="width: 25%;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($evaluations)): ?>
                    <tr>
                        <td colspan="6" class="text-center p-4">Henüz oluşturulmuş bir değerlendirme bulunmamaktadır.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($evaluations as $evaluation): ?>
                        <tr>
                            <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                            <td data-label="ID"><?= htmlspecialchars($evaluation['id']) ?></td>
                            <td data-label="Değerlendirme Adı"><strong><?= htmlspecialchars($evaluation['name'] ?? '') ?></strong></td>
                            <td data-label="Türü"><?= htmlspecialchars($evaluation['evaluation_type'] ?? '') ?></td>
                            <td data-label="Tarih"><?= htmlspecialchars($evaluation['exam_date'] ?? '') ?></td>
                            <td data-label="Durum">
                                <?php
                                    $status = $evaluation['status'] ?? 'bilinmiyor';
                                    $status_class = 'badge-secondary';
                                    if ($status === 'tamamlandı') $status_class = 'badge-success';
                                    if ($status === 'planlandı') $status_class = 'badge-info';
                                    if ($status === 'iptal') $status_class = 'badge-danger';
                                ?>
                                <span class="badge <?= $status_class ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
                            </td>
                            <td data-label="İşlemler">
                                <a href="index.php?module=evaluations&action=edit&id=<?= htmlspecialchars($evaluation['id']) ?>" class="btn btn-sm btn-warning">
                                    <i class="fa fa-pencil"></i> Düzenle
                                </a>
                                <a href="index.php?module=evaluations&action=results&id=<?= htmlspecialchars($evaluation['id']) ?>" class="btn btn-sm btn-primary ml-1">
                                    <i class="fa fa-bar-chart"></i> Sonuçlar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
