<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class Lesson_AttendanceController
{
    protected $db;
    protected $currentUser;
    protected $userRole; // Tanımlı olmasına rağmen hata veriyor, kontrol edeceğiz
    protected $userId;   // Tanımlı olmasına rağmen hata veriyor, kontrol edeceğiz

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->currentUser = $_SESSION['user'] ?? null;
        // DÜZELTME: userRole ve userId değerlerini null olmaması için varsayılan atama
        $this->userRole = $this->currentUser['role'] ?? 'guest';
        $this->userId = $this->currentUser['id'] ?? 0;

        if (!in_array($this->userRole, ['admin', 'teacher'])) {
            die("⛔ Bu modüle sadece adminler ve öğretmenler erişebilir!");
        }
    }

    /**
     * Yoklama almak için ders, ders saati ve tarih seçme formunu gösterir.
     */
    public function index()
    {
        $courses = [];
        if ($this->userRole === 'admin') {
            $courses = $this->db->select("SELECT id, name FROM courses ORDER BY name ASC");
        } else { // 'teacher'
            $courses = $this->db->select("SELECT id, name FROM courses WHERE teacher_id = ? ORDER BY name ASC", [$this->userId]);
        }
        
        // Düzeltme: index() metodunun da çıktıyı yakalaması ve layout'u dahil etmesi
        ob_start();
        $viewPath = __DIR__ . '/../../themes/default/pages/lesson_attendance/index.php'; 
        
        if (file_exists($viewPath)) {
            // View dosyasına aktarılacak değişkenler
            $courses_data = $courses; 
            $error_message = $_GET['error'] ?? null;
            $status_message = $_GET['status'] ?? null;
            
            include $viewPath;
        } else {
            log_activity('VIEW_ERROR', 'LessonAttendance', null, "Index View dosyası bulunamadı: " . $viewPath);
            echo "Index View file not found: " . $viewPath;
        }
        $pageContent = ob_get_clean();
        include __DIR__ . '/../../themes/default/layout.php'; 
    }

    /**
     * AJAX isteği için: Seçilen bir derse ait ders saatlerini JSON formatında döndürür.
     */
    public function get_lesson_slots()
    {
        header('Content-Type: application/json');
        $course_id = $_GET['course_id'] ?? 0;
        if (!$course_id) {
            echo json_encode(['success' => false, 'message' => 'Ders ID eksik.']);
            exit;
        }

        $slots = $this->db->select(
            "SELECT id, day, TIME_FORMAT(start_time, '%H:%i') as start_f, TIME_FORMAT(end_time, '%H:%i') as end_f 
             FROM course_times 
             WHERE course_id = ? 
             ORDER BY FIELD(day, 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'), start_time ASC",
            [$course_id]
        );

        echo json_encode(['success' => true, 'slots' => $slots]);
        exit;
    }
    
    /**
     * Seçilen ders, ders saati ve tarih için yoklama alma formunu gösterir.
     */
    public function take()
    {
        $course_id = $_GET['course_id'] ?? 0;
        $lesson_slot_id = $_GET['lesson_slot_id'] ?? 0;
        $date_str = $_GET['date'] ?? date('Y-m-d');

        if (!$course_id || !$lesson_slot_id || !$date_str) {
            redirect('index.php?module=lesson_attendance&action=index&error=missing_params');
            exit;
        }

        $course = $this->db->select("SELECT * FROM courses WHERE id = ?", [$course_id])[0] ?? null;
        $lesson_slot = $this->db->select("SELECT * FROM course_times WHERE id = ?", [$lesson_slot_id])[0] ?? null;
        if (!$course || !$lesson_slot) {
            redirect('index.php?module=lesson_attendance&action=index&error=not_found');
            exit;
        }

        if ($this->userRole === 'teacher' && $course['teacher_id'] != $this->userId) {
            die("Bu ders için yoklama alma yetkiniz yok.");
        }
        
        $students = $this->db->select(
            "SELECT u.id, u.name, cl.name as class_name 
             FROM users u 
             JOIN student_enrollments se ON u.id = se.student_id 
             LEFT JOIN classes cl ON u.class_id = cl.id 
             WHERE u.role = 'student' AND se.course_id = ? AND se.status = 'active' ORDER BY u.name",
            [$course_id]
        );

        $existing_records_raw = $this->db->select(
            "SELECT student_id, status, notes FROM lesson_attendance WHERE lesson_slot_id = ? AND lesson_date = ?",
            [$lesson_slot_id, $date_str]
        );
        $attendance_map = array_column($existing_records_raw, null, 'student_id');

        // DÜZELTME: take() metodunun da çıktıyı yakalaması ve layout'u dahil etmesi
        ob_start();
        $viewPath = __DIR__ . '/../../themes/default/pages/lesson_attendance/take.php'; 
        
        if (file_exists($viewPath)) {
            // View dosyasına aktarılacak tüm değişkenleri burada manuel olarak tanımlayın.
            // View'daki beklentiye göre değişken isimlerini dikkatlice kontrol edin.
            $course_data = $course; 
            $lesson_slot_data = $lesson_slot; 
            $date_display = $date_str; // View'da $date yerine $date_display kullanacağız.
            $students_list = $students; 
            $attendance_map_data = $attendance_map; 
            $statuses_list = ['Geldi', 'Gelmedi', 'Geç Geldi', 'İzinli']; // View'da $statuses yerine $statuses_list kullanacağız.
            
            // Eğer view'da hala $_GET['error'] veya $_GET['status'] bekleniyorsa onları da aktaralım
            $error_message = $_GET['error'] ?? null;
            $status_message = $_GET['status'] ?? null;

            include $viewPath;
        } else {
            log_activity('VIEW_ERROR', 'LessonAttendance', null, "Take View dosyası bulunamadı: " . $viewPath);
            echo "Take View file not found: " . $viewPath;
        }
        $pageContent = ob_get_clean();
        include __DIR__ . '/../../themes/default/layout.php';
    }
    
    /**
     * Girilen yoklama bilgilerini veritabanına kaydeder veya günceller.
     */
    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?module=lesson_attendance');
            exit;
        }

        $course_id = (int)($_POST['course_id'] ?? 0);
        $lesson_slot_id = (int)($_POST['lesson_slot_id'] ?? 0);
        $date = $_POST['date'] ?? '';
        $students_in_form = $_POST['students'] ?? [];
        $statuses = $_POST['status'] ?? [];
        $notes = $_POST['notes'] ?? [];
        
        if (!$course_id || !$lesson_slot_id || !$date || empty($students_in_form)) {
            redirect('index.php?module=lesson_attendance&action=take&course_id='.$course_id.'&lesson_slot_id='.$lesson_slot_id.'&date='.$date.'&error=save_failed');
            exit;
        }
        
        $upsert_stmt = $this->db->getConnection()->prepare(
            "INSERT INTO lesson_attendance (student_id, course_id, class_id, lesson_date, lesson_slot_id, status, notes, entry_by_user_id) 
             VALUES (:student_id, :course_id, :class_id, :lesson_date, :lesson_slot_id, :status, :notes, :entry_by)
             ON DUPLICATE KEY UPDATE 
                status = VALUES(status), 
                notes = VALUES(notes),
                entry_by_user_id = VALUES(entry_by_user_id),
                entry_at = CURRENT_TIMESTAMP"
        );
        
        foreach ($students_in_form as $student_id) {
            $student_id = (int)$student_id;
            $status = $statuses[$student_id] ?? 'Geldi';
            $note = $notes[$student_id] ?? null;
            
            $student_class_id = $this->db->select("SELECT class_id FROM users WHERE id = ?", [$student_id])[0]['class_id'] ?? null;
            
            $upsert_stmt->execute([
                ':student_id' => $student_id,
                ':course_id' => $course_id,
                ':class_id' => $student_class_id,
                ':lesson_date' => $date,
                ':lesson_slot_id' => $lesson_slot_id,
                ':status' => $status,
                ':notes' => $note,
                ':entry_by' => $this->currentUser['id']
            ]);
        }
        
        log_activity('SAVE_ATTENDANCE', 'LessonAttendance', $course_id, "Yoklama kaydedildi/güncellendi. Tarih: $date, Ders Saati: $lesson_slot_id");
        redirect('index.php?module=lesson_attendance&action=take&course_id='.$course_id.'&lesson_slot_id='.$lesson_slot_id.'&date='.$date.'&status=saved');
    }
    /**
     * Yoklama raporlarını gösterir (Admin ve Öğretmenler için).
     * Sadece ders yoklamalarını listeler.
     */
    public function report() {
        $params = []; 
        // Filtre dropdown'larını doldurmak için tüm listeleri çekelim
        try {
            $all_courses = $this->db->select("SELECT id, name FROM courses ORDER BY name ASC");
            $all_classes = $this->db->select("SELECT id, name FROM classes ORDER BY name ASC");
            $all_students = $this->db->select("SELECT id, name FROM users WHERE role='student' ORDER BY name ASC");
        } catch (PDOException $e) {
            log_activity('DB_ERROR', 'LessonAttendance', null, "Rapor filtreleri çekilirken veritabanı hatası: " . $e->getMessage());
            $all_courses = []; $all_classes = []; $all_students = []; // Hata durumunda boş döndür
        }

        // Formdan gelen filtre değerlerini al
        $filter_course_id = $_GET['filter_course_id'] ?? null;
        $filter_class_id = $_GET['filter_class_id'] ?? null;
        $filter_student_id = $_GET['filter_student_id'] ?? null;
        $filter_date_start = $_GET['filter_date_start'] ?? null;
        $filter_date_end = $_GET['filter_date_end'] ?? null;
        $filter_status = $_GET['filter_status'] ?? null;

        // Ana SQL sorgusu
        $sql = "SELECT la.lesson_date, la.status, la.notes,
                       u.name as student_name, 
                       c.name as course_name, 
                       cl.name as class_name,
                       entry_user.name as entry_teacher_name,
                       ct.day as lesson_day, TIME_FORMAT(ct.start_time, '%H:%i') as lesson_start, TIME_FORMAT(ct.end_time, '%H:%i') as lesson_end
                FROM lesson_attendance la
                JOIN users u ON la.student_id = u.id
                JOIN courses c ON la.course_id = c.id
                LEFT JOIN classes cl ON la.class_id = cl.id
                JOIN users entry_user ON la.entry_by_user_id = entry_user.id
                LEFT JOIN course_times ct ON la.lesson_slot_id = ct.id
                WHERE 1=1";
        
        // Rol bazlı yetkilendirme
        // DÜZELTME: $this->userRole yerine $this->userRole_property kullanıyoruz, çünkü logda undefined property hatası veriyordu.
        // Aslında, bu uyarı constructor'da doğru tanımlanmasına rağmen geliyordu. Bu bir workaround'dır.
        // Alternatif olarak, constructor'da property'leri public yapabilir veya __get sihirli metodunu kullanabilirsiniz.
        // Ancak en basiti, constructor'da tanımlanmış değerleri metod içinde yerel bir değişkene atamak.
        $userRole_property = $this->userRole; 
        $userId_property = $this->userId;

        if($userRole_property === 'teacher'){
            $sql .= " AND (c.teacher_id = ? OR la.entry_by_user_id = ?)";
            $params[] = $userId_property;
            $params[] = $userId_property;
        }

        // Filtreleri sorguya ekle
        if($filter_course_id){ $sql .= " AND la.course_id = ?"; $params[] = (int)$filter_course_id; }
        if($filter_class_id){ $sql .= " AND la.class_id = ?"; $params[] = (int)$filter_class_id; }
        if($filter_student_id){ $sql .= " AND la.student_id = ?"; $params[] = (int)$filter_student_id; }
        if($filter_date_start){ $sql .= " AND la.lesson_date >= ?"; $params[] = $filter_date_start; }
        if($filter_date_end){ $sql .= " AND la.lesson_date <= ?"; $params[] = $filter_date_end; }
        if($filter_status && in_array($filter_status, ['Geldi', 'Gelmedi', 'Geç Geldi', 'İzinli'])){ 
            $sql .= " AND la.status = ?"; $params[] = $filter_status; 
        }

        $sql .= " ORDER BY la.lesson_date DESC, c.name ASC, u.name ASC LIMIT 500"; // Raporlar için limit

        try {
            $attendance_records = $this->db->select($sql, $params);
        } catch (PDOException $e) {
            log_activity('DB_ERROR', 'LessonAttendance', null, "Rapor kayıtları çekilirken veritabanı hatası: " . $e->getMessage());
            $attendance_records = []; // Hata durumunda boş döndür
        }
        
        // DÜZELTME: report() metodunun çıktıyı yakalaması ve layout'u dahil etmesi
        ob_start();
        $viewPath = __DIR__ . '/../../themes/default/pages/lesson_attendance/report.php'; 
        
        if (file_exists($viewPath)) {
            // View dosyasına aktarılacak tüm değişkenleri burada manuel olarak tanımlayın.
            // View'daki beklentiye göre değişken isimlerini kontrol edin.
            $all_courses_view = $all_courses;
            $all_classes_view = $all_classes;
            $all_students_view = $all_students;
            $attendance_statuses_for_filter_view = ['Geldi', 'Gelmedi', 'Geç Geldi', 'İzinli'];
            $filters_view = $_GET; 
            $userRole_view = $this->userRole; // Bu da undefined property hatası verebiliyor
            $attendance_records_view = $attendance_records; 
            
            // Logdaki hatalar bu değişkenlerin ismine göreydi, bu yüzden takedown ettiğim değişken adlarını kullanıyorum
            $all_courses = $all_courses_view;
            $all_classes = $all_classes_view;
            $all_students = $all_students_view;
            $attendance_statuses_for_filter = $attendance_statuses_for_filter_view;
            $filters = $filters_view;
            $userRole = $userRole_view;
            $attendance_records = $attendance_records_view;

            include $viewPath;
        } else {
            log_activity('VIEW_ERROR', 'LessonAttendance', null, "Report View dosyası bulunamadı: " . $viewPath);
            echo "Report View file not found: " . $viewPath;
        }
        $pageContent = ob_get_clean();
        include __DIR__ . '/../../themes/default/layout.php'; 
    }
}