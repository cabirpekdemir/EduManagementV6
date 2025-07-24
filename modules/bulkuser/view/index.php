<h2>Toplu Kullanıcı Ekleme</h2>
<p class="lead">Excel veya Google E-Tablolar'daki kullanıcı listenizi (başlık satırı hariç) kopyalayıp aşağıdaki alana yapıştırın.</p>

<!-- Bildirim Mesajları -->
<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <strong>Hata:</strong> Bir sorun oluştu veya hiç veri yapıştırmadınız.
    </div>
<?php endif; ?>

<?php if (isset($_GET['success_count']) || isset($_GET['skipped_count'])): ?>
    <div class="alert alert-success">
        <strong>İşlem tamamlandı!</strong> Başarıyla eklenen kullanıcı sayısı: <strong><?= (int)($_GET['success_count'] ?? 0) ?></strong>.
    </div>
    <?php if (($_GET['skipped_count'] ?? 0) > 0): ?>
    <div class="alert alert-warning">
        Atlanan (geçersiz veya zaten kayıtlı) kullanıcı sayısı: <strong><?= (int)$_GET['skipped_count'] ?></strong>.
    </div>
    <?php endif; ?>
<?php endif; ?>


<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Gerekli Sütun Sırası</h3>
    </div>
    <div class="card-body">
        <p>Lütfen yapıştıracağınız verilerin aşağıdaki sütun sırasına tam olarak uyduğundan emin olun. Her bir kullanıcı ayrı bir satırda olmalıdır.</p>
        <p>Sütunlar birbirinden <strong>Sekme (Tab)</strong> ile ayrılmalıdır.</p>
        <code class="d-block bg-light p-2 rounded">Ad Soyad | E-posta | Şifre | Rol (student/teacher/parent) | Sınıf ID (opsiyonel) | TC Kimlik (opsiyonel)</code>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Kullanıcı Verilerini Yapıştırın</h3>
    </div>
    <form action="index.php?module=bulkuser&action=paste_upload" method="post">
        <div class="card-body">
            <textarea name="user_data" rows="15" class="form-control" style="font-family: monospace; font-size: 14px; line-height: 1.5;" placeholder="Örnek Satır:
Ahmet Yılmaz	ahmet@ornek.com	Parola123	student	5	12345678901
Ayşe Kaya	ayse@ornek.com	Sifre456	teacher			23456789012
..."></textarea>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success">
                <i class="fa fa-users"></i> Kullanıcıları Ekle
            </button>
        </div>
    </form>
</div>
