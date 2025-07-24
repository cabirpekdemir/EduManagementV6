<h2>Sınav Yoklama Girişi: <?= e($exam['name']) ?></h2>
<p><strong>Sınav Tarihi:</strong> <?= e($exam['exam_date'] ? date('d.m.Y', strtotime($exam['exam_date'])) : 'Belirtilmemiş') ?></p>

<a href="index.php?module=exams&action=index" class="btn" style="margin-bottom:15px;">&laquo; Sınav Listesine Dön</a>
<?php if (isset($_GET['status_message']) && $_GET['status_message'] === 'attendance_saved'): ?>
    <p style="color: green; border:1px solid green; padding:10px;">Yoklama bilgileri başarıyla kaydedildi.</p>
<?php endif; ?>

<form method="post" action="<?= e($formAction) ?>">
    <input type="hidden" name="exam_id" value="<?= e($exam['id']) ?>">
    <table border="1" cellpadding="6" cellspacing="0" style="width: 100%;">
        <thead>
            <tr>
                <th>Öğrenci Adı</th>
                <th>Sınıfı</th>
                <th>Yoklama Durumu</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($students)): ?>
                <?php foreach ($students as $student): ?>
                    <input type="hidden" name="student_ids_in_form_att[<?= e($student['id']) ?>]" value="1"> <tr>
                        <td><?= e($student['name']) ?></td>
                        <td><?= e($student['sinif'] ?? 'N/A') ?></td>
                        <td>
                            <select name="attendance[<?= e($student['id']) ?>]">
                                <option value="">-- Seçiniz --</option>
                                <?php 
                                $current_status = $attendance_map[$student['id']] ?? null;
                                foreach($attendance_statuses as $status_val): ?>
                                    <option value="<?= e($status_val) ?>" <?= ($current_status == $status_val) ? 'selected' : '' ?>>
                                        <?= e(ucfirst(str_replace('_',' ',$status_val))) // Örn: 'present' -> 'Present' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Bu sınav için yoklama alınacak öğrenci bulunamadı.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if (!empty($students)): ?>
        <br>
        <button type="submit" class="btn">Yoklamayı Kaydet</button>
    <?php endif; ?>
</form>