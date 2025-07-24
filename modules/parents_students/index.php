<?php
require_once __DIR__ . '/../../core/database.php';
$db = Database::getInstance()->getConnection();

$relations = $db->query("SELECT ps.parent_id, ps.student_id, p.name AS parent_name, s.name AS student_name 
                         FROM parents_students ps
                         JOIN users p ON ps.parent_id = p.id
                         JOIN users s ON ps.student_id = s.id")->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<h2>📋 Veli - Öğrenci Listesi</h2>
<form method="POST" action="?module=parents_students&action=delete">
  <table border="1" cellpadding="5">
    <tr>
      <th>Seç</th>
      <th>Veli</th>
      <th>Öğrenci</th>
      <th>Sil</th>
    </tr>
    <?php foreach ($relations as $rel): ?>
      <tr>
        <td><input type="checkbox" name="delete_ids[]" value="<?= $rel['parent_id'] ?>-<?= $rel['student_id'] ?>"></td>
        <td><?= htmlspecialchars($rel['parent_name']) ?></td>
        <td><?= htmlspecialchars($rel['student_name']) ?></td>
        <td>
          <a href="?module=parents_students&action=delete&pid=<?= $rel['parent_id'] ?>&sid=<?= $rel['student_id'] ?>" onclick="return confirm('Silmek istediğinize emin misiniz?')">🗑️</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
  <br><button type="submit" onclick="return confirm('Seçilenleri silmek istediğinize emin misiniz?')">Seçilenleri Sil</button>
</form>
<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/../../themes/default/layout.php';
