<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class Course_groupsController
{
    protected $db;
    protected $currentUser;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) @session_start();

        // DB elde et
        if (isset($GLOBALS['db']) && $GLOBALS['db'] instanceof Database) {
            $this->db = $GLOBALS['db'];
        } elseif (class_exists('Database') && method_exists('Database', 'getInstance')) {
            $this->db = Database::getInstance();
        } else {
            throw new RuntimeException('Database bağlantısı kurulamadı.');
        }

        // Kullanıcı / rol
        $this->currentUser = $_SESSION['user'] ?? [];
        $role = strtolower($this->currentUser['role'] ?? ($_SESSION['role'] ?? 'guest'));
        if ($role === 'öğretmen' || $role === 'ogretmen') $role = 'teacher';
        if ($role === 'öğrenci'  || $role === 'ogrenci')  $role = 'student';
        $this->currentUser['role'] = $role;

        // Yetki kontrolü: admin ve teacher
        $allowed = ['admin', 'teacher'];
        if (!in_array($role, $allowed, true)) {
            if (function_exists('log_activity')) {
                @log_activity('ACCESS_DENIED', 'CourseGroups', null, 'Yetkisiz erişim denemesi.');
            }
            die("⛔ Bu modüle erişim yetkiniz yok!");
        }
    }

    private function flashErr(string $m){ $_SESSION['form_error'] = $m; }
    private function flashOk(string $m){ $_SESSION['form_ok'] = $m; }

    private function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    private function validateCsrfToken(): bool {
        $token = $_POST['csrf_token'] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    public function index()
    {
        $sql = "SELECT cg.*, u.name as creator_name 
                FROM course_groups cg
                JOIN users u ON cg.creator_id = u.id
                ORDER BY cg.name ASC";
        $course_groups = $this->db->select($sql) ?? [];
        
        return [
            'view' => 'course_groups/view/index.php',
            'title' => 'Ders Grupları',
            'course_groups' => $course_groups
        ];
    }

    public function create()
    {
        $all_courses = $this->db->select("SELECT id, name FROM courses ORDER BY name ASC") ?? [];
        
        return [
            'view' => 'course_groups/view/create.php',
            'title' => 'Yeni Ders Grubu',
            'course_group' => null,
            'all_courses' => $all_courses,
            'selected_course_ids' => [],
            'course_select_options' => [],
            'isEdit' => false,
            'formAction' => 'index.php?module=course_groups&action=store',
            'csrf_token' => $this->generateCsrfToken()
        ];
    }

    public function store()
    {
        if (!$this->validateCsrfToken()) {
            $this->flashErr('Güvenlik hatası');
            redirect('index.php?module=course_groups&action=create');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $selected_course_ids = $_POST['course_ids'] ?? [];

        if (empty($name)) {
            $this->flashErr('Grup adı boş bırakılamaz');
            redirect('index.php?module=course_groups&action=create');
            exit;
        }

        try {
            $stmt = $this->db->getConnection()->prepare(
                "INSERT INTO course_groups (name, description, creator_id) VALUES (?, ?, ?)"
            );
            $stmt->execute([$name, $description, $this->currentUser['id']]);
            $course_group_id = $this->db->getConnection()->lastInsertId();

            if ($course_group_id && !empty($selected_course_ids)) {
                $item_stmt = $this->db->getConnection()->prepare(
                    "INSERT INTO course_group_items (course_group_id, course_id, is_individually_selectable) VALUES (?, ?, ?)"
                );
                foreach ($selected_course_ids as $course_id) {
                    $is_selectable = isset($_POST['course_selectable'][$course_id]) ? 1 : 0;
                    $item_stmt->execute([$course_group_id, (int)$course_id, $is_selectable]);
                }
            }

            if (function_exists('log_activity')) {
                log_activity('CREATE', 'CourseGroups', $course_group_id, "Ders grubu oluşturdu: '$name'");
            }
            
            $this->flashOk('Ders grubu oluşturuldu');
            redirect('index.php?module=course_groups&action=index');
        } catch (\Exception $e) {
            $this->flashErr('Hata: ' . $e->getMessage());
            redirect('index.php?module=course_groups&action=create');
        }
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $this->flashErr('Geçersiz ID');
            redirect('index.php?module=course_groups&action=index');
            exit;
        }

        $course_group = $this->db->fetch("SELECT * FROM course_groups WHERE id = ?", [$id]);

        if (!$course_group) {
            $this->flashErr('Ders grubu bulunamadı');
            redirect('index.php?module=course_groups&action=index');
            exit;
        }

        $all_courses = $this->db->select("SELECT id, name FROM courses ORDER BY name ASC") ?? [];
        $selected_courses_raw = $this->db->select(
            "SELECT course_id, is_individually_selectable FROM course_group_items WHERE course_group_id = ?",
            [$id]
        ) ?? [];
        
        $selected_course_ids = [];
        $course_select_options = [];
        foreach($selected_courses_raw as $sc_raw){
            $selected_course_ids[] = $sc_raw['course_id'];
            $course_select_options[$sc_raw['course_id']] = (bool)$sc_raw['is_individually_selectable'];
        }

        return [
            'view' => 'course_groups/view/edit.php',
            'title' => 'Ders Grubu Düzenle',
            'course_group' => $course_group,
            'all_courses' => $all_courses,
            'selected_course_ids' => $selected_course_ids,
            'course_select_options' => $course_select_options,
            'isEdit' => true,
            'formAction' => 'index.php?module=course_groups&action=update&id=' . $id,
            'csrf_token' => $this->generateCsrfToken()
        ];
    }

    public function update()
    {
        if (!$this->validateCsrfToken()) {
            $this->flashErr('Güvenlik hatası');
            redirect('index.php?module=course_groups&action=index');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $selected_course_ids = $_POST['course_ids'] ?? [];

        if (empty($name) || $id <= 0) {
            $this->flashErr('Grup adı boş bırakılamaz');
            redirect('index.php?module=course_groups&action=edit&id=' . $id);
            exit;
        }

        try {
            $stmt = $this->db->getConnection()->prepare(
                "UPDATE course_groups SET name = ?, description = ? WHERE id = ?"
            );
            $stmt->execute([$name, $description, $id]);

            $this->db->getConnection()->prepare("DELETE FROM course_group_items WHERE course_group_id = ?")->execute([$id]);

            if (!empty($selected_course_ids)) {
                $item_stmt = $this->db->getConnection()->prepare(
                    "INSERT INTO course_group_items (course_group_id, course_id, is_individually_selectable) VALUES (?, ?, ?)"
                );
                foreach ($selected_course_ids as $course_id) {
                    $is_selectable = isset($_POST['course_selectable'][$course_id]) ? 1 : 0;
                    $item_stmt->execute([$id, (int)$course_id, $is_selectable]);
                }
            }
            
            if (function_exists('log_activity')) {
                log_activity('UPDATE', 'CourseGroups', $id, "Ders grubunu güncelledi: '$name'");
            }
            
            $this->flashOk('Ders grubu güncellendi');
            redirect('index.php?module=course_groups&action=index');
        } catch (\Exception $e) {
            $this->flashErr('Hata: ' . $e->getMessage());
            redirect('index.php?module=course_groups&action=edit&id=' . $id);
        }
    }

    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $this->flashErr('Geçersiz ID');
            redirect('index.php?module=course_groups&action=index');
            exit;
        }

        try {
            $course_group = $this->db->fetch("SELECT name FROM course_groups WHERE id = ?", [$id]);
            
            $this->db->getConnection()->prepare("DELETE FROM course_group_items WHERE course_group_id = ?")->execute([$id]);
            $this->db->getConnection()->prepare("DELETE FROM course_groups WHERE id = ?")->execute([$id]);
            
            if ($course_group && function_exists('log_activity')) {
                log_activity('DELETE', 'CourseGroups', $id, "Ders grubunu sildi: '{$course_group['name']}'");
            }
            
            $this->flashOk('Ders grubu silindi');
        } catch (\Exception $e) {
            $this->flashErr('Hata: ' . $e->getMessage());
        }
        
        redirect('index.php?module=course_groups&action=index');
    }

    public function list_group_students()
    {
        if (($this->currentUser['role'] ?? 'guest') !== 'admin') {
            if (function_exists('log_activity')) {
                log_activity('ACCESS_DENIED', 'CourseGroups', null, 'Öğrenci listesi indirme için yetkisiz erişim denemesi.');
            }
            die("⛔ Bu işlemi yapma yetkiniz yok!");
        }

        $group_id = (int)($_GET['group_id'] ?? 0);
        if (!$group_id) {
            $this->flashErr('Geçersiz grup ID\'si');
            redirect('index.php?module=course_groups&action=index');
            exit;
        }

        $group = $this->db->fetch("SELECT name FROM course_groups WHERE id = ?", [$group_id]);
        if (!$group) {
            $this->flashErr('Grup bulunamadı');
            redirect('index.php?module=course_groups&action=index');
            exit;
        }
        
        $group_name_slug = strtolower(str_replace(' ', '_', preg_replace('/[^A-Za-z0-9 ]/', '', $group['name'])));

        $sql = "SELECT DISTINCT u.id, u.name, u.email, u.okul, u.sinif 
                FROM users u
                JOIN student_enrollments se ON u.id = se.student_id
                WHERE se.course_group_id = ? AND u.role = 'student' AND se.status = 'active'
                ORDER BY u.name ASC";
        
        $students = $this->db->select($sql, [$group_id]) ?? [];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $group_name_slug . '_ogrencileri_' . date('Y-m-d') . '.csv');
        $output = fopen('php://output', 'w');
        
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['Ogrenci ID', 'Ad Soyad', 'E-posta', 'Okul', 'Sinif']);

        if (!empty($students)) {
            foreach ($students as $student) {
                fputcsv($output, [
                    $student['id'],
                    $student['name'],
                    $student['email'] ?? '',
                    $student['okul'] ?? '',
                    $student['sinif'] ?? ''
                ]);
            }
        }
        
        fclose($output);
        
        if (function_exists('log_activity')) {
            log_activity('EXPORT_GROUP_STUDENTS', 'CourseGroups', $group_id, "'{$group['name']}' grubuna kayıtlı öğrencileri indirdi.");
        }
        exit;
    }
}