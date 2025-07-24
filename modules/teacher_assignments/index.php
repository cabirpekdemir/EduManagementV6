<h2>Verilen Ödevler</h2>
<table border="1" cellpadding="5">
<tr><th>Ders</th><th>Başlık</th><th>Açıklama</th><th>Teslim Tarihi</th></tr>
<?php foreach ($assignments as $a): ?>
<tr>
  <td><?= $a['course_id'] ?></td>
  <td><?= htmlspecialchars($a['title']) ?></td>
  <td><?= nl2br(htmlspecialchars($a['description'])) ?></td>
  <td><?= $a['due_date'] ?></td>
</tr>
<?php endforeach; ?>
</table>
