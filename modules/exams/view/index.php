<h2>Sınav Tanımlamaları</h2>

<!-- Butonlar AdminLTE stiliyle güncellendi -->
<a href="index.php?module=exams&action=create" class="btn btn-primary mb-3">
    <i class="fa fa-plus"></i> Yeni Sınav Tanımla
</a>
<a href="index.php?module=exams&action=calendar" class="btn btn-info mb-3">
    <i class="fa fa-calendar"></i> Sınav Takvimi
</a>

<!-- Bildirim Mesajları -->
<?php if (isset($_GET['status_message'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_GET['status_message']) ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error_message'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_GET['error_message']) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tüm Sınavlar</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Sınav Adı</th>
                    <th>Tarihi</th>
                    <th>Durumu</th>
                    <th>İlişkili Ders/Sınıf</th>
                    <th style="width: 20%;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($exams)): ?>
                    <tr><td colspan="5" class="text-center p-4">Kayıtlı sınav bulunamadı.</td></tr>
                <?php else: ?>
                    <?php foreach ($exams as $exam): ?>
                        <tr>
                            <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                            <td data-label="Sınav Adı">
                                <strong><?= htmlspecialchars($exam['name']) ?></strong><br>
                                <small class="text-muted">Oluşturan: <?= htmlspecialchars($exam['creator_name']) ?></small>
                            </td>
                            <td data-label="Tarih"><?= htmlspecialchars($exam['exam_date'] ? date('d.m.Y', strtotime($exam['exam_date'])) : 'Belirtilmemiş') ?></td>
                            <td data-label="Durum">
                                <?php
                                    $status = $exam['status'] ?? 'bilinmiyor';
                                    $status_map = [
                                        'active' => ['class' => 'badge-primary', 'text' => 'Aktif'],
                                        'completed' => ['class' => 'badge-success', 'text' => 'Tamamlandı'],
                                        'cancelled' => ['class' => 'badge-danger', 'text' => 'İptal Edildi'],
                                        'planned' => ['class' => 'badge-info', 'text' => 'Planlandı']
                                    ];
                                    $s = $status_map[$status] ?? ['class' => 'badge-secondary', 'text' => ucfirst($status)];
                                ?>
                                <span class="badge <?= $s['class'] ?>"><?= $s['text'] ?></span>
                            </td>
                            <td data-label="Ders/Sınıf">
                                <?= htmlspecialchars($exam['course_name'] ?? 'Genel') ?><br>
                                <small class="text-muted"><?= htmlspecialchars($exam['class_name'] ?? 'Tüm Sınıflar') ?></small>
                            </td>
                            <td data-label="İşlemler">
                                <?php if(in_array($userRole, ['admin', 'teacher']) && in_array($exam['status'], ['active', 'completed'])): ?>
                                    <a href="index.php?module=exams&action=results&exam_id=<?= $exam['id'] ?>" class="btn btn-sm btn-info" title="Sonuç Gir/Gör">
                                        <i class="fa fa-bar-chart"></i> Sonuçlar
                                    </a>
                                    <a href="index.php?module=exams&action=attendance_entry&exam_id=<?= $exam['id'] ?>" class="btn btn-sm btn-secondary" title="Yoklama Gir">
                                        <i class="fa fa-check-square-o"></i> Yoklama
                                    </a>
                                <?php endif; ?>
                                <?php if($userRole === 'admin'): ?>
                                    <a href="index.php?module=exams&action=edit&id=<?= $exam['id'] ?>" class="btn btn-sm btn-warning" title="Sınavı Düzenle">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <a href="index.php?module=exams&action=delete&id=<?= $exam['id'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Bu sınavı silmek istediğinize emin misiniz? Bu sınava ait girilmiş tüm sonuçlar da silinecektir!')" 
                                       title="Sınavı Sil">
                                       <i class="fa fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
