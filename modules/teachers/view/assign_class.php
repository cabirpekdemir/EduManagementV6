<?php
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$teachers = $teachers ?? [];
$q = $q ?? '';

// Flash mesajlar
if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?= h($_SESSION['flash']['type']) ?> alert-dismissible fade show">
        <?= h($_SESSION['flash']['msg']) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php unset($_SESSION['flash']); endif;
?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Öğretmenler</h5>
        <a href="index.php?module=teachers&action=create" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Yeni Öğretmen
        </a>
    </div>
    
    <div class="card-body">
        <form class="row g-2 mb-3" method="get">
            <input type="hidden" name="module" value="teachers">
            <input type="hidden" name="action" value="index">
            <div class="col-md-8">
                <input type="text" name="q" value="<?= h($q) ?>" class="form-control" 
                       placeholder="Ad, email, branş ara...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100">Ara</button>
            </div>
            <div class="col-md-2">
                <a class="btn btn-outline-dark w-100" href="index.php?module=teachers&action=index">Sıfırla</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Ad Soyad</th>
                        <th>Email</th>
                        <th>Branş</th>
                        <th>Kademe</th>
                        <th>Telefon</th>
                        <th>Durum</th>
                        <th class="text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($teachers)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Kayıt bulunamadı</td></tr>
                    <?php else: foreach ($teachers as $t): ?>
                    <tr>
                        <td><strong><?= h($t['name']) ?></strong></td>
                        <td><?= h($t['email']) ?></td>
                        <td><?= h($t['branch'] ?? '—') ?></td>
                        <td><?= h($t['kademe'] ?? '—') ?></td>
                        <td><?= h($t['phone'] ?? '—') ?></td>
                        <td>
                            <?php if ($t['is_active']): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="index.php?module=teachers&action=show&id=<?= (int)$t['id'] ?>" 
                               class="btn btn-sm btn-info">Gör</a>
                            <a href="index.php?module=teachers&action=edit&id=<?= (int)$t['id'] ?>" 
                               class="btn btn-sm btn-warning">Düzenle</a>
                            <a href="index.php?module=teachers&action=delete&id=<?= (int)$t['id'] ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>