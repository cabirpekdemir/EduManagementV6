<?php
// Bu dosya, ana index.php tarafından çağrılır.
// $announcement ve $pageTitle gibi değişkenler,
// announcementscontroller.php'nin view() metodu tarafından sağlanır.

// Güvenlik için, eğer $announcement değişkeni yoksa, bir hata gösterelim.
if (!isset($announcement) || !is_array($announcement)) {
    echo "<div class='alert alert-danger'>Duyuru verisi yüklenemedi.</div>";
    return; // Kodun geri kalanının çalışmasını engelle.
}
?>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fa fa-bullhorn"></i>
            <?= htmlspecialchars($announcement['title'] ?? 'Başlık Yok') ?>
        </h3>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Yayınlayan:</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($announcement['creator_name'] ?? 'Bilinmiyor') ?></dd>

            <dt class="col-sm-3">Yayın Tarihi:</dt>
            <dd class="col-sm-9"><?= htmlspecialchars(date('d F Y, H:i', strtotime($announcement['created_at']))) ?></dd>
            
            <dt class="col-sm-3">Hedef Kitle:</dt>
            <dd class="col-sm-9">
                <span class="badge badge-info">
                    <?= htmlspecialchars(ucfirst($announcement['target_role'] ?? 'Bilinmiyor')) ?>
                </span>
            </dd>
        </dl>

        <hr>

        <h4>Duyuru İçeriği</h4>
        <div class="mt-3">
            <?php
                // nl2br fonksiyonu, veritabanındaki satır atlamalarını (\n)
                // HTML'in anlayacağı <br> etiketlerine çevirir.
                echo nl2br(htmlspecialchars($announcement['content'] ?? 'İçerik bulunamadı.'));
            ?>
        </div>
    </div>
    <div class="card-footer">
        <a href="index.php?module=announcements&action=index" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Tüm Duyurulara Geri Dön
        </a>
    </div>
</div>
