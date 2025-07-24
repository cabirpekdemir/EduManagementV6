<h2>Sınıf Sınav Sonuçları: <?= e($class_info['name']) ?></h2>

<div style="margin-bottom: 20px;">
    <a href="index.php?module=classes&action=index" class="btn">&laquo; Sınıf Listesine Dön</a>
    <a href="index.php?module=exams&action=index" class="btn" style="margin-left:10px;">Sınav Tanımlarına Dön</a>
</div>

<form method="GET" action="index.php">
    <input type="hidden" name="module" value="exams">
    <input type="hidden" name="action" value="class_results">
    <input type="hidden" name="class_id" value="<?= e($class_info['id']) ?>">
    <label for="exam_id_filter">Sınava Göre Filtrele:</label>
    <select name="exam_id_filter" id="exam_id_filter" onchange="this.form.submit()">
        <option value="0">-- Tüm Sınavlar --</option>
        <?php if(!empty($class_exams)): foreach($class_exams as $exam_filter): ?>
            <option value="<?= e($exam_filter['id']) ?>" <?= ($exam_id_filter == $exam_filter['id']) ? 'selected' : '' ?>>
                <?= e($exam_filter['name']) ?>
            </option>
        <?php endforeach; endif; ?>
    </select>
    <?php if ($exam_id_filter): ?>
        <a href="index.php?module=exams&action=results&exam_id=<?= e($exam_id_filter) ?>" class="btn" style="margin-left:10px;">Bu Sınav İçin Not Gir/Düzenle</a>
    <?php endif; ?>
</form>


<table border="1" cellpadding="6" cellspacing="0" style="margin-top:20px; width: 100%;">
    <thead>
        <tr>
            <th>Öğrenci Adı</th>
            <th>Sınav Adı</th>
            <th>Sınav Tarihi</th>
            <th>Puan</th>
            <th>Harf Notu</th>
            <th>Giren Kişi</th>
            <th>Giriş Tarihi</th>
            <?php if($userRole === 'admin' || $userRole === 'teacher'): ?>
            <th>İşlemler</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($results)): ?>
            <?php foreach ($results as $result): ?>
            <tr>
                <td><?= e($result['student_name']) ?></td>
                <td><?= e($result['exam_name']) ?></td>
                <td><?= e($result['exam_date'] ? date('d.m.Y', strtotime($result['exam_date'])) : '') ?></td>
                <td><?= e($result['score'] ?? 'Girilmedi') ?></td>
                <td><?= e($result['grade'] ?? '-') ?></td>
                <td><?= e($result['entry_user_name']) ?></td>
                <td><?= e(date('d.m.Y H:i', strtotime($result['entry_date']))) ?></td>
                <?php if($userRole === 'admin' || $userRole === 'teacher'): ?>
                <td>
                    <a href="index.php?module=exams&action=edit_result&result_id=<?= e($result['result_id']) ?>">Düzenle</a>
                    <a href="index.php?module=exams&action=delete_result&result_id=<?= e($result['result_id']) ?>" 
                       onclick="return confirm('Bu sınav sonucunu silmek istediğinize emin misiniz?')" 
                       style="color:red; margin-left:5px;">Sil</a>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="<?= ($userRole === 'admin' || $userRole === 'teacher') ? '8' : '7' ?>">Bu sınıf için <?= $exam_id_filter ? 'seçili sınava ait' : '' ?> sonuç bulunamadı.</td></tr>
        <?php endif; ?>
    </tbody>
</table>