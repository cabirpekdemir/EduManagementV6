<h2>Ödev Güncelle</h2>

<form action="index.php?module=assignments&action=update" method="POST">
  <input type="hidden" name="id" value="<?= $assignment['id'] ?>">

  <label>Başlık:</label>
  <input type="text" value="<?= htmlspecialchars($assignment['title']) ?>" disabled>

  <label>Dosya:</label>
  <p>
    <?php if (!empty($assignment['filename'])): ?>
      <a href="<?= $assignment['filename'] ?>" target="_blank">Dosyayı Görüntüle</a>
    <?php endif; ?>
  </p>

  <label>Not:</label>
  <input type="text" name="grade" value="<?= $assignment['grade'] ?>">

  <label>Öğretmen Notu:</label>
  <textarea name="teacher_note" rows="4"><?= $assignment['teacher_note'] ?></textarea>

  <button type="submit" class="btn btn-primary">Güncelle</button>
</form>

<h3>Ödev Alan Öğrenciler:</h3>
<ul>
  <?php foreach ($students as $s): ?>
    <li><?= htmlspecialchars($s['name']) ?> (<?= $s['okul'] ?>/<?= $s['sinif'] ?>)</li>
  <?php endforeach; ?>
</ul>
