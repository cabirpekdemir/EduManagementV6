<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class Course_groupsController // Sınıf adını dosya adına uygun hale getirdim
{
    protected $db;
    protected $currentUser;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->currentUser = $_SESSION['user'] ?? null;

        // Bu modüle sadece adminler erişebilir
        if (($this->currentUser['role'] ?? 'guest') !== 'admin') {
            log_activity('ACCESS_DENIED', 'CourseGroups', null, 'Yetkisiz erişim denemesi.');
            die("⛔ Bu modüle erişim yetkiniz yok!");
        }
    }

    /**
     * Ders gruplarını listeler.
     */
    public function index()
    {
        $sql = "SELECT cg.*, u.name as creator_name 
                FROM course_groups cg
                JOIN users u ON cg.creator_id = u.id
                ORDER BY cg.name ASC";
        $course_groups = $this->db->select($sql);
        return ['course_groups' => $course_groups];
    }

    /**
     * Yeni ders grubu oluşturma formunu gösterir.
     * Ayrıca mevcut dersleri de forma gönderir, böylece grup oluşturulurken dersler seçilebilir.
     */
    public function create()
    {
        $all_courses = $this->db->select("SELECT id, name FROM courses ORDER BY name ASC");
        return [
            'course_group' => null, // Yeni kayıt için boş
            'all_courses' => $all_courses,
            'selected_course_ids' => [], // Yeni kayıt için boş
            'isEdit' => false,
            'formAction' => 'index.php?module=course_groups&action=store'
        ];
    }

    /**
     * Yeni bir ders grubunu ve seçilen derslerini kaydeder.
     */
    public function store()
    {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $selected_course_ids = $_POST['course_ids'] ?? [];

        if (empty($name)) {
            // Hata yönetimi eklenebilir, şimdilik basitçe geri yönlendiriyoruz.
            redirect('index.php?module=course_groups&action=create&error=empty_name');
            exit;
        }

        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO course_groups (name, description, creator_id) VALUES (?, ?, ?)"
        );
        $stmt->execute([$name, $description, $this->currentUser['id']]);
        $course_group_id = $this->db->getConnection()->lastInsertId();

        // Seçilen dersleri course_group_items tablosuna ekle
        if ($course_group_id && !empty($selected_course_ids)) {
            $item_stmt = $this->db->getConnection()->prepare(
                "INSERT INTO course_group_items (course_group_id, course_id) VALUES (?, ?)"
            );
            foreach ($selected_course_ids as $course_id) {
                $item_stmt->execute([$course_group_id, $course_id]);
            }
        }

        log_activity('CREATE', 'CourseGroups', $course_group_id, "Ders grubu oluşturdu: '$name'");
        redirect('index.php?module=course_groups&action=index&status=created');
    }

    /**
     * Mevcut bir ders grubunu düzenleme formunu gösterir.
     * Grubun mevcut derslerini ve tüm dersleri forma gönderir.
     */
    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $course_group = $this->db->select("SELECT * FROM course_groups WHERE id = ?", [$id])[0] ?? null;

        if (!$course_group) {
            redirect('index.php?module=course_groups&action=index&error=not_found');
            exit;
        }

        $all_courses = $this->db->select("SELECT id, name FROM courses ORDER BY name ASC");
        $selected_courses_raw = $this->db->select("SELECT course_id FROM course_group_items WHERE course_group_id = ?", [$id]);
        $selected_course_ids = array_column($selected_courses_raw, 'course_id');

        return [
            'course_group' => $course_group,
            'all_courses' => $all_courses,
            'selected_course_ids' => $selected_course_ids,
            'isEdit' => true,
            'formAction' => 'index.php?module=course_groups&action=update&id=' . $id
        ];
    }

    /**
     * Mevcut bir ders grubunu ve seçilen derslerini günceller.
     */
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

        // Önce mevcut ders bağlantılarını sil
        $this->db->getConnection()->prepare("DELETE FROM course_group_items WHERE course_group_id = ?")->execute([$id]);

        // Sonra yeni seçilen dersleri ekle
        if (!empty($selected_course_ids)) {
            $item_stmt = $this->db->getConnection()->prepare(
                "INSERT INTO course_group_items (course_group_id, course_id) VALUES (?, ?)"
            );
            foreach ($selected_course_ids as $course_id) {
                $item_stmt->execute([$id, $course_id]);
            }
        }
        
        log_activity('UPDATE', 'CourseGroups', $id, "Ders grubunu güncelledi: '$name'");
        redirect('index.php?module=course_groups&action=index&status=updated');
    }

    /**
     * Bir ders grubunu siler.
     * İlişkili course_group_items kayıtları CASCADE ile silinecektir.
     */
    public function delete()
    {
        $id = $_GET['id'] ?? 0;
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
}