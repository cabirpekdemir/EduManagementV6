<h2>Ders Talep Formu</h2>
<form method="POST" action="/?module=studentscourserequest&action=store">
  <p>Lütfen talep ettiğiniz dersleri seçiniz:</p>
  <?php foreach ($courses as $course): ?>
    <label>
      <input type="checkbox" name="courses[]" value="<?= $course['id'] ?>">
      <?= htmlspecialchars($course['name']) ?>
    </label><br>
  <?php endforeach; ?>
  <br>
  <button type="submit">Kaydet</button>
</form>
