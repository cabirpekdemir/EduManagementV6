<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$rules = $rules ?? [];
$categories = $categories ?? [];
$ruleTypes = $ruleTypes ?? [];
$currentCategory = $currentCategory ?? null;
$currentRuleType = $currentRuleType ?? null;
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
?>

<!-- Filtreler -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="get" action="index.php" class="row g-3">
            <input type="hidden" name="module" value="rules">
            <input type="hidden" name="action" value="list">
            
            <div class="col-md-4">
                <label class="form-label">Kategori</label>
                <select name="category" class="form-select">
                    <option value="">Tüm Kategoriler</option>
                    <?php foreach ($categories as $key => $name): ?>
                        <option value="<?= h($key) ?>" <?= $currentCategory === $key ? 'selected' : '' ?>>
                            <?= h($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Kural Tipi</label>
                <select name="rule_type" class="form-select">
                    <option value="">Tüm Tipler</option>
                    <?php foreach ($ruleTypes as $key => $name): ?>
                        <option value="<?= h($key) ?>" <?= $currentRuleType === $key ? 'selected' : '' ?>>
                            <?= h($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary me-2">Filtrele</button>
                <a href="index.php?module=rules&action=list" class="btn btn-outline-secondary">Temizle</a>
            </div>
        </form>
    </div>
</div>

<!-- Kurallar Listesi -->
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Kurallar</h5>
        <a href="index.php?module=rules&action=create" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Yeni Kural
        </a>
    </div>

    <div class="card-body p-0">
        <?php if (empty($rules)): ?>
            <div class="text-center text-muted py-5">
                Kayıt bulunamadı.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px">#</th>
                            <th style="width:80px">Kod</th>
                            <th>Kural Adı</th>
                            <th style="width:120px">Kategori</th>
                            <th style="width:100px">Sınıf</th>
                            <th style="width:140px">Tip</th>
                            <th style="width:80px">Öncelik</th>
                            <th style="width:80px">Durum</th>
                            <th class="text-end" style="width:180px">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $lastCategory = null;
                        foreach ($rules as $rule): 
                            // Kategori değiştiğinde ayırıcı satır
                            if ($lastCategory !== $rule['category']):
                                $lastCategory = $rule['category'];
                        ?>
                            <tr class="table-secondary">
                                <td colspan="9" class="fw-bold">
                                    <i class="fa fa-folder-open"></i> 
                                    <?= h($categories[$rule['category']] ?? $rule['category']) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                            <tr>
                                <td><?= (int)$rule['id'] ?></td>
                                <td>
                                    <code class="small"><?= h($rule['code']) ?></code>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= h($rule['name']) ?></div>
                                    <?php if (!empty($rule['description'])): ?>
                                        <small class="text-muted">
                                            <?= h(mb_substr($rule['description'], 0, 60)) ?>
                                            <?= mb_strlen($rule['description']) > 60 ? '...' : '' ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= h($categories[$rule['category']] ?? $rule['category']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($rule['grade_range'])): ?>
                                        <span class="badge bg-secondary"><?= h($rule['grade_range']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?= h($ruleTypes[$rule['rule_type']] ?? $rule['rule_type']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-dark"><?= (int)$rule['priority'] ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($rule['is_active'])): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pasif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="index.php?module=rules&action=show&id=<?= (int)$rule['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        Gör
                                    </a>
                                    <a href="index.php?module=rules&action=edit&id=<?= (int)$rule['id'] ?>" 
                                       class="btn btn-sm btn-outline-warning">
                                        Düzenle
                                    </a>
                                    <?php if ($canDelete): ?>
                                        <a href="index.php?module=rules&action=delete&id=<?= (int)$rule['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Bu kuralı silmek istediğinize emin misiniz?')">
                                            Sil
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>