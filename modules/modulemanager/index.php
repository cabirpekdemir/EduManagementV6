<h2>Modül Yöneticisi</h2>
<form method="POST">
  <button type="submit" name="scan_modules">🔄 Yeni Modülleri Tara ve Ekle</button>
</form>

<?php if (!empty($added)): ?>
  <h3>Yeni Eklenen Modüller</h3>
  <ul>
    <?php foreach ($added as $mod): ?>
      <li><?= htmlspecialchars($mod) ?> eklendi.</li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<h3>Mevcut Modüller</h3>
<table border="1" cellpadding="5">
  <tr><th>ID</th><th>Modül Adı</th><th>Etiket</th><th>Aktif mi?</th><th>İşlem</th></tr>
  <?php foreach ($modulesList as $m): ?>
    <tr>
      <td><?= $m['id'] ?></td>
      <td><?= htmlspecialchars($m['module_name']) ?></td>
      <td><?= htmlspecialchars($m['label']) ?></td>
       <td class="text-end">
  <a href="index.php?module=modulemanager&action=show&id=<?= (int)$row['id'] ?>"
     class="btn btn-sm btn-outline-primary me-1">Gör</a>

  <a href="index.php?module=modulemanager&action=edit&id=<?= (int)$row['id'] ?>"
     class="btn btn-sm btn-warning me-1">Düzenle</a>

  <form action="index.php?module=modulemanager&action=destroy&id=<?= (int)$row['id'] ?>"
        method="post" class="d-inline">
    <button type="submit" class="btn btn-sm btn-danger"
            onclick="return confirm('Bu dersi silmek istediğinize emin misiniz?');">
      Sil
    </button>
  </form>
</td>

      <td>
        <form method="POST" style="display:inline;">
          <input type="hidden" name="mod_id" value="<?= $m['id'] ?>">
          <input type="hidden" name="current_status" value="<?= $m['is_active'] ?>">
          <button type="submit" name="toggle_module">
            <?= $m['is_active'] ? 'Pasifleştir' : 'Aktifleştir' ?>
          </button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
