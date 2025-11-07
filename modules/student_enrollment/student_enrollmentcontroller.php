<?php
// modules/student_enrollment/studentenrollmentcontroller.php
// YENİ VERSİYON - Öğrenciler sadece görüntüler

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../enrollment/enrollmentrules.php';

class Student_enrollmentController
{
    protected $db;
    protected $rules;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->rules = new EnrollmentRules();
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $this->checkAuth();
    }

    private function checkAuth(): void
    {
        $role = currentRole();
        if ($role === 'guest') {
            $_SESSION['form_error'] = 'Bu alana erişim için giriş yapmalısınız.';
            header('Location: index.php?module=login');
            exit;
        }
    }

    /**
     * Öğrenci sadece kendi derslerini görür (ATANAMAZ)
     */
    public function index()
    {
        $studentId = currentUserId();
        $role = currentRole();
        
        if ($role !== 'student') {
            $_SESSION['form_error'] = 'Bu sayfa sadece öğrenciler içindir';
            header('Location: index.php');
            exit;
        }
        
        $student = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$studentId]);
        $semester = $this->rules->getCurrentSemester();
        
        // Sadece atanmış dersler
        $myEnrollments = $this->db->select("
            SELECT se.*, c.name as course_name, c.course_category,
                   t.name as teacher_name,
                   GROUP_CONCAT(DISTINCT k.name ORDER BY k.name SEPARATOR ', ') AS class_names,
                   GROUP_CONCAT(
                       DISTINCT CONCAT(
                           CASE ct.day
                             WHEN 1 THEN 'Pazartesi'
                             WHEN 2 THEN 'Salı'
                             WHEN 3 THEN 'Çarşamba'
                             WHEN 4 THEN 'Perşembe'
                             WHEN 5 THEN 'Cuma'
                             WHEN 6 THEN 'Cumartesi'
                             WHEN 7 THEN 'Pazar'
                             ELSE 'Gün'
                           END,
                           ' ',
                           DATE_FORMAT(ct.start_time,'%H:%i'),'-',DATE_FORMAT(ct.end_time,'%H:%i')
                       )
                       ORDER BY ct.day, ct.start_time
                       SEPARATOR ', '
                   ) AS time_slots
            FROM student_enrollments se
            JOIN courses c ON c.id = se.course_id
            LEFT JOIN users t ON t.id = c.teacher_id
            LEFT JOIN course_classes cc ON cc.course_id = c.id
            LEFT JOIN classes k ON k.id = cc.class_id
            LEFT JOIN course_times ct ON ct.course_id = c.id
            WHERE se.student_id = ? AND se.status = 'active'
            GROUP BY se.id, c.name, c.course_category, t.name
            ORDER BY c.name
        ", [$studentId]) ?? [];
        
        return [
            'view' => 'student_enrollment/view/index.php',
            'title' => 'Derslerim',
            'student' => $student,
            'semester' => $semester,
            'my_enrollments' => $myEnrollments
        ];
    }
    
    /**
     * Öğrencinin geçmiş kayıtları
     */
    public function history()
    {
        $studentId = currentUserId();
        
        $history = $this->db->select("
            SELECT se.*, c.name as course_name, c.course_category,
                   se.semester_year, se.semester_period, se.grade, se.is_completed
            FROM student_enrollments se
            JOIN courses c ON c.id = se.course_id
            WHERE se.student_id = ?
            ORDER BY se.semester_year DESC, se.semester_period DESC, c.name
        ", [$studentId]) ?? [];
        
        return [
            'view' => 'student_enrollment/view/history.php',
            'title' => 'Ders Geçmişim',
            'history' => $history
        ];
    }
}