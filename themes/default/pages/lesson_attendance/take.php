<h2>Yoklama Al: <?= e($course_data['name'] ?? '') ?> (<?= e(date('d.m.Y', strtotime($date_display ?? ''))) ?> - <?= e($lesson_slot_data['day'] ?? '') ?> <?= e($lesson_slot_data['start_f'] ?? '') ?>-<?= e($lesson_slot_data['end_f'] ?? '') ?>)</h2>

<a href="index.php?module=lesson_attendance&action=index" class="btn" style="margin-bottom:15px;">&laquo; Ders Seçimine Geri Dön</a>

<?php if (!empty($error_message)): ?>
    <div style="color: red; padding: 10px; border: 1px solid red; background-color: #ffe0e0; margin-bottom: 15px;">
        Hata: <?= e($error_message) ?>
    </div>
<?php endif; ?>
<?php if (!empty($status_message)): ?>
    <div style="color: green; padding: 10px; border: 1px solid green; background-color: #e0ffe0; margin-bottom: 15px;">
        Başarılı: <?= e($status_message) ?>
    </div>
<?php endif; ?>

<form method="POST" action="index.php?module=lesson_attendance&action=save">
    <input type="hidden" name="course_id" value="<?= e($course_data['id'] ?? '') ?>">
    <input type="hidden" name="lesson_slot_id" value="<?= e($lesson_slot_data['id'] ?? '') ?>">
    <input type="hidden" name="date" value="<?= e($date_display ?? '') ?>">

    <table border="1" cellpadding="6" cellspacing="0" style="width:100%;">
        <thead>
            <tr>
                <th>Öğrenci Adı</th>
                <th>Sınıfı</th>
                <th>Durum</th>
                <th>Notlar</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($students_list)): ?>
                <?php foreach ($students_list as $student): ?>
                    <?php 
                    $current_status = $attendance_map_data[$student['id']]['status'] ?? 'Geldi';
                    $current_notes = $attendance_map_data[$student['id']]['notes'] ?? '';
                    ?>
                    <tr>
                        <td>
                            <?= e($student['name'] ?? '') ?>
                            <input type="hidden" name="students[]" value="<?= e($student['id'] ?? '') ?>">
                        </td>
                        <td><?= e($student['class_name'] ?? 'N/A') ?></td>
                        <td>
                            <select name="status[<?= e($student['id'] ?? '') ?>]" style="width:100%;">
                                <?php foreach ($statuses_list as $status_option): ?>
                                    <option value="<?= e($status_option) ?>" <?= ($status_option == $current_status) ? 'selected' : '' ?>>
                                        <?= e($status_option) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="notes[<?= e($student['id'] ?? '') ?>]" value="<?= e($current_notes) ?>" placeholder="Not ekle" style="width:98%;">
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Bu ders için henüz kayıtlı öğrenci bulunmamaktadır.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <button type="submit" class="btn" style="margin-top:20px;">Yoklamayı Kaydet/Güncelle</button>
</form>