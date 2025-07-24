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

    // Not: edit, update, delete metodları gelecekte eklenebilir.
}