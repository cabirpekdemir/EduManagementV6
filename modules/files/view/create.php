<h2>Yeni Dosya Paylaş</h2>

<form method="POST" action="index.php?module=files&action=store" enctype="multipart/form-data">
  <label><strong>Dosya:</strong></label>
  <input type="file" name="file" required class="form-control mb-2">

  <label><strong>Açıklama:</strong></label>
  <textarea name="description" class="form-control mb-2" rows="3"></textarea>

  <label><strong>Paylaşılacak Kişiler:</strong></label>
  <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
    <?php if (!empty($users)): ?>
      <?php foreach ($users as $u): ?>
        <label>
          <input type="checkbox" name="shared_with[]" value="<?= $u['id'] ?>">
          <?= htmlspecialchars($u['name']) ?>
        </label><br>
      <?php endforeach; ?>
    <?php else: ?>
      <p><i>Paylaşılabilecek kullanıcı bulunamadı.</i></p>
    <?php endif; ?>
  </div>

  <button type="submit" class="btn btn-success mt-3">Yükle ve Paylaş</button>
</form>
