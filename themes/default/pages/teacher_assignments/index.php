<h2>Verilen Ödevler</h2>
<p class="lead">Öğrencilere verdiğiniz ödevlerin listesi ve detayları.</p>

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
        <h3 class="card-title">Tüm Verilen Ödevler</h3>
        <div class="card-tools">
            <a href="index.php?module=assignments&action=create" class="btn btn-tool btn-sm">
                <i class="fa fa-plus"></i> Yeni Ödev Ekle
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Ders</th>
                    <th>Başlık</th>
                    <th style="width: 40%;">Açıklama</th>
                    <th>Teslim Tarihi</th>
                    <!-- Öğretmen görünümü olduğu için işlem sütunu eklenebilir -->
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assignments)): ?>
                    <tr>
                        <td colspan="5" class="text-center p-4">Henüz oluşturulmuş bir ödev bulunmamaktadır.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assignments as $a): ?>
                        <tr>
                            <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                            <td data-label="Ders">
                                <!-- Bu bilgi için controller'da ders adının da çekilmesi gerekir -->
                                <?= htmlspecialchars($a['course_name'] ?? 'Ders #' . $a['course_id']) ?>
                            </td>
                            <td data-label="Başlık"><strong><?= htmlspecialchars($a['title']) ?></strong></td>
                            <td data-label="Açıklama"><?= nl2br(htmlspecialchars($a['description'])) ?></td>
                            <td data-label="Teslim Tarihi">
                                <span class="badge badge-danger"><?= htmlspecialchars(date('d.m.Y', strtotime($a['due_date']))) ?></span>
                            </td>
                            <td data-label="İşlemler">
                                 <a href="index.php?module=assignments&action=view_submissions&id=<?= htmlspecialchars($a['id']) ?>" class="btn btn-sm btn-info">
                                    <i class="fa fa-users"></i> Teslimler
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
