<h2>Modül Yöneticisi</h2>
<p class="lead">Sistemdeki modül dosyalarını tarayarak veritabanında eksik olanları otomatik olarak menüye ekler.</p>

<!-- Bildirimler -->
<?php if (isset($_GET['status']) && $_GET['status'] === 'success' && isset($_GET['count'])): ?>
    <div class="alert alert-success">
        <strong>Başarılı!</strong> <?= (int)$_GET['count'] > 0 ? htmlspecialchars((int)$_GET['count']) . ' adet yeni modül menüye eklendi.' : 'Yeni modül bulunamadı, menü güncel.' ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <strong>Hata:</strong> <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>


<div class="card card-primary card-outline">
    <div class="card-body">
        <form method="POST" action="index.php?module=modulemanager&action=scan">
          <button type="submit" name="scan_modules" class="btn btn-lg btn-info" onclick="return confirm('Modules klasöründeki menüde olmayan modüller otomatik olarak eklenecektir. Bu işlem geri alınamaz. Emin misiniz?')">
            <i class="fa fa-cogs"></i> Yeni Modülleri Tara ve Ekle
          </button>
        </form>
    </div>
</div>

<?php if (!empty($added)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Son Taramada Eklenen Modüller</h3>
    </div>
    <div class="card-body">
        <ul class="list-group">
            <?php foreach ($added as $mod): ?>
                <li class="list-group-item">
                    <i class="fa fa-check-circle text-success mr-2"></i><?= htmlspecialchars($mod) ?> menüye eklendi.
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>
