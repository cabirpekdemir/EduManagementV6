<?php include __DIR__ . '/form.php'; ?>
<?php if (isset($session) && !empty($session['student_id'])): ?>
    <div style="margin-top: 20px; padding: 15px; border: 1px dashed #ccc; background-color: #f0f0f0;">
        <p>Bu seansın ait olduğu **<?= e($session['student_name'] ?? 'Öğrenci') ?>** için yeni bir takip seansı ekleyebilirsiniz.</p>
        <a href="index.php?module=guidance&action=create&student_id=<?= e($session['student_id']) ?>" class="btn" style="background-color: #007bff; color: white;">
            Bu Öğrenci İçin Yeni Seans Ekle
        </a>
    </div>
<?php endif; ?>