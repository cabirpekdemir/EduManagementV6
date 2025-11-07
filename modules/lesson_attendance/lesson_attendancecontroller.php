<?php
require_once __DIR__ . '/../../core/database.php';

class Lesson_attendanceController
{
    protected $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) @session_start();
        
        $role = $_SESSION['user']['role'] ?? 'guest';
        if (!in_array($role, ['admin', 'teacher'])) {
            die("Bu modüle sadece admin ve öğretmenler erişebilir.");
        }
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

    /* ==================== ANA SAYFA - DERS SEÇİMİ ==================== */
    /* ==================== ANA SAYFA - TÜM DERSLER LİSTESİ ==================== */
public function index(): array
{
    $role = $this->currentRole();
    $userId = $this->currentUserId();

    // Rol bazlı ders listesi
    if ($role === 'admin') {
        // Admin tüm dersleri görür
        $courses = $this->db->select("
            SELECT 
                c.id, 
                c.name, 
                c.category,
                c.color,
                u.name AS teacher_name,
                (SELECT COUNT(DISTINCT s.id) 
                 FROM users s
                 INNER JOIN student_enrollments se ON s.id = se.student_id
                 WHERE se.course_id = c.id 
                 AND se.status = 'active'
                 AND s.role = 'student') AS student_count,
                (SELECT COUNT(*) 
                 FROM lesson_attendance la 
                 WHERE la.course_id = c.id 
                 AND DATE(la.lesson_date) = CURDATE()) AS today_attendance_count
            FROM courses c
            LEFT JOIN users u ON c.teacher_id = u.id
            WHERE c.is_active = 1
            ORDER BY c.name ASC
        ") ?? [];
    } else {
        // Öğretmen sadece kendi derslerini görür
        $courses = $this->db->select("
            SELECT 
                c.id, 
                c.name, 
                c.category,
                c.color,
                u.name AS teacher_name,
                (SELECT COUNT(DISTINCT s.id) 
                 FROM users s
                 INNER JOIN student_enrollments se ON s.id = se.student_id
                 WHERE se.course_id = c.id 
                 AND se.status = 'active'
                 AND s.role = 'student') AS student_count,
                (SELECT COUNT(*) 
                 FROM lesson_attendance la 
                 WHERE la.course_id = c.id 
                 AND DATE(la.lesson_date) = CURDATE()) AS today_attendance_count
            FROM courses c
            LEFT JOIN users u ON c.teacher_id = u.id
            WHERE c.teacher_id = ? AND c.is_active = 1
            ORDER BY c.name ASC
        ", [$userId]) ?? [];
    }

    return [
        'view'    => 'lesson_attendance/view/index.php',
        'title'   => 'Ders Yoklaması',
        'courses' => $courses
    ];
}

    /* ==================== YOKLAMA AL ==================== */
    public function take(): array
    {
        $courseId = (int)($_GET['course_id'] ?? 0);
        $date = $_GET['date'] ?? date('Y-m-d');

        if (!$courseId) {
            $this->flashError('Lütfen ders seçin.');
            header('Location: index.php?module=lesson_attendance&action=index');
            exit;
        }

        // Ders bilgisi
        $course = $this->db->fetch("
            SELECT c.*, u.name as teacher_name
            FROM courses c
            LEFT JOIN users u ON c.teacher_id = u.id
            WHERE c.id = ?
        ", [$courseId]);

        if (!$course) {
            $this->flashError('Ders bulunamadı.');
            header('Location: index.php?module=lesson_attendance&action=index');
            exit;
        }

        // Yetki kontrolü
        if ($this->currentRole() === 'teacher' && $course['teacher_id'] != $this->currentUserId()) {
            $this->flashError('Bu derse yetkiniz yok.');
            header('Location: index.php?module=lesson_attendance&action=index');
            exit;
        }

        // Bu derse kayıtlı öğrenciler
        $students = $this->db->select("
            SELECT u.id, u.name, u.student_number, c.name as class_name,
                   u.profile_photo
            FROM users u 
            LEFT JOIN student_enrollments se ON u.id = se.student_id 
            LEFT JOIN classes c ON u.class_id = c.id 
            WHERE u.role = 'student' 
              AND se.course_id = ? 
              AND se.status = 'active' 
            ORDER BY u.name
        ", [$courseId]) ?? [];

        if (empty($students)) {
            $this->flashError('Bu derse kayıtlı öğrenci bulunmamaktadır.');
            header('Location: index.php?module=lesson_attendance&action=index');
            exit;
        }

        // Mevcut yoklama kayıtları
        $existing = $this->db->select("
            SELECT student_id, status, notes 
            FROM lesson_attendance 
            WHERE course_id = ? AND lesson_date = ?
        ", [$courseId, $date]) ?? [];

        $attendanceMap = [];
        foreach ($existing as $record) {
            $attendanceMap[(int)$record['student_id']] = $record;
        }

        return [
            'view'          => 'lesson_attendance/view/take.php',
            'title'         => 'Yoklama Al',
            'course'        => $course,
            'date'          => $date,
            'students'      => $students,
            'attendanceMap' => $attendanceMap
        ];
    }

    /* ==================== YOKLAMA KAYDET ==================== */
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=lesson_attendance&action=index');
            exit;
        }

        $courseId = (int)($_POST['course_id'] ?? 0);
        $date = $_POST['date'] ?? '';
        $attendanceData = $_POST['attendance'] ?? [];

        if (!$courseId || !$date || empty($attendanceData)) {
            $this->flashError('Lütfen tüm alanları doldurun.');
            header('Location: index.php?module=lesson_attendance&action=take&course_id='.$courseId.'&date='.$date);
            exit;
        }

        try {
            $savedCount = 0;
            
            foreach ($attendanceData as $studentId => $data) {
                $studentId = (int)$studentId;
                $status = $data['status'] ?? 'geldi';
                $notes = trim($data['notes'] ?? '');

                // Öğrencinin sınıfını al
                $student = $this->db->fetch("SELECT class_id FROM users WHERE id = ?", [$studentId]);
                $classId = $student['class_id'] ?? null;

                // UPSERT
                $this->db->execute("
                    INSERT INTO lesson_attendance 
                        (student_id, course_id, class_id, lesson_date, status, notes, entry_by_user_id, entry_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                        status = VALUES(status), 
                        notes = VALUES(notes),
                        entry_by_user_id = VALUES(entry_by_user_id),
                        entry_at = NOW()
                ", [$studentId, $courseId, $classId, $date, $status, $notes, $this->currentUserId()]);

                $savedCount++;
            }

            $this->flashSuccess("$savedCount öğrenci için yoklama kaydedildi.");
            header('Location: index.php?module=lesson_attendance&action=take&course_id='.$courseId.'&date='.$date);
            exit;
            
        } catch (\Throwable $e) {
            error_log('Attendance save error: ' . $e->getMessage());
            $this->flashError('Yoklama kaydedilirken hata oluştu: ' . $e->getMessage());
            header('Location: index.php?module=lesson_attendance&action=take&course_id='.$courseId.'&date='.$date);
            exit;
        }
    }

    /* ==================== RAPOR ==================== */
    public function report(): array
    {
        $role = $this->currentRole();
        $userId = $this->currentUserId();

        // Filtre listeleri
        $allCourses = $this->db->select("SELECT id, name FROM courses ORDER BY name ASC") ?? [];
        $allClasses = $this->db->select("SELECT id, name FROM classes ORDER BY name ASC") ?? [];
        $allStudents = $this->db->select("SELECT id, name FROM users WHERE role='student' ORDER BY name ASC") ?? [];

        // Filtreler
        $filterCourseId = $_GET['filter_course_id'] ?? null;
        $filterClassId = $_GET['filter_class_id'] ?? null;
        $filterStudentId = $_GET['filter_student_id'] ?? null;
        $filterDateStart = $_GET['filter_date_start'] ?? null;
        $filterDateEnd = $_GET['filter_date_end'] ?? null;
        $filterStatus = $_GET['filter_status'] ?? null;

        // SQL
        $sql = "SELECT la.lesson_date, la.status, la.notes,
                       u.name as student_name, 
                       c.name as course_name, 
                       cl.name as class_name,
                       eu.name as entry_teacher_name
                FROM lesson_attendance la
                JOIN users u ON la.student_id = u.id
                JOIN courses c ON la.course_id = c.id
                LEFT JOIN classes cl ON la.class_id = cl.id
                JOIN users eu ON la.entry_by_user_id = eu.id
                WHERE 1=1";
        
        $params = [];

        // Öğretmen kısıtlaması
        if ($role === 'teacher') {
            $sql .= " AND (c.teacher_id = ? OR la.entry_by_user_id = ?)";
            $params[] = $userId;
            $params[] = $userId;
        }

        // Filtreler
        if ($filterCourseId) {
            $sql .= " AND la.course_id = ?";
            $params[] = (int)$filterCourseId;
        }
        
        if ($filterClassId) {
            $sql .= " AND la.class_id = ?";
            $params[] = (int)$filterClassId;
        }
        
        if ($filterStudentId) {
            $sql .= " AND la.student_id = ?";
            $params[] = (int)$filterStudentId;
        }
        
        if ($filterDateStart) {
            $sql .= " AND la.lesson_date >= ?";
            $params[] = $filterDateStart;
        }
        
        if ($filterDateEnd) {
            $sql .= " AND la.lesson_date <= ?";
            $params[] = $filterDateEnd;
        }
        
        if ($filterStatus && in_array($filterStatus, ['geldi', 'gelmedi', 'izinli'])) {
            $sql .= " AND la.status = ?";
            $params[] = $filterStatus;
        }

        $sql .= " ORDER BY la.lesson_date DESC, c.name ASC, u.name ASC LIMIT 500";

        $attendanceRecords = $this->db->select($sql, $params) ?? [];

        return [
            'view'               => 'lesson_attendance/view/report.php',
            'title'              => 'Yoklama Raporları',
            'allCourses'         => $allCourses,
            'allClasses'         => $allClasses,
            'allStudents'        => $allStudents,
            'attendanceRecords'  => $attendanceRecords,
            'filters'            => $_GET,
            'availableStatuses'  => ['geldi' => 'Geldi', 'gelmedi' => 'Gelmedi', 'izinli' => 'İzinli']
        ];
    }
}