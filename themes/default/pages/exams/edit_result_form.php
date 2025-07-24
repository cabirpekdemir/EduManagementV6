<h2>Sınav Sonucunu Düzenle</h2>

<p><strong>Öğrenci:</strong> <?= e($result['student_name']) ?></p>
<p><strong>Sınav:</strong> <?= e($result['exam_name']) ?> (Max Puan: <?= e($result['max_score'] ?? 'N/A') ?>)</p>

<form method="post" action="<?= e($formAction) ?>">
    <input type="hidden" name="result_id" value="<?= e($result['id']) ?>">
    
    <div>
        <label for="score">Puan:</label><br>
        <input type="number" id="score" name="score" value="<?= e($result['score'] ?? '') ?>" 
               step="0.01" <?php if(isset($result['max_score'])): ?>max="<?= e($result['max_score']) ?>"<?php endif; ?> min="0" 
               style="width: 100px;">
    </div>
    <br>
    <div>
        <label for="grade">Harf Notu/Değerlendirme:</label><br>
        <input type="text" id="grade" name="grade" value="<?= e($result['grade'] ?? '') ?>" style="width: 150px;">
    </div>
    <br>
    <div>
        <label for="comments">Yorumlar:</label><br>
        <textarea id="comments" name="comments" rows="3" style="width: 98%;"><?= e($result['comments'] ?? '') ?></textarea>
    </div>
    <br>
    <button type="submit" class="btn">Sonucu Güncelle</button>
    <a href="javascript:history.back()" class="btn" style="margin-left:10px;">Geri Dön</a>
</form>