<?php if (empty($users)): ?>
  <tr>
    <td colspan="5" class="text-center p-4">Sistemde kayıtlı kullanıcı bulunmamaktadır.</td>
  </tr>
<?php else: ?>
  <?php foreach ($users as $user): ?>
    <tr>
      <!-- Ad/soyad hücresine tıklanınca profil modülüne git -->
      <td data-label="Ad Soyad"
          onclick="window.location='index.php?module=profile&id=<?= htmlspecialchars($user['id'] ?? '') ?>'"
          style="cursor:pointer;">
        <strong><?= htmlspecialchars($user['name'] ?? '') ?></strong><br>
        <small class="text-muted">TC: <?= htmlspecialchars($user['tc_kimlik'] ?? '-') ?></small>
      </td>

      <td data-label="Email"><?= htmlspecialchars($user['email'] ?? '') ?></td>

      <td data-label="Rol"><span class="badge badge-info"><?= htmlspecialchars($user['role'] ?? '') ?></span></td>

      <td data-label="Sınıf"><?= htmlspecialchars($user['class_name'] ?? '-') ?></td>

      <td data-label="İşlemler">
        <?php if (($user['role'] ?? '') !== 'admin'): ?>
          <a href="index.php?module=users&action=edit&id=<?= htmlspecialchars($user['id'] ?? '') ?>"
             class="btn btn-sm btn-warning" title="Düzenle">
            <i class="fa fa-pencil"></i>
          </a>
          <a href="index.php?module=users&action=delete&id=<?= htmlspecialchars($user['id'] ?? '') ?>"
             class="btn btn-sm btn-danger ml-1"
             onclick="return confirm('Bu kullanıcıyı silmek ve arşivlemek istediğinize emin misiniz?')"
             title="Sil">
            <i class="fa fa-trash"></i>
          </a>
        <?php else: ?>
          <span class="badge badge-secondary">🔒 Admin</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
<?php endif; ?>
