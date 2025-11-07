<?php
require_once __DIR__ . '/../../core/database.php';

class ClassesController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) @session_start();
    }

    private function currentUserId(): int
    {
        return (int)($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0);
    }

    private function currentRole(): string
    {
        return $_SESSION['user']['role'] ?? $_SESSION['role'] ?? 'guest';
    }

    private function flashSuccess(string $msg): void
    {
        $_SESSION['flash_success'] = $msg;
    }

    private function flashError(string $msg): void
    {
        $_SESSION['flash_error'] = $msg;
    }

    /* ==================== INDEX/LIST ==================== */
    public function index(): array
    {
        return $this->list();
    }

    public function list(): array
    {
        $classes = $this->db->select("
            SELECT c.*, 
                   u.name AS advisor_name,
                   (SELECT COUNT(*) FROM users WHERE class_id = c.id AND role = 'student') AS student_count
            FROM classes c
            LEFT JOIN users u ON c.advisor_teacher_id = u.id
            ORDER BY c.name ASC
        ") ?? [];

        return [
            'view'      => 'classes/view/list.php',
            'title'     => 'Sınıflar',
            'classes'   => $classes,
            'canDelete' => ($this->currentRole() === 'admin')
        ];
    }

    /* ==================== CREATE ==================== */
    public function create(): array
    {
        $teachers = $this->db->select("
            SELECT id, name FROM users 
            WHERE role='teacher' AND is_active=1 
            ORDER BY name ASC
        ") ?? [];

        return [
            'view'              => 'classes/view/form.php',
            'title'             => 'Yeni Sınıf',
            'class'             => null,
            'teachers'          => $teachers,
            'selectedAdvisor'   => null,
            'isEdit'            => false,
            'formAction'        => 'index.php?module=classes&action=store'
        ];
    }

/* ==================== STORE ==================== */
public function store(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?module=classes&action=create');
        exit;
    }

    $data = [
        'name'               => trim($_POST['name'] ?? ''),
        'description'        => trim($_POST['description'] ?? ''),
        'advisor_teacher_id' => !empty($_POST['advisor_teacher_id']) ? (int)$_POST['advisor_teacher_id'] : null
    ];

    $errors = [];

    if (empty($data['name'])) {
        $errors['name'] = 'Sınıf adı zorunludur.';
    }

    if (!empty($errors)) {
        $_SESSION['old_input'] = $data;
        $_SESSION['validation_errors'] = $errors;
        header('Location: index.php?module=classes&action=create');
        exit;
    }

    // Duplicate check
    $dupName = $this->db->fetch("SELECT id FROM classes WHERE name = ?", [$data['name']]);
    if ($dupName) {
        $errors['name'] = 'Bu sınıf adı zaten kullanımda.';
    }

    if (!empty($errors)) {
        $_SESSION['old_input'] = $data;
        $_SESSION['validation_errors'] = $errors;
        header('Location: index.php?module=classes&action=create');
        exit;
    }

    try {
        // DÜZELTME: created_at kaldırıldı
        $this->db->execute("
            INSERT INTO classes (name, description, advisor_teacher_id)
            VALUES (?, ?, ?)
        ", [$data['name'], $data['description'], $data['advisor_teacher_id']]);

        unset($_SESSION['old_input'], $_SESSION['validation_errors']);

        $this->flashSuccess('Sınıf oluşturuldu.');
        header('Location: index.php?module=classes&action=list');
        exit;

    } catch (\Throwable $e) {
        error_log('Class store error: ' . $e->getMessage());
        $_SESSION['old_input'] = $data;
        $_SESSION['validation_errors'] = ['general' => 'Veritabanı hatası: ' . $e->getMessage()];
        header('Location: index.php?module=classes&action=create');
        exit;
    }
}
    /* ==================== EDIT ==================== */
    public function edit(): array
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('Geçersiz ID.');
            header('Location: index.php?module=classes&action=list');
            exit;
        }

        $class = $this->db->fetch("SELECT * FROM classes WHERE id = ?", [$id]);

        if (!$class) {
            $this->flashError('Sınıf bulunamadı.');
            header('Location: index.php?module=classes&action=list');
            exit;
        }

        $teachers = $this->db->select("
            SELECT id, name FROM users 
            WHERE role='teacher' AND is_active=1 
            ORDER BY name
        ") ?? [];

        // Old input varsa kullan
        $oldInput = $_SESSION['old_input'] ?? [];
        $errors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['old_input'], $_SESSION['validation_errors']);

        $classData = [
            'id'                 => (int)$class['id'],
            'name'               => $class['name'] ?? '',
            'description'        => $class['description'] ?? '',
            'advisor_teacher_id' => $class['advisor_teacher_id'] ?? null
        ];

        if (!empty($oldInput)) {
            $classData = array_merge($classData, $oldInput);
        }

        return [
            'view'            => 'classes/view/form.php',
            'title'           => 'Sınıf Düzenle',
            'class'           => $classData,
            'teachers'        => $teachers,
            'selectedAdvisor' => $classData['advisor_teacher_id'] ?? null,
            'isEdit'          => true,
            'formAction'      => 'index.php?module=classes&action=update&id=' . $id,
            'errors'          => $errors
        ];
    }

    /* ==================== UPDATE ==================== */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=classes&action=list');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('Geçersiz ID.');
            header('Location: index.php?module=classes&action=list');
            exit;
        }

        $class = $this->db->fetch("SELECT * FROM classes WHERE id = ?", [$id]);

        if (!$class) {
            $this->flashError('Sınıf bulunamadı.');
            header('Location: index.php?module=classes&action=list');
            exit;
        }

        $data = [
            'name'               => trim($_POST['name'] ?? ''),
            'description'        => trim($_POST['description'] ?? ''),
            'advisor_teacher_id' => !empty($_POST['advisor_teacher_id']) ? (int)$_POST['advisor_teacher_id'] : null
        ];

        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Sınıf adı zorunludur.';
        }

        if (!empty($errors)) {
            $_SESSION['old_input'] = $data;
            $_SESSION['validation_errors'] = $errors;
            header('Location: index.php?module=classes&action=edit&id=' . $id);
            exit;
        }

        // Duplicate check
        $dupName = $this->db->fetch("SELECT id FROM classes WHERE name = ? AND id != ?", [$data['name'], $id]);
        if ($dupName) {
            $errors['name'] = 'Bu sınıf adı başka bir sınıfta kullanılıyor.';
        }

        if (!empty($errors)) {
            $_SESSION['old_input'] = $data;
            $_SESSION['validation_errors'] = $errors;
            header('Location: index.php?module=classes&action=edit&id=' . $id);
            exit;
        }

        try {
            $this->db->execute("
                UPDATE classes SET
                    name=?, description=?, advisor_teacher_id=?
                WHERE id=?
            ", [$data['name'], $data['description'], $data['advisor_teacher_id'], $id]);

            unset($_SESSION['old_input'], $_SESSION['validation_errors']);

            $this->flashSuccess('Sınıf güncellendi.');
            header('Location: index.php?module=classes&action=show&id=' . $id);
            exit;

        } catch (\Throwable $e) {
            error_log('Class update error: ' . $e->getMessage());
            $_SESSION['old_input'] = $data;
            $_SESSION['validation_errors'] = ['general' => 'Veritabanı hatası: ' . $e->getMessage()];
            header('Location: index.php?module=classes&action=edit&id=' . $id);
            exit;
        }
    }

    /* ==================== SHOW ==================== */
    public function show(): array
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('Geçersiz ID.');
            header('Location: index.php?module=classes&action=list');
            exit;
        }

        $class = $this->db->fetch("
            SELECT c.*, u.name AS advisor_name
            FROM classes c
            LEFT JOIN users u ON c.advisor_teacher_id = u.id
            WHERE c.id = ?
        ", [$id]);

        if (!$class) {
            $this->flashError('Sınıf bulunamadı.');
            header('Location: index.php?module=classes&action=list');
            exit;
        }

        // Sınıftaki öğrenciler
        $students = $this->db->select("
            SELECT id, name, email, tc_kimlik
            FROM users
            WHERE class_id = ? AND role = 'student'
            ORDER BY name
        ", [$id]) ?? [];

        // Sınıfın dersleri
        $courses = $this->db->select("
            SELECT c.id, c.name, c.code, u.name AS teacher_name
            FROM courses c
            LEFT JOIN users u ON u.id = c.teacher_id
            JOIN course_classes cc ON cc.course_id = c.id
            WHERE cc.class_id = ?
            ORDER BY c.name
        ", [$id]) ?? [];

        return [
            'view'      => 'classes/view/show.php',
            'title'     => 'Sınıf Detay',
            'class'     => $class,
            'students'  => $students,
            'courses'   => $courses,
            'canDelete' => ($this->currentRole() === 'admin')
        ];
    }

    /* ==================== DELETE ==================== */
    public function delete(): void
    {
        if ($this->currentRole() !== 'admin') {
            $this->flashError('Silme yetkiniz yok.');
            header('Location: index.php?module=classes&action=list');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('Geçersiz ID.');
            header('Location: index.php?module=classes&action=list');
            exit;
        }

        try {
            // İlişkili kayıtları kontrol et
            $studentCount = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE class_id = ?", [$id]);
            if ($studentCount['count'] > 0) {
                $this->flashError('Bu sınıfta öğrenci bulunduğu için silinemez. Önce öğrencileri başka sınıfa atayın.');
                header('Location: index.php?module=classes&action=list');
                exit;
            }

            // İlişkili kayıtları sil
            $this->db->execute("DELETE FROM course_classes WHERE class_id = ?", [$id]);
            
            // Sınıfı sil
            $this->db->execute("DELETE FROM classes WHERE id = ?", [$id]);

            $this->flashSuccess('Sınıf silindi.');
            header('Location: index.php?module=classes&action=list');
            exit;

        } catch (\Throwable $e) {
            error_log('Class delete error: ' . $e->getMessage());
            $this->flashError('Silme hatası: ' . $e->getMessage());
            header('Location: index.php?module=classes&action=list');
            exit;
        }
    }
}