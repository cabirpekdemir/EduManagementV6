// modules/enrollment/teacherenrollment.php
<?php
class TeacherEnrollmentManager
{
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Öğretmen öğrenciyi proje grubuna ekler
     */
    public function addStudentToProjectGroup(int $teacherId, int $studentId, int $courseId): bool
    {
        $course = $this->db->fetch(
            "SELECT * FROM courses WHERE id = ? AND teacher_id = ? AND course_category = 'proje'",
            [$courseId, $teacherId]
        );
        
        if (!$course) {
            return false; // Sadece kendi proje derslerine ekleyebilir
        }
        
        // Sınıf seviyesi farketmeksizin ekle
        $this->db->execute(
            "INSERT INTO student_enrollments (student_id, course_id, status, enrolled_by_teacher) 
             VALUES (?, ?, 'active', 1)",
            [$studentId, $courseId]
        );
        
        return true;
    }
    
    /**
     * Akademi grubu için öğrenci ekleme (sadece Resim/Müzik öğretmenleri)
     */
    public function addStudentToAcademy(int $teacherId, int $studentId, int $courseId): bool
    {
        $course = $this->db->fetch(
            "SELECT * FROM courses 
             WHERE id = ? AND teacher_id = ? AND course_category = 'akademi'",
            [$courseId, $teacherId]
        );
        
        if (!$course) {
            return false;
        }
        
        // Birden fazla dönem alabilir
        $this->db->execute(
            "INSERT INTO student_enrollments (student_id, course_id, status, enrolled_by_teacher) 
             VALUES (?, ?, 'active', 1)",
            [$studentId, $courseId]
        );
        
        return true;
    }
}