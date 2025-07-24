<h2>Yeni Mesaj Gönder</h2>

<form action="index.php?module=messages&action=store" method="POST">
  <label><strong>Konu:</strong></label>
  <input type="text" name="subject" class="form-control" required>

  <label><strong>İçerik:</strong></label>
  <textarea name="content" class="form-control" rows="6" required></textarea>

  <label><strong>Ders (İsteğe bağlı):</strong></label>
  <select name="course_id" class="form-control">
    <option value="">Seçiniz</option>
    <?php foreach ($courses as $c): ?>
      <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
    <?php endforeach; ?>
  </select>

  <label><strong>Sınıf (İsteğe bağlı):</strong></label>
  <input type="text" name="sinif" class="form-control" placeholder="örn. 5-A">

  <label><strong>Alıcılar:</strong></label>
  <div style="max-height:200px; overflow-y:auto; border:1px solid #ccc; padding:10px; margin-bottom: 1em;">
    <?php if (!empty($users)): ?>
      <?php foreach ($users as $u): ?>
        <label>
          <input type="checkbox" name="receiver_ids[]" value="<?= $u['id'] ?>">
          <?= htmlspecialchars($u['name']) ?> (<?= $u['role'] ?>)
        </label><br>
      <?php endforeach; ?>
    <?php else: ?>
      <p><strong>Gönderilecek kullanıcı bulunamadı.</strong></p>
    <?php endif; ?>
  </div>

  <button type="submit" class="btn btn-success">Gönder</button>
</form>
