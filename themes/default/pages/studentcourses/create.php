<h2><?= isset($sc) ? 'Düzenle' : 'Yeni Öğrenci-Ders İlişkisi' ?></h2>
<form method="POST" action="/?module=studentcourses&action=<?= isset($sc) ? 'update' : 'store' ?>">
  <?php if (isset($sc)): ?>
    <input type="hidden" name="id" value="<?= $sc['id'] ?>">
  <?php endif; ?>

  <label>Öğrenci:</label>
  <select name="student_id" required>
    <option value="">Seçiniz</option>
    <?php foreach ($students as $s): ?>
      <option value="<?= $s['id'] ?>" <?= isset($sc) && $sc['student_id'] == $s['id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($s['name']) ?>
      </option>
    <?php endforeach; ?>
  </select><br><br>

  <label>Ders:</label>
  <select name="course_id" required>
    <option value="">Seçiniz</option>
    <?php foreach ($courses as $c): ?>
      <option value="<?= $c['id'] ?>" <?= isset($sc) && $sc['course_id'] == $c['id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($c['name']) ?>
      </option>
    <?php endforeach; ?>
  </select><br><br>

  <button type="submit">💾 Kaydet</button>
</form>
