<h2>Yeni Not Ekle</h2>
<form method="post">
  <label>Öğrenci:</label>
  <select name="student_id" required>
    <?php foreach ($students as $student): ?>
      <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?></option>
    <?php endforeach; ?>
  </select>

  <br><br>

  <label>Kurs:</label>
  <select name="course_id" required>
    <?php foreach ($courses as $course): ?>
      <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['name']) ?></option>
    <?php endforeach; ?>
  </select>

  <br><br>

  <label>İçerik:</label><br>
  <textarea name="content" rows="4" cols="50" required></textarea>

  <br><br>
  <button type="submit">Kaydet</button>
</form>
