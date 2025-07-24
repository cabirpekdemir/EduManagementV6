<h2>Öğrenci Sınav Sonuçları: <?= e($student_info['name']) ?></h2>
<p><strong>Sınıfı:</strong> <?= e($student_info['class_name'] ?? 'Belirtilmemiş') // class_name'i controller'dan alıp eklemeliyiz ?></p>

<div style="margin-bottom: 20px;">
    <a href="index.php?module=students&action=edit&id=<?= e($student_info['id']) ?>" class="btn">&laquo; Öğrenci Profiline Dön</a>
     <?php if($userRole === 'admin' || $userRole === 'teacher'): // Belki burada öğrenciye yeni not ekleme butonu olabilir ?>
        <?php endif; ?>
</div>

<?php if (isset($_GET['status_message'])): ?>
    <p style="color: green; border:1px solid green; padding:10px;"><?= e($_GET['status_message']) === 'result_updated' ? 'Sonuç başarıyla güncellendi.' : (e($_GET['status_message']) === 'result_deleted' ? 'Sonuç başarıyla silindi.' : '') ?></p>
<?php endif; ?>

<table border="1" cellpadding="6" cellspacing="0" style="width: 100%;">
    <thead>
        <tr>
            <th>Sınav Adı</th>
            <th>Sınav Tarihi</th>
            <th>Puan (Max: ...)</th>
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
                <td><?= e($result['exam_name']) ?></td>
                <td><?= e($result['exam_date'] ? date('d.m.Y', strtotime($result['exam_date'])) : '') ?></td>
                <td><?= e($result['score'] ?? 'Girilmedi') ?> <?= isset($result['max_score']) ? ' / '.e($result['max_score']) : '' ?></td>
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
            <tr><td colspan="<?= ($userRole === 'admin' || $userRole === 'teacher') ? '7' : '6' ?>">Bu öğrenci için kayıtlı sınav sonucu bulunamadı.</td></tr>
        <?php endif; ?>
    </tbody>
</table>