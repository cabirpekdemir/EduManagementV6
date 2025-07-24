<h2>Öğrenci Ata</h2>

<form method="post" action="index.php?module=courses&action=saveStudent">
    <input type="hidden" name="course_id" value="<?= e($course_id) ?>">

    <label>Öğrenci Seç:</label><br>
    <select name="student_id" required>
        <option value="">Seçiniz</option>
        <?php foreach ($students as $s): ?>
            <option value="<?= e($s['id']) ?>"><?= e($s['name']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Kaydet</button>
</form>
<h2>Öğrenci Ata</h2>