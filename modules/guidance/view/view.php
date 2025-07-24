<h2>Rehberlik Seansı Detayları</h2>

<?php if (isset($session) && !empty($session)): ?>
    <div style="border: 1px solid #ddd; padding: 20px; border-radius: 5px; background-color: #f9f9f9; margin-bottom: 20px;">
        <p><strong>Öğrenci:</strong> <?= e($session['student_name']) ?></p>
        <p><strong>Rehber/Danışman:</strong> <?= e($session['counselor_name']) ?></p>
        <p><strong>Görüşme Tarihi:</strong> <?= e(date('d.m.Y', strtotime($session['session_date']))) ?></p>
        <p><strong>Konu:</strong> <?= e($session['title']) ?></p>
        <p><strong>Görüşme Notları:</strong></p>
        <div style="border: 1px solid #eee; padding: 10px; background-color: #fff; margin-bottom: 10px; white-space: pre-wrap; word-wrap: break-word;">
            <?= e($session['notes']) ?>
        </div>
        <?php if (!empty($session['next_steps'])): // next_steps sütunu guidance_notes'dan geliyordu, eğer sessions tablosunda varsa göster ?>
            <p><strong>Sonraki Adımlar:</strong></p>
            <div style="border: 1px solid #eee; padding: 10px; background-color: #fff; white-space: pre-wrap; word-wrap: break-word;">
                <?= e($session['next_steps']) ?>
            </div>
        <?php endif; ?>
        <p style="font-size: 0.85em; color: #777; margin-top: 15px;">
            Oluşturulma Tarihi: <?= e(date('d.m.Y H:i', strtotime($session['created_at']))) ?>
            <?php if (!empty($session['updated_at']) && $session['created_at'] !== $session['updated_at']): ?>
                (Son Güncelleme: <?= e(date('d.m.Y H:i', strtotime($session['updated_at']))) ?>)
            <?php endif; ?>
        </p>
    </div>

    <a href="index.php?module=guidance&action=index" class="btn">&laquo; Geri Dön</a>
    <?php 
    // Yetki kontrolü: Admin veya seansı oluşturan öğretmen/danışman düzenleyebilir/silebilir
    $can_perform_action = false;
    if (isset($_SESSION['user']['role']) && isset($_SESSION['user']['id'])) {
        if ($_SESSION['user']['role'] === 'admin') {
            $can_perform_action = true;
        } elseif ($_SESSION['user']['role'] === 'teacher' && $session['counselor_id'] == $_SESSION['user']['id']) {
            $can_perform_action = true;
        }
    }
    ?>
    <?php if ($can_perform_action): ?>
        <a href="index.php?module=guidance&action=edit&id=<?= e($session['id']) ?>" class="btn" style="margin-left:10px;">Düzenle</a>
        <a href="index.php?module=guidance&action=delete&id=<?= e($session['id']) ?>" onclick="return confirm('Bu seansı silmek istediğinize emin misiniz?')" class="btn" style="background-color: #dc3545; color: white; margin-left:10px;">Sil</a>
    <?php endif; ?>

<?php else: ?>
    <p>Seans detayları bulunamadı.</p>
<?php endif; ?>