<h2>Mesaj Detayı</h2>

<p><strong>Gönderen:</strong> <?= htmlspecialchars($message['sender_name']) ?></p>
<p><strong>Konu:</strong> <?= htmlspecialchars($message['subject']) ?></p>
<p><strong>Tarih:</strong> <?= $message['created_at'] ?></p>

<p><strong>İçerik:</strong><br>
<?= nl2br(htmlspecialchars($message['content'])) ?></p>

<h3>Alıcılar:</h3>
<ul>
  <?php foreach ($recipients as $r): ?>
    <li><?= htmlspecialchars($r['name']) ?> (<?= $r['role'] ?>)</li>
  <?php endforeach; ?>
</ul>
