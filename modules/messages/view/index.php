<h2>Mesajlarım</h2>

<!-- Buton AdminLTE stiliyle güncellendi -->
<a href="index.php?module=messages&action=create" class="btn btn-primary mb-3">
    <i class="fa fa-pencil-square-o"></i> Yeni Mesaj Gönder
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

<div class="row">
    <!-- Gelen Mesajlar Kartı -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-inbox"></i> Gelen Mesajlar</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Kimden</th>
                            <th>Konu</th>
                            <th>Tarih</th>
                            <th style="width: 100px;">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($incomingMessages)): ?>
                            <tr><td colspan="4" class="text-center p-4">Gelen kutunuz boş.</td></tr>
                        <?php else: ?>
                            <?php foreach ($incomingMessages as $m): ?>
                                <tr>
                                    <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                                    <td data-label="Kimden"><strong><?= htmlspecialchars($m['sender_name']) ?></strong></td>
                                    <td data-label="Konu"><?= htmlspecialchars($m['subject']) ?></td>
                                    <td data-label="Tarih"><small><?= htmlspecialchars(date('d.m.Y H:i', strtotime($m['created_at']))) ?></small></td>
                                    <td data-label="İşlem">
                                        <a href="index.php?module=messages&action=view&id=<?= htmlspecialchars($m['id']) ?>" class="btn btn-sm btn-info">
                                            <i class="fa fa-eye"></i> Oku
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Gönderilen Mesajlar Kartı -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-paper-plane-o"></i> Gönderilen Mesajlar</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Konu</th>
                            <th>Alıcılar</th>
                            <th>Tarih</th>
                            <th style="width: 100px;">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sentMessages)): ?>
                            <tr><td colspan="4" class="text-center p-4">Henüz gönderilmiş mesajınız yok.</td></tr>
                        <?php else: ?>
                            <?php foreach ($sentMessages as $m): ?>
                                <tr>
                                    <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                                    <td data-label="Konu"><strong><?= htmlspecialchars($m['subject']) ?></strong></td>
                                    <td data-label="Alıcılar"><span class="badge badge-secondary"><?= htmlspecialchars($m['receiver_count']) ?> Kişi</span></td>
                                    <td data-label="Tarih"><small><?= htmlspecialchars(date('d.m.Y H:i', strtotime($m['created_at']))) ?></small></td>
                                    <td data-label="İşlem">
                                        <a href="index.php?module=messages&action=view_sent&id=<?= htmlspecialchars($m['id']) ?>" class="btn btn-sm btn-secondary">
                                            <i class="fa fa-eye"></i> Görüntüle
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
