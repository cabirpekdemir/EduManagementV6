<?php
require_once __DIR__ . '/../../core/database.php';

class TeachersController {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Listele
    public function index() {
        $teachers = $this->db->select("SELECT * FROM users WHERE role='teacher'");
        return compact('teachers');
    }

    // Ekleme formu
    public function create() {
        return [];
    }

    // Kaydet
    public function store() {
        $name      = $_POST['name'] ?? '';
        $email     = $_POST['email'] ?? '';
        $password  = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
        $okul      = $_POST['okul'] ?? '';
        $sinif     = $_POST['sinif'] ?? '';
        $tc_kimlik = $_POST['tc_kimlik'] ?? '';
        $profile_photo = '';

        // Profil fotoğrafı yüklemesi
        if (!empty($_FILES['profile_photo']['name'])) {
            $targetDir = __DIR__ . '/../../uploads/teachers/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $filename = uniqid() . '_' . basename($_FILES['profile_photo']['name']);
            $targetFile = $targetDir . $filename;
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetFile)) {
                $profile_photo = 'uploads/teachers/' . $filename;
            }
        }

        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO users (name, email, password, role, okul, sinif, tc_kimlik, profile_photo) VALUES (?, ?, ?, 'teacher', ?, ?, ?, ?)"
        );
        $stmt->execute([$name, $email, $password, $okul, $sinif, $tc_kimlik, $profile_photo]);
        header('Location: ?module=teachers&action=index');
        exit;
    }

    // Düzenleme formu
    public function edit() {
        $id = $_GET['id'] ?? 0;
        $teacher = $this->db->select("SELECT * FROM users WHERE id=? AND role='teacher'", [$id])[0] ?? null;
        return compact('teacher');
    }

    // Güncelle
    public function update() {
        $id        = $_GET['id'] ?? 0;
        $name      = $_POST['name'] ?? '';
        $email     = $_POST['email'] ?? '';
        $okul      = $_POST['okul'] ?? '';
        $sinif     = $_POST['sinif'] ?? '';
        $tc_kimlik = $_POST['tc_kimlik'] ?? '';
        $profile_photo = $_POST['current_photo'] ?? '';

        // Yeni fotoğraf yüklendiyse güncelle
        if (!empty($_FILES['profile_photo']['name'])) {
            $targetDir = __DIR__ . '/../../uploads/teachers/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $filename = uniqid() . '_' . basename($_FILES['profile_photo']['name']);
            $targetFile = $targetDir . $filename;
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetFile)) {
                $profile_photo = 'uploads/teachers/' . $filename;
            }
        }
        $stmt = $this->db->getConnection()->prepare(
            "UPDATE users SET name=?, email=?, okul=?, sinif=?, tc_kimlik=?, profile_photo=? WHERE id=? AND role='teacher'"
        );
        $stmt->execute([$name, $email, $okul, $sinif, $tc_kimlik, $profile_photo, $id]);
        header('Location: ?module=teachers&action=index');
        exit;
    }

    // Sil
   public function delete() {
    $id = $_GET['id'];
    // 1. Kullanıcıyı al
    $teacher = $this->db->select("SELECT * FROM users WHERE id=? AND role='teacher'", [$id])[0] ?? null;
    if ($teacher) {
        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO erased_users (user_id, name, email, role, okul, sinif, tc_kimlik, extra_data) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $teacher['id'], $teacher['name'], $teacher['email'], $teacher['role'],
            $teacher['okul'], $teacher['sinif'], $teacher['tc_kimlik'], json_encode($teacher)
        ]);
        $this->db->getConnection()->prepare("DELETE FROM users WHERE id=? AND role='teacher'")->execute([$id]);
        // Ek olarak, teacher ilişkili diğer kayıtlar varsa onlarla ilgili silme işlemleri burada yapılabilir.
    }
    header('Location: ?module=teachers&action=index');
    exit;
}
}
