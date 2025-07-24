<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class CoursesController
{
    protected $db;
    protected $currentUser;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->currentUser = $_SESSION['user'] ?? null;
        if (!in_array($this->currentUser['role'], ['admin', 'teacher'])) {
            die("⛔ Bu modüle erişim yetkiniz yok!");
        }
    }

    public function index()
    {
        $sql = "SELECT c.*, u.name as teacher_name
                FROM courses c
                LEFT JOIN users u ON c.teacher_id = u.id
                ORDER BY c.id DESC";
        $courses = $this->db->select($sql);

        if (!empty($courses)) {
            foreach ($courses as &$course) {
                $course['times'] = $this->db->select("SELECT * FROM course_times WHERE course_id = ?", [$course['id']]);
                // YENİ: Derse atanan sınıfları çek
                $course['classes'] = $this->db->select(
                    "SELECT cl.id, cl.name FROM classes cl JOIN course_classes cc ON cl.id = cc.class_id WHERE cc.course_id = ?",
                    [$course['id']]
                );
            }
        }
        unset($course);

        return [
            'courses' => $courses,
            'status_message' => $_GET['status'] ?? null,
            'error_message' => $_GET['error'] ?? null
        ];
    }

    public function create()
    {
        $teachers = $this->db->select("SELECT id, name FROM users WHERE role='teacher' ORDER BY name ASC");
        $classes = $this->db->select("SELECT id, name FROM classes ORDER BY name ASC"); // YENİ: Tüm sınıfları çek
        $course = [
            'id' => null, 'name' => '', 'description' => '', 'teacher_id' => '', 'classroom' => '',
            'times' => [['day'=>'', 'start_time'=>'', 'end_time'=>'']]
        ];
        return [
            'course' => $course,
            'teachers' => $teachers,
            'classes' => $classes, // YENİ: Sınıfları view'a gönder
            'selected_class_ids' => [], // YENİ: Yeni ders için boş
            'isEdit' => false,
            'formAction' => "index.php?module=courses&action=store"
        ];
    }

    public function store()
    {
        if (!in_array($this->currentUser['role'], ['admin', 'teacher'])) die("Yetkiniz yok.");
        
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $teacher_id = $_POST['teacher_id'] ?? null;
        $classroom = $_POST['classroom'] ?? '';
        $times = $_POST['times'] ?? [];
        $class_ids = $_POST['class_ids'] ?? []; // YENİ: Formdan gelen sınıf ID'leri

        if (empty($name) || empty($teacher_id)) {
            redirect('index.php?module=courses&action=create&error=empty_fields');
            exit;
        }

        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO courses (name, description, teacher_id, classroom) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$name, $description, $teacher_id, $classroom]);
        $course_id = $this->db->getConnection()->lastInsertId();

        if ($course_id) {
            // Ders zamanlarını kaydet
            if (!empty($times)) { /* ... (zaman kaydetme kodu aynı) ... */ }

            // YENİ: Seçilen sınıfları course_classes pivot tablosuna ekle
            if (!empty($class_ids)) {
                $class_stmt = $this->db->getConnection()->prepare(
                    "INSERT INTO course_classes (course_id, class_id) VALUES (?, ?)"
                );
                foreach ($class_ids as $class_id) {
                    $class_stmt->execute([$course_id, (int)$class_id]);
                }
            }
        }
        
        log_activity('CREATE', 'Courses', $course_id, "Kurs oluşturdu: '$name'");
        redirect('index.php?module=courses&action=index&status=created');
    }

    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $course = $this->db->select("SELECT * FROM courses WHERE id=?", [$id])[0] ?? null;
        if (!$course) {
             redirect('index.php?module=courses&action=index&error=not_found');
             exit;
        }
        if ($this->currentUser['role'] !== 'admin' && $course['teacher_id'] != $this->currentUser['id']) {
            die("Bu kursu düzenleme yetkiniz yok.");
        }

        $course['times'] = $this->db->select("SELECT * FROM course_times WHERE course_id=?", [$id]);
        if (empty($course['times'])) {
            $course['times'] = [['day'=>'', 'start_time'=>'', 'end_time'=>'']];
        }
        
        $teachers = $this->db->select("SELECT id, name FROM users WHERE role='teacher' ORDER BY name ASC");
        $classes = $this->db->select("SELECT id, name FROM classes ORDER BY name ASC"); // YENİ
        
        // YENİ: Bu derse atanmış sınıfların ID'lerini çek
        $selected_classes_raw = $this->db->select("SELECT class_id FROM course_classes WHERE course_id = ?", [$id]);
        $selected_class_ids = array_column($selected_classes_raw, 'class_id');

        return [
            'course' => $course, 
            'teachers' => $teachers, 
            'classes' => $classes, // YENİ
            'selected_class_ids' => $selected_class_ids, // YENİ
            'isEdit'=>true, 
            'formAction'=>"index.php?module=courses&action=update&id=$id"
        ];
    }

    public function update()
    {
        $id = $_POST['id'] ?? ($_GET['id'] ?? 0);
        if (!$id) { /* ... (hata yönetimi) ... */ }
        // ... (yetki kontrolü aynı) ...

        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $teacher_id = $_POST['teacher_id'] ?? null;
        $classroom = $_POST['classroom'] ?? '';
        $times = $_POST['times'] ?? [];
        $class_ids = $_POST['class_ids'] ?? []; // YENİ

        if (empty($name) || empty($teacher_id)) { /* ... (hata yönetimi) ... */ }

        $this->db->getConnection()->prepare(
            "UPDATE courses SET name=?, description=?, teacher_id=?, classroom=? WHERE id=?"
        )->execute([$name, $description, $teacher_id, $classroom, $id]);

        // Zamanları güncelle
        $this->db->getConnection()->prepare("DELETE FROM course_times WHERE course_id=?")->execute([$id]);
        if (!empty($times)) { /* ... (zamanları ekleme kodu aynı) ... */ }
        
        // YENİ: Sınıf atamalarını güncelle
        $this->db->getConnection()->prepare("DELETE FROM course_classes WHERE course_id=?")->execute([$id]);
        if (!empty($class_ids)) {
            $class_stmt = $this->db->getConnection()->prepare(
                "INSERT INTO course_classes (course_id, class_id) VALUES (?, ?)"
            );
            foreach ($class_ids as $class_id) {
                $class_stmt->execute([$id, (int)$class_id]);
            }
        }

        log_activity('UPDATE', 'Courses', $id, "Kursu güncelledi: '$name'");
        redirect('index.php?module=courses&action=index&status=updated');
    }
    /**
     * Bir kursu ve ilişkili tüm zamanlarını siler.
     */
    public function delete()
    {
        $id = $_GET['id'] ?? 0;
        if (!$id) {
            redirect('index.php?module=courses&action=index&error=missing_id');
            exit;
        }

        $course = $this->db->select("SELECT name, teacher_id FROM courses WHERE id=?", [$id])[0] ?? null;
        if ($this->currentUser['role'] !== 'admin' && ($course && $course['teacher_id'] != $this->currentUser['id'])) {
            log_activity('DELETE_DENIED', 'Courses', $id, 'Yetkisiz kurs silme denemesi.');
            die("Bu kursu silme yetkiniz yok.");
        }

        // `course_times` tablosundaki foreign key'de ON DELETE CASCADE ayarlı olduğu için,
        // courses'dan bir kayıt silindiğinde, ilgili course_times kayıtları da otomatik silinir.
        // Eğer bu ayar yoksa, önce `DELETE FROM course_times WHERE course_id=?` çalıştırılmalıdır.
        
        $this->db->getConnection()->prepare("DELETE FROM courses WHERE id=?")->execute([$id]);

        if ($course) {
            log_activity('DELETE', 'Courses', $id, "Kursu sildi: '{$course['name']}'");
        }
        redirect('index.php?module=courses&action=index&status=deleted');
    }
}