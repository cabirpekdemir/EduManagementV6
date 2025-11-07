<?php
// modules/course_groups/view/index.php
if (!function_exists('e')) {
  function e($v){ return htmlspecialchars((string)($v??''), ENT_QUOTES, 'UTF-8'); }
}
$course_groups = $course_groups ?? [];

// Flash mesajları
if (isset($_SESSION['form_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= e($_SESSION['form_error']) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php unset($_SESSION['form_error']); endif;

if (isset($_SESSION['form_ok'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= e($_SESSION['form_ok']) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php unset($_SESSION['form_ok']); endif;
?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Ders Grupları</h5>
        <a href="index.php?module=course_groups&action=create" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Yeni Ders Grubu
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:25%">Grup Adı</th>
                        <th style="width:30%">Açıklama</th>
                        <th style="width:15%">Oluşturan</th>
                        <th style="width:15%">Tarih</th>
                        <th class="text-end" style="width:15%">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($course_groups)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Kayıtlı ders grubu bulunamadı.</td></tr>
                    <?php else: foreach ($course_groups as $group): ?>
                    <tr>
                        <td><strong><?= e($group['name']) ?></strong></td>
                        <td><?= e($group['description'] ?? '') ?></td>
                        <td><?= e($group['creator_name']) ?></td>
                        <td><small><?= e(date('d.m.Y H:i', strtotime($group['created_at']))) ?></small></td>
                        <td class="text-end">
                            <a href="index.php?module=course_groups&action=edit&id=<?= (int)$group['id'] ?>"
                               class="btn btn-sm btn-warning me-1"
                               title="Düzenle">
                                <i class="fa fa-edit"></i> Düzenle
                            </a>
                            
                            <a href="index.php?module=course_groups&action=delete&id=<?= (int)$group['id'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Bu ders grubunu silmek istediğinize emin misiniz?');"
                               title="Sil">
                                <i class="fa fa-trash"></i> Sil
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>