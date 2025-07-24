<?php
require_once __DIR__ . '/../../core/database.php';
$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ids'])) {
    foreach ($_POST['delete_ids'] as $val) {
        list($pid, $sid) = explode('-', $val);
        $stmt = $db->prepare("DELETE FROM parents_students WHERE parent_id = ? AND student_id = ?");
        $stmt->execute([$pid, $sid]);
    }
    header("Location: /?module=parents_students");
    exit;
}

if (isset($_GET['pid']) && isset($_GET['sid'])) {
    $stmt = $db->prepare("DELETE FROM parents_students WHERE parent_id = ? AND student_id = ?");
    $stmt->execute([$_GET['pid'], $_GET['sid']]);
    header("Location: /?module=parents_students");
    exit;
}
