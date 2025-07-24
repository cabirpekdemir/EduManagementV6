<h2>Yeni Değerlendirme Oluştur</h2>
<form action="?module=evaluations&action=store" method="post">
    <table cellpadding="5" style="width: 100%;">
        <tr>
            <td style="width: 20%;"><label for="name">Değerlendirme Adı:</label></td>
            <td><input type="text" id="name" name="name" required size="50"></td>
        </tr>
        <tr>
            <td><label for="evaluation_type">Değerlendirme Türü:</label></td>
            <td>
                <select id="evaluation_type" name="evaluation_type" required>
                    <?php foreach ($evaluation_types as $type): ?>
                        <option value="<?= $type ?>"><?= $type ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="description">Açıklama:</label></td>
            <td><textarea id="description" name="description" rows="3" style="width: 95%;"></textarea></td>
        </tr>
        <tr>
            <td><label for="exam_date">Tarih:</label></td>
            <td><input type="date" id="exam_date" name="exam_date"></td>
        </tr>
        <tr>
            <td><label for="max_score">Maksimum Puan:</label></td>
            <td><input type="number" id="max_score" name="max_score" step="0.01" value="100"></td>
        </tr>
        <tr>
            <td><label for="class_id">İlgili Sınıf:</label></td>
            <td>
                <select id="class_id" name="class_id">
                    <option value="">-- Sınıf Seçilirse Tüm Sınıf Otomatik Atanır --</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
         <tr>
            <td style="vertical-align: top;"><label for="students">Bireysel Öğrenci Ata:<br><small>(İsteğe bağlı, sınıf seçilse bile ek olarak atanabilir)</small></label></td>
            <td>
                <select id="students" name="students[]" multiple size="8" style="width: 95%;">
                    <?php foreach ($all_students as $student): ?>
                        <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?></option>
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
                    <div class="teacher-slot" style="margin-bottom: 10px;">
                        <select name="teachers[0][id]">
                            <option value="">-- Öğretmen Seç --</option>
                            <?php foreach($all_teachers as $teacher): ?>
                                <option value="<?= $teacher['id'] ?>"><?= htmlspecialchars($teacher['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="teachers[0][role]">
                            <option value="">-- Rol Seç --</option>
                            <option value="sorumlu">Sorumlu</option>
                            <option value="gozetmen">Gözetmen</option>
                        </select>
                    </div>
                    <div class="teacher-slot">
                        <select name="teachers[1][id]">
                            <option value="">-- Öğretmen Seç --</option>
                            <?php foreach($all_teachers as $teacher): ?>
                                <option value="<?= $teacher['id'] ?>"><?= htmlspecialchars($teacher['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="teachers[1][role]">
                            <option value="">-- Rol Seç --</option>
                            <option value="sorumlu">Sorumlu</option>
                            <option value="gozetmen">Gözetmen</option>
                        </select>
                    </div>
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
                    <option value="draft">Taslak</option>
                    <option value="active" selected>Aktif</option>
                </select>
            </td>
        </tr>
        <tr>
            <td></td>
            <td><button type="submit">Kaydet</button></td>
        </tr>
    </table>
</form>