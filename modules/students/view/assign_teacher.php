<h2>Öğretmen Eşleştir</h2>
<form method="post" action="?module=students&action=saveTeacher&id=<?= $student['id'] ?>">
    <select name="teacher_id">
        <option value="">Seçiniz</option>
        <?php foreach ($teachers as $t): ?>
            <option value="<?= $t['id'] ?>" <?= $assigned_teacher == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Kaydet</button>
</form>
