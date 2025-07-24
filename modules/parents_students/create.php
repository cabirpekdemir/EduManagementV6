<?php
require_once __DIR__ . '/../../core/database.php';
$db = Database::getInstance()->getConnection();

// Kullanıcıları al
$veliler = $db->query("SELECT id, name FROM users WHERE role = 'parent'")->fetchAll(PDO::FETCH_ASSOC);
$ogrenciler = $db->query("SELECT id, name FROM users WHERE role = 'student'")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parent_id = $_POST['parent_id'];
    $student_ids = $_POST['student_ids'] ?? [];

    foreach ($student_ids as $student_id) {
        $stmt = $db->prepare("INSERT INTO parents_students (parent_id, student_id) VALUES (?, ?)");
        $stmt->execute([$parent_id, $student_id]);
    }

    header("Location: /?module=parents_students");
    exit;
}

ob_start();
?>
<h2>👨‍👩‍👧‍👦 Veli - Öğrenci İlişkisi Ekle</h2>
<form method="POST">
  <label>Veli Seç:</label><br>
  <select name="parent_id" required>
    <option value="">-- Seçin --</option>
    <?php foreach ($veliler as $veli): ?>
      <option value="<?= $veli['id'] ?>"><?= htmlspecialchars($veli['name']) ?></option>
    <?php endforeach; ?>
  </select><br><br>

  <label>Öğrenciler:</label><br>
  <?php foreach ($ogrenciler as $ogr): ?>
    <input type="checkbox" name="student_ids[]" value="<?= $ogr['id'] ?>"> <?= htmlspecialchars($ogr['name']) ?><br>
  <?php endforeach; ?>

  <br><button type="submit">Ekle</button>
</form>
<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/../../themes/default/layout.php';
