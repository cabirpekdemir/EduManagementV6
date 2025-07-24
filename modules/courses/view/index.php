<h2>Kurs Listesi</h2>

<!-- Buton AdminLTE stiliyle güncellendi -->
<a href="index.php?module=courses&action=create" class="btn btn-primary mb-3">
    <i class="fa fa-plus"></i> Yeni Kurs Ekle
</a>

<!-- Başarı veya Hata Mesajları için Alan -->
<?php if (isset($status_message)): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($status_message) ?>
    </div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($error_message) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tüm Kurslar</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Kurs Adı</th>
                    <th>Öğretmen</th>
                    <th>Atanan Sınıflar</th>
                    <th>Ders Zamanları</th>
                    <th style="width: 15%;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($courses)): ?>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                            <td data-label="Kurs Adı">
                                <strong><?= htmlspecialchars($course['name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($course['classroom'] ?? 'Konum Belirtilmemiş') ?></small>
                            </td>
                            <td data-label="Öğretmen"><?= htmlspecialchars($course['teacher_name'] ?? 'Atanmamış') ?></td>
                            <td data-label="Atanan Sınıflar">
                                <?php if (!empty($course['classes'])): ?>
                                    <?php foreach($course['classes'] as $class): ?>
                                        <span class="badge badge-secondary mr-1"><?= htmlspecialchars($class['name']) ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td data-label="Ders Zamanları">
                                <?php if (!empty($course['times'])): ?>
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach($course['times'] as $time): ?>
                                            <li><small><?= htmlspecialchars($time['day']) ?>: <?= htmlspecialchars(date('H:i', strtotime($time['start_time']))) ?> - <?= htmlspecialchars(date('H:i', strtotime($time['end_time']))) ?></small></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <span class="text-muted">Zaman belirtilmemiş</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="İşlemler">
                                <a href="index.php?module=courses&action=edit&id=<?= htmlspecialchars($course['id']) ?>" class="btn btn-sm btn-warning">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="index.php?module=courses&action=delete&id=<?= htmlspecialchars($course['id']) ?>" class="btn btn-sm btn-danger ml-1" onclick="return confirm('Bu kursu silmek istediğinize emin misiniz?')">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center p-4">Kayıtlı kurs bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
