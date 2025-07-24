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
      <td><?= $m['is_active'] ? '✅' : '❌' ?></td>
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
