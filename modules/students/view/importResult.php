<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$result = $result ?? null;
?>

<div class="mb-3">
    <a href="index.php?module=students&action=list" class="btn btn-primary btn-sm">
        <i class="fa fa-list"></i> Öğrenci Listesi
    </a>
    <a href="index.php?module=students&action=import" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-upload"></i> Tekrar İçe Aktar
    </a>
</div>

<?php if (!$result): ?>
    <div class="alert alert-warning">
        Sonuç bulunamadı.
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-md-8 mx-auto">
            <!-- Özet Kartları -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-success shadow-sm">
                        <div class="card-body text-center">
                            <h2 class="text-success mb-0"><?= (int)$result['imported'] ?></h2>
                            <p class="text-muted mb-0">Başarıyla İçe Aktarıldı</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-warning shadow-sm">
                        <div class="card-body text-center">
                            <h2 class="text-warning mb-0"><?= (int)$result['skipped'] ?></h2>
                            <p class="text-muted mb-0">Atlandı</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hatalar -->
            <?php if (!empty($result['errors'])): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-warning">
                        <h6 class="mb-0">
                            <i class="fa fa-exclamation-triangle"></i> Atlanan Kayıtlar ve Hatalar
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach (array_slice($result['errors'], 0, 50) as $error): ?>
                                <div class="list-group-item">
                                    <small><?= h($error) ?></small>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (count($result['errors']) > 50): ?>
                                <div class="list-group-item bg-light">
                                    <small class="text-muted">
                                        ... ve <?= count($result['errors']) - 50 ?> hata daha
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>