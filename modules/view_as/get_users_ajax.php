<?php
// modules/view_as/get_users_ajax.php
session_start();

// Sadece admin erişebilir
$user = $_SESSION['original_user'] ?? $_SESSION['user'] ?? null;
if (!$user || $user['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Yetkiniz yok']);
    exit;
}

require_once __DIR__ . '/../../core/database.php';

$role = $_GET['role'] ?? 'teacher';

if (!in_array($role, ['teacher', 'student', 'parent'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Geçersiz rol']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT id, name, email, phone, 
               CASE 
                   WHEN role = 'student' THEN student_number
                   ELSE NULL
               END as student_number
        FROM users 
        WHERE role = ? AND is_active = 1
        ORDER BY name ASC
        LIMIT 100
    ");
    $stmt->execute([$role]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'users' => $users], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}