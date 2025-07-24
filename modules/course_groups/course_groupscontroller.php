<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class Course_groupsController
{
    protected $db;
    protected $currentUser;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->currentUser = $_SESSION['user'] ?? null;

        if (($this->currentUser['role'] ?? 'guest') !== 'admin') {
            log_activity('ACCESS_DENIED', 'CourseGroups', null, 'Yetkisiz erişim denemesi.');
            die("⛔ Bu modüle erişim yetkiniz yok!");
        }
    }

    public function index()
    {
        $sql = "SELECT cg.*, u.name as creator_name 
                FROM course_groups cg
                JOIN users u ON cg.creator_id = u.id
                ORDER BY cg.name ASC";
        $course_groups = $this->db->select($sql);
        return ['course_groups' => $course_groups];
    }

    public function create()
    {
        $all_courses = $this->db->select("SELECT id, name FROM courses ORDER BY name ASC");
        return [
            'course_group' => null,
            'all_courses' => $all_courses,
            'selected_course_ids' => [],
            'course_select_options' => [], // Yeni grup için boş
            'isEdit' => false,
            'formAction' => 'index.php?module=course_groups&action=store'
        ];
    }

    public function store()
    {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $selected_course_ids = $_POST['course_ids'] ?? []; // Bunlar seçilen derslerin ID'leri

        if (empty($name)) {
            redirect('index.php?module=course_groups&action=create&error=empty_name');
            exit;
        }

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
                // Her ders için 'is_individually_selectable' değerini POST'tan al
                $is_selectable = isset($_POST['course_selectable'][$course_id]) ? 1 : 0;
                $item_stmt->execute([$course_group_id, (int)$course_id, $is_selectable]);
            }
        }

        log_activity('CREATE', 'CourseGroups', $course_group_id, "Ders grubu oluşturdu: '$name'");
        redirect('index.php?module=course_groups&action=index&status=created');
    }

    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $course_group = $this->db->select("SELECT * FROM course_groups WHERE id = ?", [$id])[0] ?? null;

        if (!$course_group) {
            redirect('index.php?module=course_groups&action=index&error=not_found');
            exit;
        }

        $all_courses = $this->db->select("SELECT id, name FROM courses ORDER BY name ASC");
        $selected_courses_raw = $this->db->select(
            "SELECT course_id, is_individually_selectable FROM course_group_items WHERE course_group_id = ?",
            [$id]
        );
        
        $selected_course_ids = [];
        $course_select_options = []; // ['course_id' => true/false]
        foreach($selected_courses_raw as $sc_raw){
            $selected_course_ids[] = $sc_raw['course_id'];
            $course_select_options[$sc_raw['course_id']] = (bool)$sc_raw['is_individually_selectable'];
        }

        return [
            'course_group' => $course_group,
            'all_courses' => $all_courses,
            'selected_course_ids' => $selected_course_ids,
            'course_select_options' => $course_select_options,
            'isEdit' => true,
            'formAction' => 'index.php?module=course_groups&action=update&id=' . $id
        ];
    }

    public function update()
    {
        $id = $_GET['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $selected_course_ids = $_POST['course_ids'] ?? [];

        if (empty($name) || !$id) {
            redirect('index.php?module=course_groups&action=edit&id=' . $id . '&error=empty_name');
            exit;
        }

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
        
        log_activity('UPDATE', 'CourseGroups', $id, "Ders grubunu güncelledi: '$name'");
        redirect('index.php?module=course_groups&action=index&status=updated');
    }

    public function delete()
    {
        $id = $_GET['id'] ?? 0;
        // ... (delete metodu önceki gibi kalabilir) ...
        if (!$id) {
            redirect('index.php?module=course_groups&action=index&error=missing_id');
            exit;
        }

        $course_group = $this->db->select("SELECT name FROM course_groups WHERE id = ?", [$id])[0] ?? null;
        
        $this->db->getConnection()->prepare("DELETE FROM course_groups WHERE id = ?")->execute([$id]);
        
        if ($course_group) {
            log_activity('DELETE', 'CourseGroups', $id, "Ders grubunu sildi: '{$course_group['name']}'");
        }
        redirect('index.php?module=course_groups&action=index&status=deleted');
    }
    public function list_group_students()
    {
        if (($this->currentUser['role'] ?? 'guest') !== 'admin') {
            log_activity('ACCESS_DENIED', 'CourseGroups', null, 'Öğrenci listesi indirme için yetkisiz erişim denemesi.');
            die("⛔ Bu işlemi yapma yetkiniz yok!");
        }

        $group_id = $_GET['group_id'] ?? 0;
        if (!$group_id) {
            redirect('index.php?module=course_groups&action=index&error_message=Geçersiz grup IDsi.');
            exit;
        }

        // Grup adını al (dosya adı için)
        $group = $this->db->select("SELECT name FROM course_groups WHERE id = ?", [$group_id])[0] ?? null;
        if (!$group) {
            redirect('index.php?module=course_groups&action=index&error_message=Grup bulunamadı.');
            exit;
        }
        $group_name_slug = strtolower(str_replace(' ', '_', preg_replace('/[^A-Za-z0-9 ]/', '', $group['name'])));


        // Bu gruba doğrudan veya grubun içindeki dersler aracılığıyla kaydolmuş aktif öğrencileri çek
        // student_enrollments tablosunda course_group_id tuttuğumuz için sorgu daha basit olacak.
        $sql = "SELECT DISTINCT u.id, u.name, u.email, u.okul, u.sinif 
                FROM users u
                JOIN student_enrollments se ON u.id = se.student_id
                WHERE se.course_group_id = ? AND u.role = 'student' AND se.status = 'active'
                ORDER BY u.name ASC";
        
        $students = $this->db->select($sql, [$group_id]);

        // CSV oluşturma
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $group_name_slug . '_ogrencileri_' . date('Y-m-d') . '.csv');
        $output = fopen('php://output', 'w');
        
        // CSV Başlıkları (UTF-8 BOM for Excel compatibility with Turkish characters)
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
        log_activity('EXPORT_GROUP_STUDENTS', 'CourseGroups', $group_id, "'{$group['name']}' grubuna kayıtlı öğrencileri indirdi.");
        exit;
    }
}