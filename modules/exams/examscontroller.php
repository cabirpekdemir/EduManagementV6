<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class ExamsController
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

        $admin_only_actions = ['create', 'store', 'edit', 'update', 'delete', 'update_exam_status'];
        $teacher_admin_actions = ['index', 'results', 'save_results', 'class_results', 'student_results', 'edit_result', 'update_result', 'delete_result', 'attendance_entry', 'save_attendance'];
        // calendar ve get_calendar_events tüm yetkili roller (admin,teacher,student,parent) tarafından görülebilir.
        
        global $action; // ana index.php'den
        
        if (in_array($action, $admin_only_actions) && $this->userRole !== 'admin') {
            log_activity('ACCESS_DENIED', 'Exams', null, "Yetkisiz sınav yönetimi (CRUD/Status) erişim denemesi: {$action}");
            die("⛔ Bu sınav yönetimi işlemine sadece adminler erişebilir!");
        }
        if (in_array($action, $teacher_admin_actions) && !in_array($this->userRole, ['admin', 'teacher'])) {
            if (!($this->userRole === 'student' && $action === 'student_results') && 
                !($this->userRole === 'parent' && $action === 'student_results') ) { // Öğrenci/Veli kendi sonuçlarını görebilir
                log_activity('ACCESS_DENIED', 'Exams', null, "Yetkisiz sınav sonuçları erişim denemesi: {$action}");
                die("⛔ Sınav sonuçlarına erişim yetkiniz yok.");
            }
        }
    }

    public function index()
    {
        $sql = "SELECT e.*, c.name as course_name, cl.name as class_name, u.name as creator_name
                FROM exams e
                LEFT JOIN courses c ON e.course_id = c.id
                LEFT JOIN classes cl ON e.class_id = cl.id
                JOIN users u ON e.creator_id = u.id
                ORDER BY e.exam_date DESC, e.name ASC";
        $exams = $this->db->select($sql);
        return ['exams' => $exams, 'userRole' => $this->userRole];
    }

   public function create()
    {
        $courses = $this->db->select("SELECT id, name FROM courses ORDER BY name ASC");
        $classes = $this->db->select("SELECT id, name FROM classes ORDER BY name ASC");
        return [
            'exam' => [ // Yeni sınav için boş değerler
                'name' => '',
                'description' => '',
                'exam_date' => null,
                'start_time' => null, // YENİ
                'end_time' => null,   // YENİ
                'course_id' => null,
                'class_id' => null,
                'max_score' => null,
                'status' => 'draft'
            ], 
            'courses' => $courses,
            'classes' => $classes,
            'isEdit' => false,
            'formAction' => 'index.php?module=exams&action=store',
            'exam_statuses' => ['draft', 'active', 'completed', 'cancelled']
        ];
    }
    public function store()
    {
        // ... (önceki değişken tanımlamaları aynı) ...
        $max_score = !empty($_POST['max_score']) ? (float)$_POST['max_score'] : null;
        $status = $_POST['status'] ?? 'draft';
        // YENİ SAAT ALANLARI
        $start_time = !empty($_POST['start_time']) ? $_POST['start_time'] : null;
        $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : null;
        $creator_id = $this->userId;

        if (empty($name) || !in_array($status, ['draft', 'active', 'completed', 'cancelled'])) {
            redirect('index.php?module=exams&action=create&error_message=empty_name_or_invalid_status');
            exit;
        }

        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO exams (name, description, exam_date, start_time, end_time, course_id, class_id, creator_id, max_score, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)" // Sütun ve soru işaretleri arttı
        );
        $stmt->execute([$name, $description, $exam_date, $start_time, $end_time, $course_id, $class_id, $creator_id, $max_score, $status]);
        // ... (metodun geri kalanı aynı) ...
        log_activity('CREATE', 'Exams', $this->db->getConnection()->lastInsertId(), "Sınav tanımladı: '$name', Tarih: $exam_date, Saat: $start_time-$end_time");
        redirect('index.php?module=exams&action=index&status_message=created');
    }
    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $exam = $this->db->select("SELECT * FROM exams WHERE id = ?", [$id])[0] ?? null;

        if (!$exam) {
            redirect('index.php?module=exams&action=index&error_message=not_found');
            exit;
        }
        
        $courses = $this->db->select("SELECT id, name FROM courses ORDER BY name ASC");
        $classes = $this->db->select("SELECT id, name FROM classes ORDER BY name ASC");

        return [
            'exam' => $exam,
            'courses' => $courses,
            'classes' => $classes,
            'isEdit' => true,
            'formAction' => 'index.php?module=exams&action=update&id=' . $id,
            'exam_statuses' => ['draft', 'active', 'completed', 'cancelled']
        ];
    }

   public function update()
    {
        // ... (önceki değişken tanımlamaları aynı) ...
        $max_score = !empty($_POST['max_score']) ? (float)$_POST['max_score'] : null;
        $status = $_POST['status'] ?? 'draft';
        // YENİ SAAT ALANLARI
        $start_time = !empty($_POST['start_time']) ? $_POST['start_time'] : null;
        $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : null;

        if (empty($name) || !$id || !in_array($status, ['draft', 'active', 'completed', 'cancelled'])) {
            redirect('index.php?module=exams&action=edit&id=' . $id . '&error_message=empty_name_or_invalid_status');
            exit;
        }

        $stmt = $this->db->getConnection()->prepare(
            "UPDATE exams SET name = ?, description = ?, exam_date = ?, start_time = ?, end_time = ?, 
             course_id = ?, class_id = ?, max_score = ?, status = ? 
             WHERE id = ?" // Sütun ve soru işaretleri arttı
        );
        $stmt->execute([$name, $description, $exam_date, $start_time, $end_time, $course_id, $class_id, $max_score, $status, $id]);
        // ... (metodun geri kalanı aynı) ...
        log_activity('UPDATE', 'Exams', $id, "Sınavı güncelledi: '$name', Tarih: $exam_date, Saat: $start_time-$end_time");
        redirect('index.php?module=exams&action=index&status_message=updated');
    }

    public function delete()
    {
        // ... (önceki delete metodu aynı kalabilir) ...
        $id = $_GET['id'] ?? 0;
        if (!$id) {
            redirect('index.php?module=exams&action=index&error_message=missing_id');
            exit;
        }
        $exam = $this->db->select("SELECT name FROM exams WHERE id = ?", [$id])[0] ?? null;
        $this->db->getConnection()->prepare("DELETE FROM exams WHERE id = ?")->execute([$id]);
        if ($exam) {
            log_activity('DELETE', 'Exams', $id, "Sınavı sildi: '{$exam['name']}'");
        }
        redirect('index.php?module=exams&action=index&status_message=deleted');
    }

    // --- SONUÇ GİRİŞİ VE LİSTELEME METODLARI (ÖNCEKİ GİBİ, GEREKİRSE KÜÇÜK GÜNCELLEMELERLE) ---
    public function results() { /* ... Bir önceki cevaptaki tam hali ... */ }
    public function save_results() { /* ... Bir önceki cevaptaki tam hali ... */ }
    public function class_results() { /* ... Bir önceki cevaptaki tam hali ... */ }
    public function student_results() { /* ... Bir önceki cevaptaki tam hali ... */ }
    public function edit_result() { /* ... Bir önceki cevaptaki tam hali ... */ }
    public function update_result() { /* ... Bir önceki cevaptaki tam hali ... */ }
    public function delete_result() { /* ... Bir önceki cevaptaki tam hali ... */ }

    // --- YENİ METODLAR ---
    /**
     * Sınav Takvimi için etkinlik verilerini JSON olarak döndürür.
     */
   public function get_calendar_exams()
    {
        header('Content-Type: application/json');
        $sql = "SELECT id, name as title, exam_date, start_time, end_time, status,
                       CASE status
                           WHEN 'active' THEN '#3a87ad'
                           WHEN 'completed' THEN '#468847'
                           ELSE '#ccc'
                       END as color
                FROM exams 
                WHERE status IN ('active', 'completed') AND exam_date IS NOT NULL";
        
        $exams_raw = $this->db->select($sql);
        $calendar_events = [];
        if(!empty($exams_raw)){
            foreach($exams_raw as $exam){
                $start_datetime = $exam['exam_date'];
                if($exam['start_time']){
                    $start_datetime .= 'T' . $exam['start_time'];
                }

                $end_datetime = $exam['exam_date']; // Bitiş tarihi aynı gün varsayılıyor
                if($exam['end_time']){
                    $end_datetime .= 'T' . $exam['end_time'];
                } elseif($exam['start_time']) { 
                    // Eğer bitiş saati yok ama başlangıç saati varsa, 1 saat ekleyelim (opsiyonel)
                    try {
                        $dt = new DateTime($start_datetime);
                        $dt->modify('+1 hour');
                        $end_datetime = $dt->format('Y-m-d\TH:i:s');
                    } catch (Exception $e) {
                        // Hatalı tarih formatı durumunda es geç
                    }
                }


                $calendar_events[] = [
                    'id' => $exam['id'],
                    'title' => $exam['title'],
                    'start' => $start_datetime,
                    'end' => ($exam['end_time'] || $exam['start_time']) ? $end_datetime : null, // Bitiş saati varsa veya başlangıç saati varsa ayarla
                    'color' => $exam['color'],
                    'url' => 'index.php?module=exams&action=results&exam_id=' . $exam['id'] // Veya edit sayfasına
                ];
            }
        }
        echo json_encode($calendar_events);
        exit;
    }

    /**
     * Sınav Takvimi sayfasını yükler.
     */
    public function calendar()
    {
        // Bu metod sadece view'ı yükler, view JS ile verileri çeker.
        return []; 
    }

    /**
     * Belirli bir sınav için yoklama giriş formunu gösterir.
     */
    public function attendance_entry()
    {
        if (!in_array($this->userRole, ['admin', 'teacher'])) {
            log_activity('ACCESS_DENIED', 'ExamAttendance', null, 'Yetkisiz sınav yoklama erişimi.');
            die("⛔ Sınav yoklama girişine erişim yetkiniz yok.");
        }

        $exam_id = $_GET['exam_id'] ?? 0;
        $exam = $this->db->select("SELECT * FROM exams WHERE id = ?", [$exam_id])[0] ?? null;
        if (!$exam || !in_array($exam['status'], ['active', 'completed'])) { // Sadece aktif veya tamamlanmış sınavlara yoklama
            redirect('index.php?module=exams&action=index&error_message=invalid_exam_for_attendance');
            exit;
        }

        // Sınavla ilişkili öğrenciler (results metoduyla aynı mantık)
        $students = [];
        if (!empty($exam['class_id'])) {
            $students = $this->db->select("SELECT id, name, sinif FROM users WHERE role = 'student' AND class_id = ? ORDER BY name ASC", [$exam['class_id']]);
        } elseif (!empty($exam['course_id'])) {
            $students = $this->db->select("SELECT u.id, u.name, u.sinif FROM users u JOIN student_enrollments se ON u.id = se.student_id WHERE u.role = 'student' AND se.course_id = ? AND se.status = 'active' ORDER BY u.name ASC", [$exam['course_id']]);
        } else {
            $students = $this->db->select("SELECT id, name, sinif FROM users WHERE role = 'student' ORDER BY name ASC LIMIT 200");
        }

        // Mevcut yoklama durumlarını çek
        $attendance_raw = $this->db->select("SELECT student_id, attendance_status FROM exam_results WHERE exam_id = ?", [$exam_id]);
        $attendance_map = [];
        foreach ($attendance_raw as $att) {
            $attendance_map[$att['student_id']] = $att['attendance_status'];
        }

        return [
            'exam' => $exam,
            'students' => $students,
            'attendance_map' => $attendance_map,
            'attendance_statuses' => ['present', 'absent', 'late', 'excused'], // Yoklama durumları
            'formAction' => 'index.php?module=exams&action=save_attendance&exam_id=' . $exam_id
        ];
    }

    /**
     * Sınav yoklama bilgilerini kaydeder/günceller.
     */
    public function save_attendance()
    {
        if (!in_array($this->userRole, ['admin', 'teacher'])) {
            log_activity('SAVE_ATTENDANCE_DENIED', 'Exams', null, 'Yetkisiz sınav yoklama kaydetme.');
            die("Yetkiniz yok.");
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?module=exams&action=index');
            exit;
        }

        $exam_id = $_GET['exam_id'] ?? ($_POST['exam_id'] ?? 0);
        if (!$exam_id) {
            redirect('index.php?module=exams&action=index&error_message=invalid_exam_id_on_save_att');
            exit;
        }
        
        $attendance_data = $_POST['attendance'] ?? []; // attendance[student_id] => status

        $entry_by_user_id = $this->userId;

        // Önce bu sınava ait tüm öğrenciler için exam_results'ta bir kayıt olduğundan emin olalım
        // Yoksa INSERT ile oluşturalım, varsa UPDATE edelim.
        // Bu, ON DUPLICATE KEY UPDATE ile daha verimli yapılabilir.
        // Veya results() metodundan gelen öğrenci listesindeki herkes için bir kayıt oluşturulur.

        $upsert_stmt = $this->db->getConnection()->prepare(
            "INSERT INTO exam_results (exam_id, student_id, attendance_status, attendance_entry_by_user_id, attendance_entry_date, entry_by_user_id) 
             VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, ?)
             ON DUPLICATE KEY UPDATE 
                attendance_status = VALUES(attendance_status),
                attendance_entry_by_user_id = VALUES(attendance_entry_by_user_id),
                attendance_entry_date = VALUES(attendance_entry_date),
                updated_at = CURRENT_TIMESTAMP" 
                // Not: entry_by_user_id not girişi için, attendance_entry_by_user_id yoklama için ayrı
        );
        
        $student_ids_in_form = array_keys($_POST['student_ids_in_form_att'] ?? []);


        foreach ($student_ids_in_form as $student_id) {
             $student_id = (int)$student_id;
             $status = $attendance_data[$student_id] ?? null;
             if ($status && in_array($status, ['present', 'absent', 'late', 'excused'])) {
                // Eğer öğrenci için henüz not girilmemişse entry_by_user_id olarak yoklamayı gireni atayalım
                $upsert_stmt->execute([$exam_id, $student_id, $status, $entry_by_user_id, $entry_by_user_id]);
             }
        }
        
        log_activity('SAVE_EXAM_ATTENDANCE', 'Exams', $exam_id, "ID:{$exam_id} sınavı için yoklama kaydetti.");
        redirect('index.php?module=exams&action=attendance_entry&exam_id=' . $exam_id . '&status_message=attendance_saved');
    }
}