<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$result = $result ?? null;
?>

<div class="mb-3">
    <a href="index.php?module=import&action=index" class="btn btn-primary btn-sm">
        <i class="fa fa-home"></i> Import Menüsü
    </a>
</div>

<?php if (!$result): ?>
    <div class="alert alert-warning">Sonuç bulunamadı.</div>
<?php else: ?>
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-success shadow-sm">
                        <div class="card-body text-center">
                            <h2 class="text-success mb-0"><?= (int)$result['imported'] ?></h2>
                            <p class="text-muted mb-0">✅ Başarılı</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-warning shadow-sm">
                        <div class="card-body text-center">
                            <h2 class="text-warning mb-0"><?= (int)$result['skipped'] ?></h2>
                            <p class="text-muted mb-0">⚠️ Atlandı</p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($result['errors'])): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-warning">
                        <h6 class="mb-0">Hatalar (<?= count($result['errors']) ?>)</h6>
                    </div>
                    <div class="card-body" style="max-height:400px;overflow-y:auto;">
                        <?php foreach ($result['errors'] as $error): ?>
                            <div class="border-bottom pb-2 mb-2">
                                <small><?= h($error) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>