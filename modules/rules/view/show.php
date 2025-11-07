<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$rule = $rule ?? [];
$categories = $categories ?? [];
$ruleTypes = $ruleTypes ?? [];
$gradeRanges = $gradeRanges ?? [];
$canDelete = $canDelete ?? false;

// Flash mesajlar
if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= h($_SESSION['flash_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); endif;

if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= h($_SESSION['flash_error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); endif;

// JSON formatla
$conditionsFormatted = '';
if (!empty($rule['conditions'])) {
    $decoded = json_decode($rule['conditions'], true);
    $conditionsFormatted = $decoded ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $rule['conditions'];
}
?>

<div class="mb-3">
    <a href="index.php?module=rules&action=list" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left"></i> Listeye Dön
    </a>
    <a href="index.php?module=rules&action=edit&id=<?= (int)$rule['id'] ?>" class="btn btn-primary btn-sm">
        <i class="fa fa-pen"></i> Düzenle
    </a>
    <?php if ($canDelete): ?>
        <a href="index.php?module=rules&action=delete&id=<?= (int)$rule['id'] ?>" 
           class="btn btn-danger btn-sm"
           onclick="return confirm('Bu kuralı silmek istediğinize emin misiniz?')">
            <i class="fa fa-trash"></i> Sil
        </a>
    <?php endif; ?>
</div>

<!-- Kural Bilgileri -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fa fa-gavel"></i> <?= h($rule['name'] ?? '') ?>
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <strong class="text-muted small">Kural Kodu</strong>
                <div><code class="fs-6"><?= h($rule['code'] ?? '') ?></code></div>
            </div>
            <div class="col-md-6">
                <strong class="text-muted small">Durum</strong>
                <div>
                    <?= !empty($rule['is_active']) 
                        ? '<span class="badge bg-success fs-6">Aktif</span>' 
                        : '<span class="badge bg-secondary fs-6">Pasif</span>' ?>
                </div>
            </div>
            <div class="col-md-4">
                <strong class="text-muted small">Kategori</strong>
                <div>
                    <span class="badge bg-info fs-6">
                        <?= h($categories[$rule['category']] ?? $rule['category']) ?>
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <strong class="text-muted small">Sınıf Aralığı</strong>
                <div>
                    <?php if (!empty($rule['grade_range'])): ?>
                        <span class="badge bg-secondary fs-6">
                            <?= h($gradeRanges[$rule['grade_range']] ?? $rule['grade_range']) ?>
                        </span>
                    <?php else: ?>
                        <span class="text-muted">Tüm Sınıflar</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4">
                <strong class="text-muted small">Kural Tipi</strong>
                <div>
                    <span class="badge bg-primary fs-6">
                        <?= h($ruleTypes[$rule['rule_type']] ?? $rule['rule_type']) ?>
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <strong class="text-muted small">Öncelik</strong>
                <div><span class="badge bg-dark fs-6"><?= (int)($rule['priority'] ?? 0) ?></span></div>
            </div>
            <div class="col-md-6">
                <strong class="text-muted small">Oluşturulma</strong>
                <div><?= h($rule['created_at'] ?? '—') ?></div>
            </div>
            <div class="col-12">
                <strong class="text-muted small">Açıklama</strong>
                <div class="mt-2 p-3 bg-light rounded">
                    <?= nl2br(h($rule['description'] ?? 'Açıklama girilmemiş.')) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Koşullar -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fa fa-cogs"></i> Kural Koşulları</h6>
    </div>
    <div class="card-body">
        <?php if (!empty($conditionsFormatted)): ?>
            <pre class="bg-dark text-light p-3 rounded"><code><?= h($conditionsFormatted) ?></code></pre>
        <?php else: ?>
            <div class="text-muted">Koşul tanımlanmamış.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Kullanım Bilgisi -->
<div class="alert alert-info">
    <h6 class="alert-heading"><i class="fa fa-info-circle"></i> Kullanım Bilgisi</h6>
    <p class="mb-0">
        Bu kural <strong><?= !empty($rule['is_active']) ? 'aktif' : 'pasif' ?></strong> durumda. 
        <?= !empty($rule['is_active']) 
            ? 'Sistemde otomatik olarak uygulanacaktır.' 
            : 'Şu anda uygulanmıyor.' ?>
    </p>
</div>