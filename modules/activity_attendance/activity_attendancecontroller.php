<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class Activity_AttendanceController
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

    /**
     * Yoklama alınacak, onaylanmış ve tarihi geçmemiş tüm etkinlikleri listeler.
     * Kategori adını da almak için JOIN yapar.
     */
    public function index()
    {
        $sql = "SELECT 
                    a.id, 
                    a.title, 
                    a.activity_date, 
                    ac.name as category_name
                FROM 
                    activities a
                LEFT JOIN 
                    activity_categories ac ON a.category_id = ac.id
                WHERE 
                    a.status = 'approved' AND a.activity_date >= CURDATE()
                ORDER BY 
                    a.activity_date ASC";
        
        $activities_raw = $this->db->select($sql);

        $activities_for_view = [];
        if (!empty($activities_raw)) {
            foreach($activities_raw as $activity) {
                $activity['name'] = $activity['title'];
                $activities_for_view[] = $activity;
            }
        }

        return [
            'activities' => $activities_for_view,
            'error_message' => $_GET['error'] ?? null,
            'status_message' => $_GET['status'] ?? null
        ];
    }

    /**
     * Seçilen etkinlik için yoklama alma formunu gösterir.
     */
    public function take()
    {
        $activity_id = $_GET['activity_id'] ?? 0;
        if (!$activity_id) {
            redirect('index.php?module=activity_attendance&action=index&error=missing_params');
            exit;
        }

        $activity = $this->db->select("SELECT * FROM activities WHERE id = ?", [$activity_id])[0] ?? null;
        if (!$activity) {
            redirect('index.php?module=activity_attendance&action=index&error=not_found');
            exit;
        }
        
        // DÜZELTİLMİŞ ÖĞRENCİ SORGUSU:
        // activity_classes tablosundaki sınıf ID'lerine göre o sınıftaki öğrencileri getirir.
        $student_sql = "SELECT u.id, u.name, cl.name as class_name 
                        FROM users u 
                        JOIN classes cl ON u.class_id = cl.id
                        JOIN activity_classes ac ON u.class_id = ac.class_id -- Öğrencinin sınıfı, etkinliğin atanmış sınıfı mı?
                        WHERE u.role = 'student' AND ac.activity_id = ? 
                        ORDER BY u.name";
        
        $students = $this->db->select($student_sql, [$activity_id]);
        
        // Eğer etkinliğe sınıf atanmamışsa veya hiç öğrenci gelmiyorsa,
        // aktivitenin 'include_parents' veya başka bir kuralı varsa tüm öğrencileri çekebiliriz.
        // Ancak current durumda sadece atanan sınıftaki öğrencileri çekiyoruz.

        $existing_records_raw = $this->db->select("SELECT student_id, status, notes FROM activity_attendance WHERE activity_id = ?", [$activity_id]);
        $attendance_map = array_column($existing_records_raw, null, 'student_id');

        return [
            'activity' => $activity,
            'students' => $students,
            'attendance_map' => $attendance_map,
            'statuses' => ['Geldi', 'Gelmedi', 'İzinli'] 
        ];
    }

    /**
     * Girilen etkinlik yoklama bilgilerini kaydeder.
     */
    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('index.php?module=activity_attendance'); exit; }
        
        $activity_id = (int)($_POST['activity_id'] ?? 0);
        $students_in_form = array_keys($_POST['students'] ?? []);
        $statuses = $_POST['status'] ?? [];
        $notes = $_POST['notes'] ?? [];
        
        if (!$activity_id || empty($students_in_form)) {
            redirect('index.php?module=activity_attendance&action=take&activity_id='.$activity_id.'&error=save_failed');
            exit;
        }

        $activity = $this->db->select("SELECT activity_date FROM activities WHERE id = ?", [$activity_id])[0] ?? null;
        if (!$activity) {
            redirect('index.php?module=activity_attendance&action=index&error=activity_not_found');
            exit;
        }
        
        $upsert_stmt = $this->db->getConnection()->prepare(
            "INSERT INTO activity_attendance (student_id, activity_id, class_id, attendance_date, status, notes, entry_by_user_id) 
             VALUES (:student_id, :activity_id, :class_id, :attendance_date, :status, :notes, :entry_by)
             ON DUPLICATE KEY UPDATE status = VALUES(status), notes = VALUES(notes), entry_by_user_id = VALUES(entry_by_user_id), entry_at = CURRENT_TIMESTAMP"
        );
        
        foreach ($students_in_form as $student_id) {
            $student_id = (int)$student_id;
            $status = $statuses[$student_id] ?? 'Geldi';
            $note = $notes[$student_id] ?? null;
            
            // Öğrencinin sınıf ID'sini users tablosundan çekiyoruz.
            // Bu, activity_classes tablosundan gelmez, öğrencinin atanmış olduğu sınıftır.
            $student_class_id = $this->db->select("SELECT class_id FROM users WHERE id = ?", [$student_id])[0]['class_id'] ?? null;
            
            $upsert_stmt->execute([
                ':student_id' => $student_id,
                ':activity_id' => $activity_id,
                ':class_id' => $student_class_id, // Öğrencinin kendi sınıfı
                ':attendance_date' => $activity['activity_date'],
                ':status' => $status,
                ':notes' => $note,
                ':entry_by' => $this->currentUser['id']
            ]);
        }
        
        log_activity('SAVE_ATTENDANCE', 'ActivityAttendance', $activity_id, "Etkinlik yoklaması kaydedildi/güncellendi.");
        redirect('index.php?module=activity_attendance&action=take&activity_id='.$activity_id.'&status=saved');
    }

    /**
     * Etkinlik yoklama raporlarını gösterir (Admin ve Öğretmenler için).
     */
    public function report() {
        $params = [];
        // Filtre dropdown'larını doldurmak için gerekli listeleri çek
        $all_activities = $this->db->select("SELECT id, title as name FROM activities ORDER BY title ASC");
        $all_classes = $this->db->select("SELECT id, name FROM classes ORDER BY name ASC");
        $all_students = $this->db->select("SELECT id, name FROM users WHERE role='student' ORDER BY name ASC");
        
        // Formdan gelen filtre değerlerini al
        $filter_activity_id = $_GET['filter_activity_id'] ?? null;
        $filter_class_id = $_GET['filter_class_id'] ?? null;
        $filter_student_id = $_GET['filter_student_id'] ?? null;
        $filter_date_start = $_GET['filter_date_start'] ?? null;
        $filter_date_end = $_GET['filter_date_end'] ?? null;
        $filter_status = $_GET['filter_status'] ?? null;

        // Ana SQL sorgusu
        $sql = "SELECT aa.attendance_date, aa.status, aa.notes,
                       u.name as student_name, 
                       act.title as activity_name,
                       cl.name as class_name,
                       entry_user.name as entry_teacher_name
                FROM activity_attendance aa
                JOIN users u ON aa.student_id = u.id
                JOIN activities act ON aa.activity_id = act.id
                LEFT JOIN classes cl ON aa.class_id = cl.id
                JOIN users entry_user ON aa.entry_by_user_id = entry_user.id
                WHERE 1=1";
        
        // Rol bazlı yetkilendirme (Öğretmenler şimdilik tüm etkinlik yoklamalarını görebilir, daraltılabilir)
        if($this->userRole === 'teacher'){
            // Öğretmenler sadece kendi oluşturduğu etkinliklerin yoklamasını veya kendisinin girdiği yoklamaları görebilir.
            $sql .= " AND (aa.entry_by_user_id = ? OR act.creator_id = ?)";
            $params[] = $this->userId;
            $params[] = $this->userId;
        }

        // Filtreleri sorguya ekle
        if($filter_activity_id){ $sql .= " AND aa.activity_id = ?"; $params[] = (int)$filter_activity_id; }
        if($filter_class_id){ $sql .= " AND aa.class_id = ?"; $params[] = (int)$filter_class_id; }
        if($filter_student_id){ $sql .= " AND aa.student_id = ?"; $params[] = (int)$filter_student_id; }
        if($filter_date_start){ $sql .= " AND aa.attendance_date >= ?"; $params[] = $filter_date_start; }
        if($filter_date_end){ $sql .= " AND aa.attendance_date <= ?"; $params[] = $filter_date_end; }
        if($filter_status && in_array($filter_status, ['Geldi', 'Gelmedi', 'İzinli'])){ 
            $sql .= " AND aa.status = ?"; $params[] = $filter_status; 
        }

        $sql .= " ORDER BY aa.attendance_date DESC, act.title ASC, u.name ASC LIMIT 200"; 

        $attendance_records = $this->db->select($sql, $params);
        
        return [
            'attendance_records' => $attendance_records,
            'all_activities' => $all_activities,
            'all_classes' => $all_classes,
            'all_students' => $all_students,
            'attendance_statuses_for_filter' => ['Geldi', 'Gelmedi', 'İzinli'],
            'filters' => $_GET
        ];
    }
}