<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class StudentsController
{
    private $db;
    private $currentUser;
    private $userId;
    private $userRole;

    public function __construct()
    {
        $this->db = Database::getInstance();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->currentUser = $_SESSION['user'] ?? null;
        $this->userId = $this->currentUser['id'] ?? null;
        $this->userRole = $this->currentUser['role'] ?? null;

        $allowedRoles = ['admin', 'teacher'];
        if (!in_array($this->userRole, $allowedRoles)) {
            die("⛔ Bu modüle sadece admin ve öğretmenler erişebilir!");
        }
    }

    public function index()
    {
        $params = [];
        if ($this->userRole === 'teacher') {
            $sql = "SELECT u.*, c.name as class_name FROM users u LEFT JOIN classes c ON u.class_id = c.id WHERE c.advisor_teacher_id = ? AND LOWER(u.role) = 'student' ORDER BY u.name ASC";
            $params = [$this->userId];
        } else {
            $sql = "SELECT u.*, c.name as class_name FROM users u LEFT JOIN classes c ON u.class_id = c.id WHERE LOWER(u.role) = 'student' ORDER BY u.name ASC";
        }
        $students = $this->db->select($sql, $params);
        return ['pageTitle' => 'Öğrenci Listesi', 'students' => $students];
    }

    public function list()
    {
        $params = [];
        if ($this->userRole === 'teacher') {
            $sql = "SELECT u.*, c.name as class_name FROM users u LEFT JOIN classes c ON u.class_id = c.id WHERE c.advisor_teacher_id = ? AND LOWER(u.role) = 'student' ORDER BY u.name ASC";
            $params = [$this->userId];
        } else {
            $sql = "SELECT u.*, c.name as class_name FROM users u LEFT JOIN classes c ON u.class_id = c.id WHERE LOWER(u.role) = 'student' ORDER BY u.name ASC";
        }
        $students = $this->db->select($sql, $params);
        return ['pageTitle' => 'Öğrenci Listesi', 'students' => $students];
    }

    // YENİ: Yeni öğrenci ekleme formunu gösterir
    public function create()
    {
        $classes = $this->db->select("SELECT id, name FROM classes ORDER BY name ASC");
        return ['pageTitle' => 'Yeni Öğrenci Ekle', 'classes' => $classes];
    }

    // YENİ: Yeni öğrenciyi veritabanına kaydeder
    public function store()
    {
        // Temel doğrulama
        if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['tc_kimlik'])) {
             die("Tüm zorunlu alanlar doldurulmalıdır.");
        }

        // Güvenli şifre oluşturma
        $hashed_password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO users (name, email, password, role, tc_kimlik, class_id) VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            $_POST['name'],
            $_POST['email'],
            $hashed_password,
            'student', // Rol otomatik olarak 'student' atanır
            $_POST['tc_kimlik'],
            $_POST['class_id'] ?: null
        ];

        try {
            $this->db->getConnection()->prepare($sql)->execute($params);
        } catch (PDOException $e) {
            // E-posta veya TC mükerrer ise hata yönetimi
            if ($e->errorInfo[1] == 1062) {
                die("Hata: Girdiğiniz e-posta veya TC Kimlik Numarası zaten sistemde kayıtlı.");
            } else {
                die("Veritabanı hatası: " . $e->getMessage());
            }
        }
        
        redirect('?module=students&action=index');
        exit;
    }

 public function show()
{
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo "Geçersiz ID";
        return;
    }

    $student = $this->db->select("SELECT * FROM users WHERE id = ? AND role = 'student'", [$id]);
    if (!$student) {
        echo "Öğrenci bulunamadı.";
        return;
    }

    $student = $student[0]; // tek kayıt
    include __DIR__ . '/show.php';
}

    public function showAction()
{
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo "Öğrenci bulunamadı.";
        return;
    }

    $db = Database::getInstance();

    // Öğrenci bilgileri
    $student = $db->selectOne("SELECT * FROM students WHERE id = ?", [$id]);
    if (!$student) {
        echo "Öğrenci bulunamadı.";
        return;
    }

    // Öğrencinin sınıfı
    $class = $db->selectOne("
        SELECT c.* FROM classes c
        JOIN student_classes sc ON c.id = sc.class_id
        WHERE sc.student_id = ?
    ", [$id]);

    // Aldığı dersler (ve öğretmen bilgileriyle)
    $courses = $db->select("
        SELECT co.*, u.username AS teacher_name 
        FROM students_courses sc
        JOIN courses co ON sc.course_id = co.id
        LEFT JOIN users u ON co.teacher_id = u.id
        WHERE sc.student_id = ? AND sc.status = 'onaylandı'
    ", [$id]);

    // Ders saatleri
    foreach ($courses as &$course) {
        $course['times'] = $db->select("
            SELECT day, start_time, end_time 
            FROM course_times 
            WHERE course_id = ?
        ", [$course['id']]);
    }

    // Notlar
    $grades = $db->select("
        SELECT g.*, co.name AS course_name, u.username AS teacher_name 
        FROM grades g
        JOIN courses co ON co.id = g.course_id
        JOIN users u ON u.id = g.teacher_id
        WHERE g.student_id = ?
    ", [$id]);

    // Sınav katılımı
    $exam_attendance = $db->select("
        SELECT ea.*, ex.name AS exam_name, ex.exam_date 
        FROM exam_attendance ea
        JOIN exams ex ON ex.id = ea.exam_id
        WHERE ea.student_id = ?
    ", [$id]);

    // Özel notlar (yalnızca admin/teacher görsün)
    $special_notes = null;
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'teacher') {
        $special_notes = $db->select("
            SELECT sn.*, u.username AS created_by 
            FROM students_notes sn
            JOIN users u ON u.id = sn.created_by
            WHERE sn.student_id = ?
            ORDER BY sn.created_at DESC
        ", [$id]);
    }

    require_once "modules/students/views/show.php";
}
public function edit()
{
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo "Geçersiz ID";
        return;
    }

    $user = $this->db->select("SELECT * FROM users WHERE id = ? AND role = 'student'", [$id]);
    if (!$user) {
        echo "Öğrenci bulunamadı.";
        return;
    }

    $user = $user[0]; // Tek kayıt
    include __DIR__ . '/edit.php';
}

public function update()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $email = $_POST['email'];

        $this->db->update("users", [
            'name' => $name,
            'email' => $email
        ], "id = ?", [$id]);

        header("Location: index.php?module=students");
        exit;
    }
}

    // Not: edit, update, delete metodları gelecekte eklenebilir.
}