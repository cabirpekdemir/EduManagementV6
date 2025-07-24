<h2><?= $isEdit ? "Sınavı Düzenle" : "Yeni Sınav Tanımla" ?></h2>

<form method="post" action="<?= e($formAction) ?>">
    <?php if ($isEdit && isset($exam)): ?>
        <input type="hidden" name="id" value="<?= e($exam['id']) ?>">
    <?php endif; ?>

    <div>
        <label for="name">Sınav Adı:</label><br>
        <input type="text" id="name" name="name" value="<?= e($exam['name'] ?? '') ?>" required size="50">
    </div>
    <br>
    <div>
        <label for="description">Açıklama:</label><br>
        <textarea id="description" name="description" rows="3" cols="50"><?= e($exam['description'] ?? '') ?></textarea>
    </div>
    <br>
    <div>
        <label for="exam_date">Sınav Tarihi:</label><br>
        <input type="date" id="exam_date" name="exam_date" value="<?= e($exam['exam_date'] ?? '') ?>">
    </div>
    <div>
        <label for="exam_date">Sınav Tarihi:</label><br>
        <input type="date" id="exam_date" name="exam_date" value="<?= e($exam['exam_date'] ?? '') ?>">
    </div>
    <br>

    <div>
        <label for="start_time">Başlangıç Saati (İsteğe Bağlı):</label><br>
        <input type="time" id="start_time" name="start_time" value="<?= e($exam['start_time'] ?? '') ?>">
    </div>
    <br>
    <div>
        <label for="end_time">Bitiş Saati (İsteğe Bağlı):</label><br>
        <input type="time" id="end_time" name="end_time" value="<?= e($exam['end_time'] ?? '') ?>">
    </div>
    <br>
    <div>
        <label for="course_id">İlişkili Ders (Genel bir sınavsa boş bırakın):</label><br>
        ```


    <tbody>
        <?php if (!empty($exams)): ?>
            <?php foreach ($exams as $exam): ?>
            <tr>
                <td><?= e($exam['id']) ?></td>
                <td><?= e($exam['name']) ?></td>
                <td><?= e($exam['exam_date'] ? date('d.m.Y', strtotime($exam['exam_date'])) : 'Belirtilmemiş') ?></td>
                <td> <?= e($exam['start_time'] ? date('H:i', strtotime($exam['start_time'])) : '--') ?>
                    -
                    <?= e($exam['end_time'] ? date('H:i', strtotime($exam['end_time'])) : '--') ?>
                </td>
                <td style="font-weight:bold; color: <?= $exam['status']==='active' ? 'blue' : ($exam['status']==='completed' ? 'green' : ($exam['status']==='cancelled' ? 'red' : 'grey')) ?>">
                    <?= e(ucfirst($exam['status'])) ?>
                </td>
                <td><?= e($exam['course_name'] ?? 'Genel') ?></td>
                <td><?= e($exam['class_name'] ?? 'Tümü') ?></td>
                <td><?= e($exam['max_score'] ?? 'N/A') ?></td>
                <td><?= e($exam['creator_name']) ?></td>
                <td>
                    </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <?php endif; ?>
    </tbody>
    <br>
    <div>
        
        <select name="course_id" id="course_id">
            <option value="">-- Ders Seçin --</option>
            <?php if(!empty($courses)): foreach($courses as $course): ?>
                <option value="<?= e($course['id']) ?>" <?= (($exam['course_id'] ?? null) == $course['id']) ? 'selected' : '' ?>>
                    <?= e($course['name']) ?>
                </option>
            <?php endforeach; endif; ?>
        </select>
    </div>
    <br>
    <div>
        <label for="class_id">İlişkili Sınıf/Şube (Tüm sınıflar içinse boş bırakın):</label><br>
        <select name="class_id" id="class_id">
            <option value="">-- Sınıf Seçin --</option>
            <?php if(!empty($classes)): foreach($classes as $class_item): ?>
                <option value="<?= e($class_item['id']) ?>" <?= (($exam['class_id'] ?? null) == $class_item['id']) ? 'selected' : '' ?>>
                    <?= e($class_item['name']) ?>
                </option>
            <?php endforeach; endif; ?>
        </select>
    </div>
    <br>
    <div>
        <label for="max_score">Maksimum Puan (Örn: 100):</label><br>
        <input type="number" id="max_score" name="max_score" value="<?= e($exam['max_score'] ?? '') ?>" step="0.01" style="width: 100px;">
    </div>
    <br>
    <div>
        <label for="status">Sınav Durumu:</label><br>
        <select name="status" id="status" required>
            <?php foreach($exam_statuses as $stat_val): ?>
            <option value="<?= e($stat_val) ?>" <?= (($exam['status'] ?? 'draft') == $stat_val) ? 'selected' : '' ?>><?= e(ucfirst($stat_val)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <br>
    
    <button type="submit" class="btn"><?= $isEdit ? "Güncelle" : "Oluştur" ?></button>
    <a href="index.php?module=exams&action=index" style="margin-left: 10px;">Vazgeç</a>
</form>