<h2>Not Düzenle</h2>
<form method="post">
  <label>Öğrenci:</label>
  <select name="student_id" required>
    <?php foreach ($students as $student): ?>
      <option value="<?= $student['id'] ?>" <?= $student['id'] == $note['student_id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($student['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <br><br>

  <label>Kurs:</label>
  <select name="course_id" required>
    <?php foreach ($courses as $course): ?>
      <option value="<?= $course['id'] ?>" <?= $course['id'] == $note['course_id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($course['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <br><br>

  <label>İçerik:</label><br>
  <textarea name="content" rows="4" cols="50" required><?= htmlspecialchars($note['content']) ?></textarea>

  <br><br>
  <button type="submit">Güncelle</button>
</form>
