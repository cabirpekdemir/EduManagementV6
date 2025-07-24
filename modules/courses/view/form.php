<h2><?= $isEdit ? "Kursu Düzenle" : "Yeni Kurs Ekle" ?></h2>

<form method="post" action="<?= e($formAction) ?>">
    <?php if ($isEdit && isset($course)): ?>
        <input type="hidden" name="id" value="<?= e($course['id']) ?>">
    <?php endif; ?>

    <div>
        <label for="name">Kurs Adı:</label><br>
        <input type="text" id="name" name="name" value="<?= e($course['name'] ?? '') ?>" required size="50">
    </div>
    <br>
    <div>
        <label for="description">Açıklama:</label><br>
        <textarea id="description" name="description" rows="3" cols="50"><?= e($course['description'] ?? '') ?></textarea>
    </div>
    <br>
    <div>
        <label for="teacher_id">Öğretmen:</label><br>
        <select name="teacher_id" id="teacher_id" required>
            <option value="">-- Öğretmen Seçin --</option>
            <?php if(!empty($teachers)): foreach ($teachers as $teacher): ?>
                <option value="<?= e($teacher['id']) ?>" <?= (($course['teacher_id'] ?? null) == $teacher['id']) ? 'selected' : '' ?>>
                    <?= e($teacher['name']) ?>
                </option>
            <?php endforeach; endif; ?>
        </select>
    </div>
    <br>
    <div>
        <label for="classroom">Derslik / Konum:</label><br>
        <input type="text" id="classroom" name="classroom" value="<?= e($course['classroom'] ?? '') ?>" size="50">
    </div>
    <br>

    <div>
        <label for="class_ids">Bu Dersin Verileceği Sınıflar/Şubeler (Birden fazla seçmek için Ctrl/Cmd basılı tutun):</label><br>
        <select name="class_ids[]" id="class_ids" multiple size="6" style="min-width: 300px;">
            <?php if (!empty($classes)): ?>
                <?php foreach ($classes as $class_item): ?>
                    <option value="<?= e($class_item['id']) ?>" 
                        <?= !empty($selected_class_ids) && in_array($class_item['id'], $selected_class_ids) ? 'selected' : '' ?>>
                        <?= e($class_item['name']) ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="" disabled>Sistemde kayıtlı sınıf bulunmuyor.</option>
            <?php endif; ?>
        </select>
    </div>
    <br>
    <fieldset style="border: 1px solid #ccc; padding: 15px; border-radius: 4px;">
        <legend>Ders Gün ve Saatleri</legend>
        <div id="timesContainer">
            <?php 
            $times = $course['times'] ?? [['day'=>'', 'start_time'=>'', 'end_time'=>'']];
            foreach ($times as $i => $time): ?>
                <div class="time-row" style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="text" name="times[<?= $i ?>][day]" placeholder="Gün (örn: Pazartesi)" value="<?= e($time['day'] ?? '') ?>" required>
                    <input type="time" name="times[<?= $i ?>][start_time]" value="<?= e($time['start_time'] ?? '') ?>" required>
                    <input type="time" name="times[<?= $i ?>][end_time]" value="<?= e($time['end_time'] ?? '') ?>" required>
                    <button type="button" class="remove-time-btn" onclick="removeTimeRow(this)">Sil</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn-small" onclick="addTimeRow()">+ Yeni Zaman Ekle</button>
    </fieldset>
    <br>
    
    <button type="submit" class="btn">💾 <?= $isEdit ? "Güncelle" : "Oluştur" ?></button>
    <a href="index.php?module=courses&action=index" style="margin-left: 10px;">Vazgeç</a>
</form>

<script>
function addDayRow() {
    const idx = document.querySelectorAll('#timesContainer .day-row').length;
    const container = document.getElementById('timesContainer');
    const div = document.createElement('div');
    div.className = 'day-row';
    div.innerHTML = `
        <input type="text" name="days[${idx}][day]" placeholder="Gün (ör: Pazartesi)" required>
        <input type="time" name="days[${idx}][start_time]" required>
        <input type="time" name="days[${idx}][end_time]" required>
        <button type="button" onclick="removeRow(this)">Sil</button>
    `;
    container.appendChild(div);
}
function removeRow(btn) {
    btn.parentNode.remove();
}
</script>
