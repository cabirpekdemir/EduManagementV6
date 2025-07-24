<h2>Yeni Ödev Ver</h2>
<form method="POST" action="?module=teacher_assignments&action=store">
  <label>Ders:</label>
  <select name="course_id">
    <?php foreach ($courses as $c): ?>
      <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
    <?php endforeach; ?>
  </select><br><br>
  <label>Başlık:</label><br>
  <input type="text" name="title" required><br><br>
  <label>Açıklama:</label><br>
  <textarea name="description" rows="4" cols="50"></textarea><br><br>
  <label>Teslim Tarihi:</label><br>
  <input type="date" name="due_date"><br><br>
  <button type="submit">Kaydet</button>
</form>
