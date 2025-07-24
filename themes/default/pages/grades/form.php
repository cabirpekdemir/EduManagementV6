<h2><?= $isEdit ? "Notu Düzenle" : "Yeni Not Ekle" ?></h2>
<form method="post" action="<?= $formAction ?>">
    
    <label>Öğrenci:</label><br>
    <select name="student_id" required>
        <option value="">Seçiniz</option>
        <?php foreach ($students as $s): ?>
            <option value="<?= $s['id'] ?>" <?= ($grade['student_id'] ?? '') == $s['id'] ? "selected" : "" ?>>
                <?= htmlspecialchars($s['name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Kurs:</label><br>
    <select name="course_id" required>
        <option value="">Seçiniz</option>
        <?php foreach ($courses as $c): ?>
            <option value="<?= $c['id'] ?>" <?= ($grade['course_id'] ?? '') == $c['id'] ? "selected" : "" ?>>
                <?= htmlspecialchars($c['name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Not:</label><br>
    <input type="text" name="grade" value="<?= htmlspecialchars($grade['grade'] ?? '') ?>" required><br><br>

    <label>Not Tarihi:</label><br>
    <input type="date" name="grade_date" value="<?= htmlspecialchars($grade['grade_date'] ?? date('Y-m-d')) ?>" required><br><br>
    
    <label>Açıklama:</label><br>
    <textarea name="comments" rows="4" cols="50"><?= htmlspecialchars($grade['comments'] ?? '') ?></textarea><br><br>

    <button type="submit"><?= $isEdit ? "Güncelle" : "Oluştur" ?></button>
    <a href="index.php?module=grades&action=index">Vazgeç</a>
</form>