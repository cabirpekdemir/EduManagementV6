<?php
// modules/teacher_enrollment/teacherenrollmentcontroller.php

require_once __DIR__ . '/../../core/auth.php';

class Teacher_enrollmentController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $this->checkAuth();
    }

    private function checkAuth(): void
    {
        $role = currentRole();
        if (!in_array($role, ['admin', 'teacher'])) {
            $_SESSION['form_error'] = 'Bu alana erişim yetkiniz yok';
            header('Location: index.php');
            exit;
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

    /**
     * Öğretmenin ders listesi
     */
    public function index()
    {
        $teacherId = currentUserId();
        $role = currentRole();
        
        // Admin tüm dersleri, öğretmen sadece kendi derslerini görür
        if ($role === 'admin') {
            $courses = $this->db->select("
                SELECT c.*, t.name as teacher_name,
                       (SELECT COUNT(*) FROM student_enrollments WHERE course_id = c.id AND status = 'active') as student_count
                FROM courses c
                LEFT JOIN users t ON t.id = c.teacher_id
                ORDER BY c.name
            ") ?? [];
        } else {
            $courses = $this->db->select("
                SELECT c.*, 
                       (SELECT COUNT(*) FROM student_enrollments WHERE course_id = c.id AND status = 'active') as student_count
                FROM courses c
                WHERE c.teacher_id = ?
                ORDER BY c.name
            ", [$teacherId]) ?? [];
        }
        
        return [
            'view' => 'teacher_enrollment/view/index.php',
            'title' => 'Derslerim',
            'courses' => $courses,
            'csrf_token' => $this->generateCsrfToken()
        ];
    }
    
    /**
     * Derse öğrenci ekle (Proje/Akademi için)
     */
    public function add_student()
    {
        $courseId = (int)($_GET['course_id'] ?? 0);
        $teacherId = currentUserId();
        $role = currentRole();
        
        // Dersi kontrol et
        $course = $this->db->fetch("SELECT * FROM courses WHERE id = ?", [$courseId]);
        
        if (!$course) {
            $this->flashErr('Ders bulunamadı');
            header('Location: index.php?module=teacher_enrollment&action=index');
            exit;
        }
        
        // Yetki kontrolü
        if ($role !== 'admin' && $course['teacher_id'] != $teacherId) {
            $this->flashErr('Bu derse öğrenci ekleme yetkiniz yok');
            header('Location: index.php?module=teacher_enrollment&action=index');
            exit;
        }
        
        // Tüm öğrenciler
        $students = $this->db->select("
            SELECT id, name, sinif, okul 
            FROM users 
            WHERE role = 'student' 
            ORDER BY name
        ") ?? [];
        
        // Bu derse kayıtlı öğrenciler
        $enrolled = $this->db->select("
            SELECT u.id, u.name, u.sinif, se.semester_year, se.semester_period, se.enrolled_by_teacher
            FROM student_enrollments se
            JOIN users u ON u.id = se.student_id
            WHERE se.course_id = ? AND se.status = 'active'
            ORDER BY u.name
        ", [$courseId]) ?? [];
        
        return [
            'view' => 'teacher_enrollment/view/add_student.php',
            'title' => 'Öğrenci Ekle',
            'course' => $course,
            'students' => $students,
            'enrolled' => $enrolled,
            'csrf_token' => $this->generateCsrfToken()
        ];
    }
    
    /**
     * Öğrenciyi derse ekle (POST)
     */
    public function store_student()
    {
        if (!$this->validateCsrfToken() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->flashErr('Geçersiz istek');
            header('Location: index.php?module=teacher_enrollment&action=index');
            exit;
        }
        
        $courseId = (int)($_POST['course_id'] ?? 0);
        $studentId = (int)($_POST['student_id'] ?? 0);
        $semesterYear = trim($_POST['semester_year'] ?? '');
        $semesterPeriod = trim($_POST['semester_period'] ?? '');
        
        $teacherId = currentUserId();
        $role = currentRole();
        
        try {
            $course = $this->db->fetch("SELECT * FROM courses WHERE id = ?", [$courseId]);
            
            if (!$course) {
                throw new \Exception('Ders bulunamadı');
            }
            
            // Yetki kontrolü
            if ($role !== 'admin' && $course['teacher_id'] != $teacherId) {
                throw new \Exception('Bu derse öğrenci ekleme yetkiniz yok');
            }
            
            // Öğrenci zaten kayıtlı mı?
            $exists = $this->db->fetch("
                SELECT id FROM student_enrollments 
                WHERE student_id = ? AND course_id = ? AND semester_year = ? AND semester_period = ? AND status = 'active'
            ", [$studentId, $courseId, $semesterYear, $semesterPeriod]);
            
            if ($exists) {
                throw new \Exception('Öğrenci zaten bu derse kayıtlı');
            }
            
            $this->db->beginTransaction();
            
            // Kayıt yap (öğretmen tarafından)
            $this->db->execute("
                INSERT INTO student_enrollments (student_id, course_id, semester_year, semester_period, status, enrolled_by_teacher)
                VALUES (?, ?, ?, ?, 'active', 1)
            ", [$studentId, $courseId, $semesterYear, $semesterPeriod]);
            
            $this->db->commit();
            $this->flashOk('Öğrenci derse eklendi');
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->flashErr('Hata: ' . $e->getMessage());
        }
        
        header('Location: index.php?module=teacher_enrollment&action=add_student&course_id=' . $courseId);
        exit;
    }
    
    /**
     * Öğrenciyi dersten çıkar
     */
    public function remove_student()
    {
        if (!$this->validateCsrfToken() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->flashErr('Geçersiz istek');
            header('Location: index.php?module=teacher_enrollment&action=index');
            exit;
        }
        
        $enrollmentId = (int)($_POST['enrollment_id'] ?? 0);
        $courseId = (int)($_POST['course_id'] ?? 0);
        
        try {
            $this->db->execute("
                UPDATE student_enrollments 
                SET status = 'cancelled' 
                WHERE id = ?
            ", [$enrollmentId]);
            
            $this->flashOk('Öğrenci dersten çıkarıldı');
            
        } catch (\Exception $e) {
            $this->flashErr('Hata: ' . $e->getMessage());
        }
        
        header('Location: index.php?module=teacher_enrollment&action=add_student&course_id=' . $courseId);
        exit;
    }
    // modules/teacher_enrollment/teacherenrollmentcontroller.php içine ekleyin

public function bulk_assign()
{
    $teacherId = currentUserId();
    $role = currentRole();
    
    // Seçili kademe
    $selectedStage = $_GET['stage'] ?? '';
    
    // DERSLER: Admin tümünü, öğretmen sadece kendininkileri görür
    if ($role === 'admin') {
        $courses = $this->db->select("
            SELECT c.*, u.name as teacher_name 
            FROM courses c
            LEFT JOIN users u ON u.id = c.teacher_id
            ORDER BY c.name
        ") ?? [];
    } else {
        // Öğretmen sadece KENDİ derslerini görür
        $courses = $this->db->select("
            SELECT c.*, u.name as teacher_name 
            FROM courses c
            LEFT JOIN users u ON u.id = c.teacher_id
            WHERE c.teacher_id = ?
            ORDER BY c.name
        ", [$teacherId]) ?? [];
    }
    
    // ÖĞRENCİLER: Kademeye göre filtrele
    if ($selectedStage !== '') {
        // Sınıf üzerinden kademe filtresi
        $students = $this->db->select("
            SELECT u.id, u.name, u.sinif, u.okul, c.name as class_name, c.stage
            FROM users u
            LEFT JOIN classes c ON c.id = u.class_id
            WHERE u.role = 'student' 
              AND c.stage = ?
            ORDER BY c.name, u.name
        ", [$selectedStage]) ?? [];
    } else {
        // Kademe seçilmemişse tüm öğrenciler (sınıf bilgisiyle)
        $students = $this->db->select("
            SELECT u.id, u.name, u.sinif, u.okul, c.name as class_name, c.stage
            FROM users u
            LEFT JOIN classes c ON c.id = u.class_id
            WHERE u.role = 'student'
            ORDER BY c.stage, c.name, u.name
        ") ?? [];
    }
    
    return [
        'view' => 'teacher_enrollment/view/bulk_assign.php',
        'title' => 'Toplu Ders Atama',
        'courses' => $courses,
        'students' => $students,
        'selectedStage' => $selectedStage,
        'csrf_token' => $this->generateCsrfToken()
    ];
}

public function bulk_assign_store()
{
    if (!$this->validateCsrfToken() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->flashErr('Geçersiz istek');
        header('Location: index.php?module=teacher_enrollment&action=bulk_assign');
        exit;
    }
    
    $courseId = (int)($_POST['course_id'] ?? 0);
    $studentIds = $_POST['student_ids'] ?? [];
    $semesterYear = trim($_POST['semester_year'] ?? '');
    $semesterPeriod = trim($_POST['semester_period'] ?? '');
    
    try {
        $this->db->beginTransaction();
        
        $successCount = 0;
        $skipCount = 0;
        
        foreach ($studentIds as $studentId) {
            $studentId = (int)$studentId;
            
            // Zaten kayıtlı mı?
            $exists = $this->db->fetch("
                SELECT id FROM student_enrollments 
                WHERE student_id = ? AND course_id = ? AND semester_year = ? AND semester_period = ? AND status = 'active'
            ", [$studentId, $courseId, $semesterYear, $semesterPeriod]);
            
            if ($exists) {
                $skipCount++;
                continue;
            }
            
            // Kayıt ekle
            $this->db->execute("
                INSERT INTO student_enrollments (student_id, course_id, semester_year, semester_period, status, enrolled_by_teacher)
                VALUES (?, ?, ?, ?, 'active', 1)
            ", [$studentId, $courseId, $semesterYear, $semesterPeriod]);
            
            $successCount++;
        }
        
        $this->db->commit();
        $this->flashOk("$successCount öğrenci eklendi" . ($skipCount ? ", $skipCount öğrenci zaten kayıtlıydı" : ""));
        
    } catch (\Exception $e) {
        $this->db->rollBack();
        $this->flashErr('Hata: ' . $e->getMessage());
    }
    
    header('Location: index.php?module=teacher_enrollment&action=bulk_assign');
    exit;
}
}