<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php'; // validate_password() için

class ProfileController
{
    protected $db;
    protected $currentUser;
    protected $userRole; 
    protected $userId; 

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->currentUser = $_SESSION['user'] ?? null;
        $this->userRole = $this->currentUser['role'] ?? 'guest';
        $this->userId = $this->currentUser['id'] ?? 0;

        if (!$this->currentUser) {
            redirect('index.php?module=login&action=index&error_message=' . urlencode('Giriş yapmalısınız.'));
            exit;
        }
    }

    public function index() {
        $user_id = $this->userId; 
        $user = $this->db->select("SELECT * FROM users WHERE id = ?", [$user_id])[0] ?? null;
        if (!$user) {
            redirect('index.php?module=dashboard&error_message=' . urlencode('Kullanıcı bulunamadı.'));
            exit;
        }
        $all_classes = $this->db->select("SELECT id, name FROM classes ORDER BY name ASC"); 
        
        $extra_profile_data = [];

        // Admin'e özel veriler
        if ($this->userRole === 'admin') {
            $pending_activities = $this->db->select("SELECT id, title, activity_date FROM activities WHERE status = 'pending' ORDER BY activity_date ASC");
            $extra_profile_data['pending_activities'] = $pending_activities;

            $pending_course_requests = $this->db->select("SELECT sc.id, u.name as student_name, 
                                                                 CASE sc.item_type WHEN 'course' THEN c.name ELSE cg.name END as item_name,
                                                                 sc.item_type
                                                           FROM students_course_requests sc
                                                           LEFT JOIN users u ON sc.student_id = u.id
                                                           LEFT JOIN courses c ON sc.item_id = c.id AND sc.item_type = 'course'
                                                           LEFT JOIN course_groups cg ON sc.item_id = cg.id AND sc.item_type = 'group'
                                                           WHERE sc.status = 'pending' ORDER BY sc.request_date ASC");
            $extra_profile_data['pending_course_requests'] = $pending_course_requests;
            
            $extra_profile_data['total_users'] = $this->db->select("SELECT COUNT(id) as count FROM users")[0]['count'];
            $extra_profile_data['total_students'] = $this->db->select("SELECT COUNT(id) as count FROM users WHERE role = 'student'")[0]['count'];
            $extra_profile_data['total_teachers'] = $this->db->select("SELECT COUNT(id) as count FROM users WHERE role = 'teacher'")[0]['count'];
        }

        // Öğretmene özel veriler
        if ($this->userRole === 'teacher') {
            $teacher_courses = $this->db->select("SELECT id, name FROM courses WHERE teacher_id = ? ORDER BY name ASC", [$this->userId]);
            $extra_profile_data['teacher_courses'] = $teacher_courses;

            $teacher_assigned_students_sql = "
                SELECT DISTINCT u.id, u.name, cl.name as class_name
                FROM users u
                JOIN classes cl ON u.class_id = cl.id
                JOIN course_classes ccl ON cl.id = ccl.class_id
                JOIN courses crs ON ccl.course_id = crs.id
                WHERE u.role = 'student' AND crs.teacher_id = ?
                ORDER BY u.name ASC
            ";
            $teacher_students = $this->db->select($teacher_assigned_students_sql, [$this->userId]);
            $extra_profile_data['teacher_students'] = $teacher_students;
        }

        return [
            'pageTitle' => 'Profilim', 
            'user' => $user, 
            'all_classes' => $all_classes,
            'userRole' => $this->userRole, 
            'extra_profile_data' => $extra_profile_data
        ];
    }

    public function update()
    {
        $user_id = $this->currentUser['id'];
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $tc_kimlik = $_POST['tc_kimlik'] ?? '';
        $class_id = !empty($_POST['class_id']) ? (int)$_POST['class_id'] : null;

        $current_user_data = $this->db->select("SELECT email, profile_photo FROM users WHERE id = ?", [$user_id])[0] ?? null;

        if (!$current_user_data) {
             redirect('index.php?module=profile&action=index&error_message=' . urlencode('Kullanıcı bulunamadı.'));
             exit;
        }

        if (empty($name) || empty($email) || empty($tc_kimlik)) {
            redirect('index.php?module=profile&action=index&error_message=' . urlencode('Ad, E-posta ve TC Kimlik zorunlu alanlardır.'));
            exit;
        }

        $existing_user_check = $this->db->select("SELECT id FROM users WHERE (email = ? OR tc_kimlik = ?) AND id != ?", [$email, $tc_kimlik, $user_id]);
        if (!empty($existing_user_check)) {
            redirect('index.php?module=profile&action=index&error_message=' . urlencode('E-posta veya TC Kimlik zaten başka bir kullanıcıya ait.'));
            exit;
        }

        $profile_photo = $current_user_data['profile_photo'] ?? null;
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0 && !empty($_FILES['profile_photo']['name'])) {
            if ($profile_photo && file_exists(__DIR__ . "/../../" . $profile_photo)) {
                @unlink(__DIR__ . "/../../" . $profile_photo);
            }
            $target_dir = __DIR__ . "/../../uploads/users/";
            if (!is_dir($target_dir)) { @mkdir($target_dir, 0775, true); }
            $filename = uniqid() . '-' . basename($_FILES["profile_photo"]["name"]);
            $target_file = $target_dir . $filename;
            if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
                $profile_photo = "uploads/users/" . $filename;
            } else {
                redirect('index.php?module=profile&action=index&error_message=' . urlencode('Yeni fotoğraf yüklenirken bir hata oluştu.'));
                exit;
            }
        }
        
        try {
            $sql_update = "UPDATE users SET name = ?, email = ?, class_id = ?, tc_kimlik = ?, profile_photo = ? WHERE id = ?";
            $params = [$name, $email, $class_id, $tc_kimlik, $profile_photo, $user_id];

            $stmt = $this->db->getConnection()->prepare($sql_update);
            $stmt->execute($params);

            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;

            log_activity('UPDATE', 'Profile', $user_id, "Profil bilgileri güncellendi.");
            redirect('index.php?module=profile&action=index&status_message=' . urlencode('Profil bilgileriniz başarıyla güncellendi.'));

        } catch (PDOException $e) {
            error_log("Profile Update Error: " . $e->getMessage());
            redirect('index.php?module=profile&action=index&error_message=' . urlencode('Profil güncellenirken bir veritabanı hatası oluştu.'));
        }
        exit;
    }


    /**
     * Profil sayfasındaki güvenlik sekmesini (parola değiştirme formu) gösterir.
     */
    public function security()
    {
        $user_id = $this->userId;
        $user = $this->db->select("SELECT id, name, email FROM users WHERE id = ?", [$user_id])[0] ?? null;
        if (!$user) {
            redirect('index.php?module=dashboard&error_message=' . urlencode('Kullanıcı bulunamadı.'));
            exit;
        }
        return ['pageTitle' => 'Güvenlik Ayarları', 'user' => $user];
    }

    /**
     * Kullanıcının parolasını günceller.
     */
    public function update_password()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?module=profile&action=security');
            exit;
        }

        $user_id = $this->userId;
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $user_data = $this->db->select("SELECT id, password, email, name FROM users WHERE id = ?", [$user_id])[0] ?? null;

        if (!$user_data) {
            redirect('index.php?module=profile&action=security&error_message=' . urlencode('Kullanıcı bulunamadı.'));
            exit;
        }

        if (!password_verify($current_password, $user_data['password'])) {
            redirect('index.php?module=profile&action=security&error_message=' . urlencode('Mevcut parola yanlış.'));
            exit;
        }

        if ($new_password !== $confirm_password) {
            redirect('index.php?module=profile&action=security&error_message=' . urlencode('Yeni parolalar eşleşmiyor.'));
            exit;
        }
        
        if (password_verify($new_password, $user_data['password'])) {
            redirect('index.php?module=profile&action=security&error_message=' . urlencode('Yeni parola, mevcut paroladan farklı olmalıdır.'));
            exit;
        }

        $password_errors = validate_password($new_password, $user_data['email'], $user_data['name']);
        if (!empty($password_errors)) {
            redirect('index.php?module=profile&action=security&error_message=' . urlencode(implode("<br>", $password_errors)));
            exit;
        }

        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

        try {
            $this->db->getConnection()->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed_new_password, $user_id]);
            log_activity('PASSWORD_CHANGE', 'Profile', $user_id, "Parola değiştirildi.");
            redirect('index.php?module=profile&action=security&status_message=' . urlencode('Parolanız başarıyla değiştirildi.'));
        } catch (PDOException $e) {
            error_log("Profile Password Change Error: " . $e->getMessage());
            redirect('index.php?module=profile&action=security&error_message=' . urlencode('Parola değiştirilirken bir hata oluştu.'));
        }
        exit;
    }
}