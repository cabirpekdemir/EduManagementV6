<h2>"<?= htmlspecialchars($evaluation['name'] ?? '') ?>" Değerlendirmesini Düzenle</h2>
<form action="?module=evaluations&action=update" method="post">
    <input type="hidden" name="id" value="<?= $evaluation['id'] ?>">
    <table cellpadding="5" style="width: 100%;">
        <tr>
            <td style="width: 20%;"><label for="name">Değerlendirme Adı:</label></td>
            <td><input type="text" id="name" name="name" required size="50" value="<?= htmlspecialchars($evaluation['name'] ?? '') ?>"></td>
        </tr>
        <tr>
            <td><label for="evaluation_type">Değerlendirme Türü:</label></td>
            <td>
                <select id="evaluation_type" name="evaluation_type" required>
                    <?php foreach ($evaluation_types as $type): ?>
                        <option value="<?= $type ?>" <?= ($evaluation['evaluation_type'] ?? '') == $type ? 'selected' : '' ?>>
                            <?= $type ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="description">Açıklama:</label></td>
            <td><textarea id="description" name="description" rows="3" style="width: 95%;"><?= htmlspecialchars($evaluation['description'] ?? '') ?></textarea></td>
        </tr>
        <tr>
            <td><label for="exam_date">Tarih:</label></td>
            <td><input type="date" id="exam_date" name="exam_date" value="<?= htmlspecialchars($evaluation['exam_date'] ?? '') ?>"></td>
        </tr>
        <tr>
            <td><label for="max_score">Maksimum Puan:</label></td>
            <td><input type="number" id="max_score" name="max_score" step="0.01" value="<?= htmlspecialchars($evaluation['max_score'] ?? '100') ?>"></td>
        </tr>
        <tr>
            <td><label for="class_id">İlgili Sınıf:</label></td>
            <td>
                <select id="class_id" name="class_id">
                     <option value="">-- Sınıf Seçilirse Tüm Sınıf Otomatik Atanır --</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= $class['id'] ?>" <?= ($evaluation['class_id'] ?? '') == $class['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($class['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label for="students">Bireysel Öğrenci Ata:<br><small>(Mevcut atamaları değiştirebilir veya ekleme yapabilirsiniz)</small></label></td>
            <td>
                <select id="students" name="students[]" multiple size="8" style="width: 95%;">
                    <?php foreach ($all_students as $student): ?>
                        <option value="<?= $student['id'] ?>" <?= in_array($student['id'], $assigned_students) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($student['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2"><hr></td>
        </tr>
        <tr>
            <td style="vertical-align: top;"><label>Görevli Öğretmenler:</label></td>
            <td>
                <div id="teacher-assignments">
                    <?php for ($i = 0; $i < 2; $i++): 
                        $current_teacher_id = $assigned_teachers[$i]['teacher_id'] ?? '';
                        $current_role = $assigned_teachers[$i]['role'] ?? '';
                    ?>
                    <div class="teacher-slot" style="margin-bottom: 10px;">
                        <select name="teachers[<?= $i ?>][id]">
                            <option value="">-- Öğretmen Seç --</option>
                            <?php foreach($all_teachers as $teacher): ?>
                                <option value="<?= $teacher['id'] ?>" <?= $current_teacher_id == $teacher['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($teacher['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select name="teachers[<?= $i ?>][role]">
                            <option value="">-- Rol Seç --</option>
                            <option value="sorumlu" <?= $current_role == 'sorumlu' ? 'selected' : '' ?>>Sorumlu</option>
                            <option value="gozetmen" <?= $current_role == 'gozetmen' ? 'selected' : '' ?>>Gözetmen</option>
                        </select>
                    </div>
                    <?php endfor; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2"><hr></td>
        </tr>
        <tr>
            <td><label for="status">Durum:</label></td>
            <td>
                <select id="status" name="status">
                    <option value="draft" <?= ($evaluation['status'] ?? '') == 'draft' ? 'selected' : '' ?>>Taslak</option>
                    <option value="active" <?= ($evaluation['status'] ?? '') == 'active' ? 'selected' : '' ?>>Aktif</option>
                    <option value="completed" <?= ($evaluation['status'] ?? '') == 'completed' ? 'selected' : '' ?>>Tamamlandı</option>
                    <option value="cancelled" <?= ($evaluation['status'] ?? '') == 'cancelled' ? 'selected' : '' ?>>İptal Edildi</option>
                </select>
            </td>
        </tr>
        <tr>
            <td></td>
            <td><button type="submit">Güncelle</button></td>
        </tr>
    </table>
</form>