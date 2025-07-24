<h2>Not Listesi</h2>

<!-- Buton AdminLTE stiliyle güncellendi -->
<a href="index.php?module=grades&action=create" class="btn btn-primary mb-3">
    <i class="fa fa-plus"></i> Yeni Not Ekle
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
        <h3 class="card-title">Tüm Notlar</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Öğrenci</th>
                    <th>Ders</th>
                    <th>Not</th>
                    <th>Not Tarihi</th>
                    <th style="width: 30%;">Açıklama</th>
                    <th style="width: 15%;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($grades)): ?>
                    <?php foreach ($grades as $g): ?>
                    <tr>
                        <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                        <td data-label="Öğrenci"><strong><?= htmlspecialchars($g['student_name']) ?></strong></td>
                        <td data-label="Ders"><?= htmlspecialchars($g['course_name']) ?></td>
                        <td data-label="Not">
                            <span class="badge badge-primary" style="font-size: 1.1em;"><?= htmlspecialchars($g['grade']) ?></span>
                        </td>
                        <td data-label="Not Tarihi"><?= htmlspecialchars(date('d.m.Y', strtotime($g['grade_date']))) ?></td>
                        <td data-label="Açıklama"><?= htmlspecialchars($g['comments']) ?></td>
                        <td data-label="İşlemler">
                            <a href="index.php?module=grades&action=edit&id=<?= htmlspecialchars($g['id']) ?>" class="btn btn-sm btn-warning">
                                <i class="fa fa-pencil"></i>
                            </a>
                            <a href="index.php?module=grades&action=delete&id=<?= htmlspecialchars($g['id']) ?>" class="btn btn-sm btn-danger ml-1" onclick="return confirm('Bu notu silmek istediğinize emin misiniz?')">
                                <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center p-4">Kayıtlı not bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
