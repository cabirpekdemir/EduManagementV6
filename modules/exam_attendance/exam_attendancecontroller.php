<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class Exam_AttendanceController
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
        if (!in_array($this->userRole, ['admin', 'teacher'])) {
            die("⛔ Bu modüle sadece adminler ve öğretmenler erişebilir!");
        }
    }

    public function index()
    {
        // Değerlendirmeleri çekiyoruz, 'evaluations' tablosundan ve 'exam_date' sütununu kullanıyoruz
        $evaluations = $this->db->select("SELECT id, name, exam_date FROM evaluations WHERE status = 'active' ORDER BY exam_date DESC");
        return ['evaluations' => $evaluations, 'error_message' => $_GET['error'] ?? null];
    }

    public function take()
    {
        $evaluation_id = $_GET['exam_id'] ?? 0; // URL'den hala 'exam_id' olarak gelebilir, içeride evaluation_id'ye çeviriyoruz
        if (!$evaluation_id) {
            redirect('index.php?module=exam_attendance&action=index&error=missing_params');
            exit;
        }

        // Değerlendirme detaylarını evaluations tablosundan çekiyoruz
        $evaluation = $this->db->select("SELECT * FROM evaluations WHERE id = ?", [$evaluation_id])[0] ?? null;
        if (!$evaluation) {
            redirect('index.php?module=exam_attendance&action=index&error=not_found');
            exit;
        }

        $student_sql = "SELECT u.id, u.name, cl.name as class_name FROM users u LEFT JOIN classes cl ON u.class_id = cl.id WHERE u.role = 'student'";
        $student_params = [];

        // Eğer değerlendirme belirli bir sınıfa veya derse bağlıysa öğrencileri filtrele
        // Burada evaluations tablonuzdaki class_id veya course_id sütunlarını kullanmalısınız
        if (!empty($evaluation['class_id'])) {
            $student_sql .= " AND u.class_id = ?";
            $student_params[] = $evaluation['class_id'];
        } elseif (!empty($evaluation['course_id'])) {
            $student_sql .= " AND u.id IN (SELECT student_id FROM student_enrollments WHERE course_id = ? AND status = 'active')";
            $student_params[] = $evaluation['course_id'];
        }
        $student_sql .= " ORDER BY u.name ASC";
        $students = $this->db->select($student_sql, $student_params);

        // Yoklama kayıtlarını alırken 'exam_id' (exam_attendance tablosundaki sütun) kullanıyoruz
        $existing_records_raw = $this->db->select("SELECT student_id, status, notes FROM exam_attendance WHERE exam_id = ?", [$evaluation_id]);
        $attendance_map = array_column($existing_records_raw, null, 'student_id');

        return [
            'exam' => $evaluation, // 'exam' değişken adını koruduk, view'da değişiklik daha az olsun diye
            'students' => $students,
            'attendance_map' => $attendance_map,
            'statuses' => ['Geldi', 'Gelmedi', 'Geç Geldi', 'İzinli']
        ];
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('index.php?module=exam_attendance'); exit; }
        
        $evaluation_id = (int)($_POST['exam_id'] ?? 0); // Formdan hala 'exam_id' olarak geliyor
        $students_in_form = array_keys($_POST['students'] ?? []);
        $statuses = $_POST['status'] ?? [];
        $notes = $_POST['notes'] ?? [];
        
        if (!$evaluation_id || empty($students_in_form)) {
            redirect('index.php?module=exam_attendance&action=take&exam_id='.$evaluation_id.'&error=save_failed');
            exit;
        }

        // Değerlendirme tarihini evaluations tablosundan çekiyoruz, sütun adı 'exam_date'
        $evaluation = $this->db->select("SELECT exam_date, class_id FROM evaluations WHERE id = ?", [$evaluation_id])[0] ?? null; // class_id'yi de alalım
        if (!$evaluation) {
            redirect('index.php?module=exam_attendance&action=index&error=evaluation_not_found');
            exit;
        }
        
        $upsert_stmt = $this->db->getConnection()->prepare(
            "INSERT INTO exam_attendance (student_id, exam_id, class_id, attendance_date, status, notes, entry_by_user_id) 
             VALUES (:student_id, :exam_id, :class_id, :attendance_date, :status, :notes, :entry_by)
             ON DUPLICATE KEY UPDATE status = VALUES(status), notes = VALUES(notes), entry_by_user_id = VALUES(entry_by_user_id), entry_at = CURRENT_TIMESTAMP"
        );
        
        foreach ($students_in_form as $student_id) {
            $student_id = (int)$student_id;
            $status = $statuses[$student_id] ?? 'Geldi';
            $note = $notes[$student_id] ?? null;
            
            // Eğer değerlendirme belirli bir sınıfa atanmamışsa, öğrencinin kendi sınıfını kullanabiliriz
            // Veya evaluation'ın class_id'sini doğrudan kullanabiliriz.
            $effective_class_id = $evaluation['class_id'] ?? ($this->db->select("SELECT class_id FROM users WHERE id = ?", [$student_id])[0]['class_id'] ?? null);

            $upsert_stmt->execute([
                ':student_id' => $student_id,
                ':exam_id' => $evaluation_id, // Bu, exam_attendance tablosundaki exam_id sütunudur.
                ':class_id' => $effective_class_id,
                ':attendance_date' => $evaluation['exam_date'], // 'exam_date' kullanıldı
                ':status' => $status,
                ':notes' => $note,
                ':entry_by' => $this->currentUser['id']
            ]);
        }
        
        log_activity('SAVE_ATTENDANCE', 'ExamAttendance', $evaluation_id, "Sınav/Değerlendirme yoklaması kaydedildi/güncellendi.");
        redirect('index.php?module=exam_attendance&action=take&exam_id='.$evaluation_id.'&status=saved');
    }

    public function report() {
        $params = [];
        // Filtre dropdown'larını doldurmak için gerekli listeleri çek
        // 'evaluations' tablosundan çekiyoruz
        $all_evaluations = $this->db->select("SELECT id, name FROM evaluations ORDER BY name ASC");
        $all_classes = $this->db->select("SELECT id, name FROM classes ORDER BY name ASC");
        $all_students = $this->db->select("SELECT id, name FROM users WHERE role='student' ORDER BY name ASC");
        
        // Formdan gelen filtre değerlerini al
        $filter_evaluation_id = $_GET['filter_exam_id'] ?? null; // URL'den hala 'filter_exam_id' olarak gelebilir
        $filter_class_id = $_GET['filter_class_id'] ?? null;
        $filter_student_id = $_GET['filter_student_id'] ?? null;
        $filter_date_start = $_GET['filter_date_start'] ?? null;
        $filter_date_end = $_GET['filter_date_end'] ?? null;
        $filter_status = $_GET['filter_status'] ?? null;

        // Ana SQL sorgusu
        $sql = "SELECT ea.attendance_date, ea.status, ea.notes,
                       u.name as student_name, 
                       e.name as evaluation_name, -- exam_name yerine evaluation_name
                       cl.name as class_name,
                       entry_user.name as entry_teacher_name
                FROM exam_attendance ea
                JOIN users u ON ea.student_id = u.id
                JOIN evaluations e ON ea.exam_id = e.id -- 'exams' yerine 'evaluations'
                LEFT JOIN classes cl ON ea.class_id = cl.id
                JOIN users entry_user ON ea.entry_by_user_id = entry_user.id
                LEFT JOIN courses c ON e.course_id = c.id"; 
        
        $sql .= " WHERE 1=1";

        // Rol bazlı yetkilendirme (Düzeltilmiş)
        if($this->currentUser['role'] === 'teacher'){
            // Bir öğretmen, ya yoklamayı kendisi girdiyse YA DA değerlendirmenin ilişkili olduğu dersin öğretmeniyse kayıtları görebilir.
            $sql .= " AND (ea.entry_by_user_id = ? OR c.teacher_id = ?)";
            $params[] = $this->currentUser['id'];
            $params[] = $this->currentUser['id'];
        }

        // Filtreleri sorguya ekle
        if($filter_evaluation_id){ $sql .= " AND ea.exam_id = ?"; $params[] = (int)$filter_evaluation_id; } // Hala exam_id sütununu kullanıyoruz
        if($filter_class_id){ $sql .= " AND ea.class_id = ?"; $params[] = (int)$filter_class_id; }
        if($filter_student_id){ $sql .= " AND ea.student_id = ?"; $params[] = (int)$filter_student_id; }
        if($filter_date_start){ $sql .= " AND ea.attendance_date >= ?"; $params[] = $filter_date_start; }
        if($filter_date_end){ $sql .= " AND ea.attendance_date <= ?"; $params[] = $filter_date_end; }
        if($filter_status && in_array($filter_status, ['Geldi', 'Gelmedi', 'Geç Geldi', 'İzinli'])){ 
            $sql .= " AND ea.status = ?"; $params[] = $filter_status; 
        }

        $sql .= " ORDER BY ea.attendance_date DESC, e.name ASC, u.name ASC LIMIT 200"; 

        $attendance_records = $this->db->select($sql, $params);
        
        return [
            'attendance_records' => $attendance_records,
            'all_evaluations' => $all_evaluations, // Değişken adı 'all_exams' yerine 'all_evaluations'
            'all_classes' => $all_classes,
            'all_students' => $all_students,
            'attendance_statuses_for_filter' => ['Geldi', 'Gelmedi', 'Geç Geldi', 'İzinli'],
            'filters' => $_GET
        ];
    }
}