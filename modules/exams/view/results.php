<h2>Sınav Sonuç Girişi: <?= e($exam['name']) ?></h2>
<p><strong>Sınav Tarihi:</strong> <?= e($exam['exam_date'] ? date('d.m.Y', strtotime($exam['exam_date'])) : 'Belirtilmemiş') ?></p>
<p><strong>Max Puan:</strong> <?= e($exam['max_score'] ?? 'Belirtilmemiş') ?></p>

<a href="index.php?module=exams&action=index" class="btn" style="margin-bottom:15px;">&laquo; Sınav Listesine Dön</a>

<?php if (isset($_GET['status_message']) && $_GET['status_message'] === 'results_saved'): ?>
    <p style="color: green; border:1px solid green; padding:10px;">Sınav sonuçları başarıyla kaydedildi.</p>
<?php endif; ?>

<form method="post" action="<?= e($formAction) ?>">
    <input type="hidden" name="exam_id" value="<?= e($exam['id']) ?>">
    <table border="1" cellpadding="6" cellspacing="0" style="width: 100%;">
        <thead>
            <tr>
                <th>Öğrenci ID</th>
                <th>Öğrenci Adı</th>
                <th>Sınıfı</th>
                <th>Puan (Max: <?= e($exam['max_score'] ?? 'N/A') ?>)</th>
                <th>Harf Notu/Değerlendirme</th>
                <th>Yorumlar</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($students)): ?>
                <?php foreach ($students as $student): ?>
                    <?php $current_result = $exam_results[$student['id']] ?? ['score' => '', 'grade' => '', 'comments' => '']; ?>
                    <tr>
                        <td><?= e($student['id']) ?></td>
                        <td><?= e($student['name']) ?></td>
                        <td><?= e($student['sinif'] ?? 'N/A') ?></td>
                        <td>
                            <input type="number" 
                                   name="scores[<?= e($student['id']) ?>]" 
                                   value="<?= e($current_result['score']) ?>" 
                                   step="0.01" 
                                   <?php if(isset($exam['max_score'])): ?>max="<?= e($exam['max_score']) ?>"<?php endif; ?>
                                   min="0"
                                   style="width: 80px;">
                        </td>
                        <td>
                            <input type="text" 
                                   name="grades[<?= e($student['id']) ?>]" 
                                   value="<?= e($current_result['grade']) ?>" 
                                   style="width: 100px;">
                        </td>
                        <td>
                            <textarea name="comments[<?= e($student['id']) ?>]" rows="1" style="width: 98%;"><?= e($current_result['comments']) ?></textarea>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Bu sınav için atanmış veya bulunabilen öğrenci yok. Lütfen sınav tanımındaki Ders/Sınıf ilişkisini kontrol edin veya öğrencilerin ilgili ders/sınıfa kayıtlı olduğundan emin olun.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if (!empty($students)): ?>
        <br>
        <button type="submit" class="btn">Tüm Sonuçları Kaydet</button>
    <?php endif; ?>
</form>