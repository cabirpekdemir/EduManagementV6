<h2>Yeni Ödev Yükle</h2>

<form action="index.php?module=assignments&action=store" method="POST" enctype="multipart/form-data">
  <label>Ders Seçin:</label>
  <select name="course_id" required>
    <?php foreach ($courses as $c): ?>
      <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
    <?php endforeach; ?>
  </select>

  <label>Başlık:</label>
  <input type="text" name="title" required>

  <label>Dosya:</label>
  <input type="file" name="file">

  <label>Ödev Verilecek Öğrenciler:</label>
  <div style="max-height:200px; overflow:auto; border:1px solid #ccc; padding:10px;">
    <?php foreach ($students as $s): ?>
      <label>
        <input type="checkbox" name="students[]" value="<?= $s['id'] ?>">
        <?= htmlspecialchars($s['name']) ?> (<?= $s['okul'] ?>/<?= $s['sinif'] ?>)
      </label><br>
    <?php endforeach; ?>
  </div>

  <button type="submit" class="btn btn-success">Kaydet</button>
</form>
