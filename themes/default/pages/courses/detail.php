<h2><?= e($course['name']) ?> - Kurs Detayları</h2>

<?php if (!$course): ?>
    <p>⛔ Kurs bilgisi bulunamadı.</p>
<?php else: ?>

<div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 20px;">
    <strong>Açıklama:</strong> <?= nl2br(e($course['description'])) ?><br>
    <strong>Sınıf:</strong> <?= e($course['classroom']) ?><br>
    <strong>Gün/Saat:</strong> <?= e($course['day']) ?> / <?= e($course['start_time']) ?> - <?= e($course['end_time']) ?><br>
    <strong>Öğretmen:</strong> <?= e($teacher['name'] ?? 'Atanmamış') ?>
</div>

<div style="display: flex; gap: 20px; margin-bottom: 20px;">
    <div style="background:#f0f0f0; padding:15px; flex:1; text-align:center; border-radius: 10px;">
        <h3><?= count($students) ?></h3>
        <p>Öğrenci</p>
    </div>
    <div style="background:#f0f0f0; padding:15px; flex:1; text-align:center; border-radius: 10px;">
        <h3><?= count($activities) ?></h3>
        <p>Etkinlik</p>
    </div>
</div>

<h3>Öğrenci Listesi</h3>
<?php if (count($students)): ?>
    <ul>
    <?php foreach ($students as $s): ?>
        <li><?= e($s['name']) ?></li>
    <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Bu kursa henüz öğrenci atanmamış.</p>
<?php endif; ?>

<h3>Etkinlik Listesi</h3>
<?php if (count($activities)): ?>
    <ul>
    <?php foreach ($activities as $a): ?>
        <li>
            <strong><?= e($a['title']) ?></strong> – <?= e($a['activity_date']) ?><br>
            <?= nl2br(e($a['description'])) ?>
        </li>
    <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Bu kursa ait etkinlik bulunmuyor.</p>
<?php endif; ?>

<?php endif; ?>
<div style="margin-top: 20px;">
    <a href="index.php?module=courses&action=assignStudent&id=<?= e($course['id']) ?>" 
       style="padding: 10px 15px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;">
       🎓 Öğrenci Ata
    </a>

    <a href="index.php?module=courses&action=addActivity&id=<?= e($course['id']) ?>" 
       style="padding: 10px 15px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px;">
       📅 Etkinlik Ekle
    </a>
</div>
