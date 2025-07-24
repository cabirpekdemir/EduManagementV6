<h2><?= htmlspecialchars($evaluation['name'] ?? '') ?> Sonuç Girişi</h2>
<p>
    <strong>Türü:</strong> <?= htmlspecialchars($evaluation['evaluation_type'] ?? '') ?> | 
    <strong>Tarihi:</strong> <?= htmlspecialchars($evaluation['exam_date'] ?? '') ?>
</p>

<?php if (isset($_GET['status']) && $_GET['status'] == 'saved'): ?>
    <p style="color:green; font-weight:bold;">Sonuçlar başarıyla kaydedildi!</p>
<?php endif; ?>

<?php if (!empty($students)): ?>
<form action="?module=evaluations&action=store_results" method="post">
    <input type="hidden" name="evaluation_id" value="<?= $evaluation['id'] ?>">
    <table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
        <thead>
            <tr>
                <th>Öğrenci Adı</th>
                <th>Puan (Max: <?= (int)$evaluation['max_score'] ?>)</th>
                <th>Yorum / Not</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): 
                $student_id = $student['id'];
                // Bu öğrencinin mevcut sonucunu al
                $current_result = $existing_results[$student_id] ?? null;
            ?>
            <tr>
                <td><?= htmlspecialchars($student['name']) ?></td>
                <td>
                    <input type="number" 
                           name="results[<?= $student_id ?>][score]" 
                           value="<?= htmlspecialchars($current_result['score'] ?? '') ?>"
                           step="0.01" 
                           max="<?= (int)$evaluation['max_score'] ?>"
                           style="width: 80px;">
                </td>
                <td>
                    <textarea name="results[<?= $student_id ?>][comments]" 
                              rows="1" 
                              style="width: 98%;"><?= htmlspecialchars($current_result['comments'] ?? '') ?></textarea>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <br>
    <button type="submit">Tüm Sonuçları Kaydet</button>
</form>
<?php else: ?>
    <p>Bu değerlendirme için atanmış bir sınıf bulunamadı veya sınıfta öğrenci yok.</p>
<?php endif; ?>

<br><br>
<a href="?module=evaluations&action=index">« Değerlendirme Listesine Geri Dön</a>