<?php
require_once __DIR__ . '/../../core/database.php';

class ProfileController
{
    protected $db;
    
    public function __construct() 
    { 
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) @session_start();
    }

    private function uid(): int 
    { 
        return (int)($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0); 
    }

    public function index(): array
    {
        $uid = $this->uid();
        
        if (!$uid) {
            header('Location: index.php?module=login');
            exit;
        }

        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$uid]);
        
        if (!$user) {
            header('Location: index.php?module=login');
            exit;
        }

        // Rol bazlı ek bilgiler
        $userRole = $user['role'] ?? 'guest';
        $extraData = [];

        if ($userRole === 'teacher') {
            $extraData['teacher_courses'] = $this->db->select("
                SELECT id, name FROM courses WHERE teacher_id = ? ORDER BY name
            ", [$uid]) ?? [];

            $extraData['teacher_students'] = $this->db->select("
                SELECT DISTINCT u.id, u.name, c.name as class_name
                FROM users u
                LEFT JOIN classes c ON c.id = u.class_id
                WHERE u.role = 'student' AND u.teacher_id = ?
                ORDER BY u.name
            ", [$uid]) ?? [];
        }

        if ($userRole === 'student') {
            // Sınıf listesi
            $extraData['all_classes'] = $this->db->select("
                SELECT id, name FROM classes ORDER BY name
            ") ?? [];
        }

        return [
            'view'               => 'profile/view/index.php',
            'user'               => $user,
            'userRole'           => $userRole,
            'extra_profile_data' => $extraData,
            'all_classes'        => $extraData['all_classes'] ?? []
        ];
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=profile&action=index');
            exit;
        }

        $uid = $this->uid();
        if (!$uid) {
            header('Location: index.php?module=login');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $tc = preg_replace('/\D/', '', $_POST['tc_kimlik'] ?? '');
        $classId = isset($_POST['class_id']) && $_POST['class_id'] !== '' ? (int)$_POST['class_id'] : null;

        if (empty($name)) {
            header('Location: index.php?module=profile&action=index&error_message=' . urlencode('Ad soyad zorunludur.'));
            exit;
        }

        if (!empty($tc) && !preg_match('/^[1-9][0-9]{10}$/', $tc)) {
            header('Location: index.php?module=profile&action=index&error_message=' . urlencode('TC Kimlik 11 haneli olmalı.'));
            exit;
        }

        // TC duplicate check
        if (!empty($tc)) {
            $dup = $this->db->fetch("SELECT id FROM users WHERE tc_kimlik = ? AND id != ?", [$tc, $uid]);
            if ($dup) {
                header('Location: index.php?module=profile&action=index&error_message=' . urlencode('Bu TC Kimlik kullanımda.'));
                exit;
            }
        }

        // Profil fotoğrafı
        $photoPath = null;
        if (!empty($_FILES['profile_photo']['name']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            $fileType = $_FILES['profile_photo']['type'];
            
            if (in_array($fileType, $allowed) && $_FILES['profile_photo']['size'] <= 2097152) {
                $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                $newName = 'user_' . $uid . '_' . time() . '.' . $ext;
                $uploadDir = __DIR__ . '/../../uploads/users/';
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadDir . $newName)) {
                    $photoPath = 'uploads/users/' . $newName;
                }
            }
        }

        try {
            if ($photoPath) {
                $this->db->execute("
                    UPDATE users 
                    SET name = ?, tc_kimlik = ?, class_id = ?, profile_photo = ?
                    WHERE id = ?
                ", [$name, $tc, $classId, $photoPath, $uid]);
            } else {
                $this->db->execute("
                    UPDATE users 
                    SET name = ?, tc_kimlik = ?, class_id = ?
                    WHERE id = ?
                ", [$name, $tc, $classId, $uid]);
            }

            if (isset($_SESSION['user'])) {
                $_SESSION['user']['name'] = $name;
            }

            header('Location: index.php?module=profile&action=index&status_message=' . urlencode('Profiliniz güncellendi.'));
            exit;
            
        } catch (\Throwable $e) {
            error_log('Profile update error: ' . $e->getMessage());
            header('Location: index.php?module=profile&action=index&error_message=' . urlencode('Hata oluştu.'));
            exit;
        }
    }

    public function security(): array
    {
        return ['view' => 'profile/view/security.php'];
    }

    public function update_password(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=profile&action=security');
            exit;
        }

        $uid = $this->uid();
        if (!$uid) {
            header('Location: index.php?module=login');
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            header('Location: index.php?module=profile&action=security&error_message=' . urlencode('Tüm alanları doldurun.'));
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            header('Location: index.php?module=profile&action=security&error_message=' . urlencode('Yeni şifreler eşleşmiyor.'));
            exit;
        }

        if (strlen($newPassword) < 8) {
            header('Location: index.php?module=profile&action=security&error_message=' . urlencode('Şifre en az 8 karakter olmalı.'));
            exit;
        }

        $user = $this->db->fetch("SELECT password FROM users WHERE id = ?", [$uid]);
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            header('Location: index.php?module=profile&action=security&error_message=' . urlencode('Mevcut şifre yanlış.'));
            exit;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        try {
            $this->db->execute("UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $uid]);
            header('Location: index.php?module=profile&action=security&status_message=' . urlencode('Şifreniz değiştirildi.'));
            exit;
        } catch (\Throwable $e) {
            error_log('Password update error: ' . $e->getMessage());
            header('Location: index.php?module=profile&action=security&error_message=' . urlencode('Hata oluştu.'));
            exit;
        }
    }
}