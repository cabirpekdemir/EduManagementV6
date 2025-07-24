<h2>Notlar Listesi</h2>

<!-- Buton AdminLTE stiliyle güncellendi -->
<a href="index.php?module=notes&action=add" class="btn btn-primary mb-3">
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
                    <th>Kurs</th>
                    <th style="width: 40%;">Not İçeriği</th>
                    <th>Tarih</th>
                    <th style="width: 15%;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($notes)): ?>
                    <tr>
                        <td colspan="5" class="text-center p-4">Henüz eklenmiş bir not bulunmamaktadır.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($notes as $note): ?>
                        <tr>
                            <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                            <td data-label="Öğrenci">
                                <strong><?= htmlspecialchars($note['student_name']) ?></strong>
                            </td>
                            <td data-label="Kurs"><?= htmlspecialchars($note['course_name']) ?></td>
                            <td data-label="İçerik"><?= nl2br(htmlspecialchars($note['content'])) ?></td>
                            <td data-label="Tarih"><?= htmlspecialchars(date('d.m.Y H:i', strtotime($note['created_at']))) ?></td>
                            <td data-label="İşlemler">
                                <a href="index.php?module=notes&action=edit&id=<?= htmlspecialchars($note['id']) ?>" class="btn btn-sm btn-warning">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="index.php?module=notes&action=delete&id=<?= htmlspecialchars($note['id']) ?>" class="btn btn-sm btn-danger ml-1" onclick="return confirm('Bu notu silmek istediğinizden emin misiniz?')">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
