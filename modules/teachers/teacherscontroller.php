<?php
require_once __DIR__ . '/../../core/database.php';

class TeachersController
{
    protected $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) @session_start();
    }

    public function index()
{
    $q = trim($_GET['q'] ?? '');
    
    // Gerçek kolon isimleri: graduated_school, branch (kademe users'da yok)
    $sql = "SELECT id, name, email, phone, 
                   branch, 
                   graduated_school as mezun_okul,
                   is_active 
            FROM users 
            WHERE role = 'teacher'";
    
    $params = [];
    
    if ($q !== '') {
        $sql .= " AND (name LIKE ? OR email LIKE ? OR branch LIKE ?)";
        $qLike = "%$q%";
        $params = [$qLike, $qLike, $qLike];
    }
    
    $sql .= " ORDER BY name ASC";
    
    $teachers = $this->db->select($sql, $params) ?? [];
    
    return [
        'view'     => 'teachers/view/index.php',
        'title'    => 'Öğretmenler',
        'teachers' => $teachers,
        'q'        => $q
    ];
}
public function show()
{
    $id = (int)($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        $this->flashErr('Geçersiz ID.');
        header('Location: index.php?module=teachers&action=index');
        exit;
    }

    // Tüm kolonları çek
    $teacher = $this->db->fetch("
        SELECT * FROM users WHERE id = ? AND role = 'teacher' LIMIT 1
    ", [$id]);

    if (!$teacher) {
        $this->flashErr('Öğretmen bulunamadı.');
        header('Location: index.php?module=teachers&action=index');
        exit;
    }

    // Öğretmenin dersleri
    $courses = $this->db->select("
        SELECT c.id, c.name, c.course_category,
               COUNT(DISTINCT se.student_id) as student_count
        FROM courses c
        LEFT JOIN student_enrollments se ON se.course_id = c.id AND se.status = 'active'
        WHERE c.teacher_id = ?
        GROUP BY c.id, c.name, c.course_category
        ORDER BY c.name
    ", [$id]) ?? [];

    // Öğretmenin öğrencileri
    $students = $this->db->select("
        SELECT DISTINCT u.id, u.name, u.sinif, u.okul
        FROM users u
        JOIN student_enrollments se ON se.student_id = u.id
        JOIN courses c ON c.id = se.course_id
        WHERE c.teacher_id = ? AND se.status = 'active' AND u.role = 'student'
        ORDER BY u.name
    ", [$id]) ?? [];

    return [
        'view'     => 'teachers/view/show.php',
        'title'    => 'Öğretmen Detay',
        'teacher'  => $teacher,
        'courses'  => $courses,
        'students' => $students
    ];
}
    public function create()
    {
        return [
            'view'    => 'teachers/view/form.php',
            'title'   => 'Yeni Öğretmen',
            'teacher' => null,
            'isEdit'  => false
        ];
    }

public function store()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?module=teachers&action=create');
        exit;
    }

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $tc       = preg_replace('/\D/', '', $_POST['tc_kimlik'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $branch   = trim($_POST['branch'] ?? '');
    $mezun    = trim($_POST['mezun_okul'] ?? '');

    // Validation
    if (empty($name) || empty($email)) {
        $this->flashErr('Ad Soyad ve E-posta zorunludur.');
        header('Location: index.php?module=teachers&action=create');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->flashErr('Geçerli bir e-posta adresi giriniz.');
        header('Location: index.php?module=teachers&action=create');
        exit;
    }

    if (!empty($tc) && !preg_match('/^[1-9][0-9]{10}$/', $tc)) {
        $this->flashErr('TC Kimlik No 11 haneli olmalı.');
        header('Location: index.php?module=teachers&action=create');
        exit;
    }

    // Duplicate check
    $dup = $this->db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
    if ($dup) {
        $this->flashErr('Bu email zaten kayıtlı.');
        header('Location: index.php?module=teachers&action=create');
        exit;
    }

    // ⭐ GÜÇLÜ ŞİFRE OLUŞTUR
    require_once __DIR__ . '/../../core/security.php';
    $tempPassword = Security::generateStrongPassword(12);
    $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

    try {
        $this->db->execute("
            INSERT INTO users (name, email, tc_kimlik, password, role, phone, branch, graduated_school, 
                               email_verified, must_change_password, is_active, created_at)
            VALUES (?, ?, ?, ?, 'teacher', ?, ?, ?, 0, 1, 1, NOW())
        ", [$name, $email, $tc, $hashedPassword, $phone, $branch, $mezun]);

        $newTeacherId = $this->db->lastInsertId();

        // ⭐ E-POSTA GÖNDER
        $emailSent = false;
        try {
            $emailSent = Security::sendEmail(
                $email,
                'first_login',
                [
                    'name' => $name,
                    'email' => $email,
                    'temp_password' => $tempPassword,
                    'login_link' => 'https://hipotezegitim.com.tr/edu/index.php?module=login',
                    'app_name' => 'Hipotez Eğitim'
                ]
            );
        } catch (Exception $e) {
            error_log("Mail gönderim hatası: " . $e->getMessage());
        }

        // ⭐ MESAJ GÖSTER
        if ($emailSent) {
            $this->flashOk('Öğretmen oluşturuldu. ✅ Giriş bilgileri e-postaya gönderildi.');
        } else {
            // Mail gönderilemedi, şifreyi göster
            $_SESSION['temp_password_display'] = [
                'name' => $name,
                'email' => $email,
                'password' => $tempPassword,
                'teacher_id' => $newTeacherId
            ];
            $this->flashOk('Öğretmen oluşturuldu.');
        }

        header('Location: index.php?module=teachers&action=show&id=' . $newTeacherId);
        exit;

    } catch (Exception $e) {
        $this->flashErr('Kayıt sırasında hata: ' . $e->getMessage());
        header('Location: index.php?module=teachers&action=create');
        exit;
    }
}
    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $this->flashErr('Geçersiz ID.');
            header('Location: index.php?module=teachers&action=index');
            exit;
        }

        $teacher = $this->db->fetch("
            SELECT * FROM users WHERE id = ? AND role = 'teacher' LIMIT 1
        ", [$id]);

        if (!$teacher) {
            $this->flashErr('Öğretmen bulunamadı.');
            header('Location: index.php?module=teachers&action=index');
            exit;
        }

        return [
            'view'    => 'teachers/view/form.php',
            'title'   => 'Öğretmen Düzenle',
            'teacher' => $teacher,
            'isEdit'  => true
        ];
    }

 public function update()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?module=teachers&action=index');
        exit;
    }

    $id       = (int)($_POST['id'] ?? 0);
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $tc       = preg_replace('/\D/', '', $_POST['tc_kimlik'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $branch   = trim($_POST['branch'] ?? '');
    $graduated = trim($_POST['graduated_school'] ?? '');  // ← Değişti
    $password = trim($_POST['password'] ?? '');
    $isActive = !empty($_POST['is_active']) ? 1 : 0;

    if ($id <= 0 || empty($name) || empty($email) || !preg_match('/^[1-9][0-9]{10}$/', $tc)) {
        $this->flashErr('Tüm zorunlu alanları doldurun.');
        header('Location: index.php?module=teachers&action=edit&id=' . $id);
        exit;
    }

    // Duplicate check
    $dup = $this->db->fetch("SELECT id FROM users WHERE (email = ? OR tc_kimlik = ?) AND id != ?", [$email, $tc, $id]);
    if ($dup) {
        $this->flashErr('Bu email veya TC başka bir kullanıcıda mevcut.');
        header('Location: index.php?module=teachers&action=edit&id=' . $id);
        exit;
    }

    // Profil fotoğrafı yükleme
    $photoPath = null;
    if (!empty($_FILES['profile_photo']['name'])) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = $_FILES['profile_photo']['type'];
        
        if (in_array($fileType, $allowed) && $_FILES['profile_photo']['size'] <= 2097152) {
            $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
            $newName = 'teacher_' . $id . '_' . time() . '.' . $ext;
            $uploadDir = __DIR__ . '/../../uploads/users/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadDir . $newName)) {
                $photoPath = 'uploads/users/' . $newName;
            }
        }
    }

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        if ($photoPath) {
            $this->db->execute("
                UPDATE users 
                SET name=?, email=?, tc_kimlik=?, password=?, phone=?, branch=?, graduated_school=?, profile_photo=?, is_active=?
                WHERE id=? AND role='teacher'
            ", [$name, $email, $tc, $hashedPassword, $phone, $branch, $graduated, $photoPath, $isActive, $id]);
        } else {
            $this->db->execute("
                UPDATE users 
                SET name=?, email=?, tc_kimlik=?, password=?, phone=?, branch=?, graduated_school=?, is_active=?
                WHERE id=? AND role='teacher'
            ", [$name, $email, $tc, $hashedPassword, $phone, $branch, $graduated, $isActive, $id]);
        }
    } else {
        if ($photoPath) {
            $this->db->execute("
                UPDATE users 
                SET name=?, email=?, tc_kimlik=?, phone=?, branch=?, graduated_school=?, profile_photo=?, is_active=?
                WHERE id=? AND role='teacher'
            ", [$name, $email, $tc, $phone, $branch, $graduated, $photoPath, $isActive, $id]);
        } else {
            $this->db->execute("
                UPDATE users 
                SET name=?, email=?, tc_kimlik=?, phone=?, branch=?, graduated_school=?, is_active=?
                WHERE id=? AND role='teacher'
            ", [$name, $email, $tc, $phone, $branch, $graduated, $isActive, $id]);
        }
    }

    $this->flashOk('Öğretmen güncellendi.');
     header('Location: index.php?module=teachers&action=index');
        exit;
    }

    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $this->flashErr('Geçersiz ID.');
            header('Location: index.php?module=teachers&action=index');
            exit;
        }

        $this->db->execute("DELETE FROM users WHERE id = ? AND role = 'teacher'", [$id]);
        
        $this->flashOk('Öğretmen silindi.');
        header('Location: index.php?module=teachers&action=index');
        exit;
    }

    private function generatePassword(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $pass = '';
        for ($i = 0; $i < 8; $i++) {
            $pass .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $pass;
    }

    private function flashErr(string $m) { $_SESSION['flash'] = ['type' => 'danger', 'msg' => $m]; }
    private function flashOk(string $m)  { $_SESSION['flash'] = ['type' => 'success', 'msg' => $m]; }
    public function assign_course()

{
    $teacherId = (int)($_GET['id'] ?? 0);
    
    if ($teacherId <= 0) {
        $this->flashErr('Geçersiz ID.');
        header('Location: index.php?module=teachers&action=index');
        exit;
    }

    $teacher = $this->db->fetch("
        SELECT id, name FROM users WHERE id = ? AND role = 'teacher' LIMIT 1
    ", [$teacherId]);

    if (!$teacher) {
        $this->flashErr('Öğretmen bulunamadı.');
        header('Location: index.php?module=teachers&action=index');
        exit;
    }

    // Tüm dersler
    $allCourses = $this->db->select("
        SELECT id, name, course_category 
        FROM courses 
        ORDER BY name
    ") ?? [];

    // Öğretmenin mevcut dersleri
    $teacherCourses = $this->db->select("
        SELECT id FROM courses WHERE teacher_id = ?
    ", [$teacherId]) ?? [];
    
    $assignedIds = array_column($teacherCourses, 'id');

    return [
        'view'          => 'teachers/view/assign_course.php',
        'title'         => 'Ders Ata',
        'teacher'       => $teacher,
        'allCourses'    => $allCourses,
        'assignedIds'   => $assignedIds
    ];
}

public function assign_course_store()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?module=teachers&action=index');
        exit;
    }

    $teacherId = (int)($_POST['teacher_id'] ?? 0);
    $courseIds = $_POST['course_ids'] ?? [];

    if ($teacherId <= 0 || empty($courseIds)) {
        $this->flashErr('Öğretmen ve en az bir ders seçmelisiniz.');
        header('Location: index.php?module=teachers&action=assign_course&id=' . $teacherId);
        exit;
    }

    // Seçili dersleri bu öğretmene ata
    foreach ($courseIds as $courseId) {
        $this->db->execute("
            UPDATE courses SET teacher_id = ? WHERE id = ?
        ", [$teacherId, (int)$courseId]);
    }

    $this->flashOk(count($courseIds) . ' ders öğretmene atandı.');
    header('Location: index.php?module=teachers&action=show&id=' . $teacherId);
    exit;
}

public function remove_course()
{
    $teacherId = (int)($_GET['teacher_id'] ?? 0);
    $courseId = (int)($_GET['course_id'] ?? 0);

    if ($teacherId <= 0 || $courseId <= 0) {
        $this->flashErr('Geçersiz parametreler.');
        header('Location: index.php?module=teachers&action=index');
        exit;
    }

    // Dersten öğretmeni kaldır
    $this->db->execute("
        UPDATE courses SET teacher_id = NULL WHERE id = ? AND teacher_id = ?
    ", [$courseId, $teacherId]);

    $this->flashOk('Ders öğretmenden kaldırıldı.');
    header('Location: index.php?module=teachers&action=show&id=' . $teacherId);
    exit;
}
}