<?php
if (!function_exists('display_menu_row_recursive')) {
    function display_menu_row_recursive(array $item, array $all_roles, int $level = 0) {
        $padding = $level * 25;
        ?>
        <tr>
            <td data-label="ID"><?= htmlspecialchars($item['id']) ?></td>
            <td data-label="Başlık" style="padding-left: <?= $padding + 10 ?>px;">
                <?= $level > 0 ? '<span style="color:#999;">└──&nbsp;</span>' : '' ?>
                <strong><?= htmlspecialchars($item['title']) ?></strong>
            </td>
            <td data-label="URL"><?= htmlspecialchars($item['url']) ?></td>
            <td data-label="İkon"><i class="fa <?= htmlspecialchars($item['icon'] ?? 'fa-circle-o') ?>"></i></td>
            <td data-label="Sıra"><?= htmlspecialchars($item['display_order']) ?></td>
            <td data-label="Roller">
                <?php
                if (!empty($item['assigned_roles_array'])) {
                    foreach ($item['assigned_roles_array'] as $role) {
                        echo '<span class="badge badge-secondary mr-1">' . htmlspecialchars(ucfirst($role)) . '</span>';
                    }
                } else {
                    echo "<i>(Rol atanmamış)</i>";
                }
                ?>
            </td>
            <td data-label="Aktif">
                <?php if (!empty($item['is_active'])): ?>
                    <span class="badge badge-success">Evet</span>
                <?php else: ?>
                    <span class="badge badge-danger">Hayır</span>
                <?php endif; ?>
            </td>
            <td data-label="İşlemler">
                <a href="index.php?module=menumanager&action=edit&id=<?= (int)$item['id'] ?>" class="btn btn-sm btn-warning">
                    <i class="fa fa-pencil"></i>
                </a>
                <a href="index.php?module=menumanager&action=delete&id=<?= (int)$item['id'] ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm(''<?= htmlspecialchars($item['title']) ?>' menü öğesini silmek istediğinize emin misiniz? Alt menüler köke taşınacaktır.')">
                    <i class="fa fa-trash"></i>
                </a>
            </td>
        </tr>
        <?php
        if (!empty($item['children'])) {
            foreach ($item['children'] as $child) {
                display_menu_row_recursive($child, $all_roles, $level + 1);
            }
        }
    }
}
?>

<h2>Menü Yöneticisi</h2>

<a href="index.php?module=menumanager&action=create" class="btn btn-primary mb-3"><i class="fa fa-plus"></i> Yeni Menü Öğesi Ekle</a>
<a href="index.php?module=menumanager&action=auto_add_modules" class="btn btn-info mb-3" onclick="return confirm('Modules klasöründeki menüde olmayan modüller otomatik olarak eklenecektir. Emin misiniz?')"><i class="fa fa-cogs"></i> Eksik Modülleri Otomatik Ekle</a>

<?php if (isset($_GET['status'])): ?>
    <div class="alert alert-success">
        <?php
        $messages = [
            'created'        => 'Menü öğesi başarıyla oluşturuldu.',
            'updated'        => 'Menü öğesi başarıyla güncellendi.',
            'deleted'        => 'Menü öğesi başarıyla silindi.',
            'modules_added'  => htmlspecialchars($_GET['count'] ?? 0) . ' adet yeni modül eklendi.',
        ];
        echo $messages[$_GET['status']] ?? 'İşlem başarılı.';
        ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?php
        $errors = [
            'not_found'     => 'Hata: Menü öğesi bulunamadı.',
            'empty_title'   => 'Hata: Başlık boş bırakılamaz.',
            'invalid_data'  => 'Hata: Geçersiz veri.',
            'invalid_parent'=> 'Hata: Bir menü kendi ebeveyni olamaz.',
            'missing_id'    => 'Hata: ID eksik.'
        ];
        echo $errors[$_GET['error']] ?? 'Bilinmeyen bir hata oluştu.';
        ?>
    </div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Menü Yapısı</h3>
  </div>
  <div class="card-body p-0">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Başlık (Hiyerarşi)</th>
          <th>URL</th>
          <th>İkon</th>
          <th>Sıra</th>
          <th>Roller</th>
          <th>Aktif</th>
          <th>İşlemler</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($menus_tree)): ?>
          <?php foreach ($menus_tree as $root): ?>
            <?php display_menu_row_recursive($root, $all_roles, 0); ?>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center p-4">Kayıtlı menü öğesi bulunamadı.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
