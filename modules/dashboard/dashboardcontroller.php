<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class DashboardController
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

        if ($this->userRole === 'guest') {
            redirect('index.php?module=login&action=index');
            exit;
        }
    }

    public function index()
    {
        $data = ['userRole' => $this->userRole];

        if ($this->userRole === 'admin') {
            // Admin için istatistikler ve hızlı linkler
            $data['stats'] = [
                'students' => $this->db->select("SELECT COUNT(*) as count FROM users WHERE role='student'")[0]['count'] ?? 0,
                'teachers' => $this->db->select("SELECT COUNT(*) as count FROM users WHERE role='teacher'")[0]['count'] ?? 0,
                'pending_requests' => $this->db->select("SELECT COUNT(*) as count FROM students_course_requests WHERE status='pending' OR status='teacher_approved'")[0]['count'] ?? 0,
                'pending_activities' => $this->db->select("SELECT COUNT(*) as count FROM activities WHERE status='pending'")[0]['count'] ?? 0,
                'pending_announcements' => $this->db->select("SELECT COUNT(*) as count FROM announcements WHERE status='pending'")[0]['count'] ?? 0
            ];
            // Diğer hızlı linkler ve bilgiler eklenebilir.

        } elseif ($this->userRole === 'teacher') {
            // Öğretmen için bilgiler
            $data['stats'] = [
                 'my_students_count' => $this->db->select("SELECT COUNT(DISTINCT student_id) as count FROM teachers_students WHERE teacher_id = ?", [$this->userId])[0]['count'] ?? 0,
                 'my_courses_count' => $this->db->select("SELECT COUNT(*) as count FROM courses WHERE teacher_id = ?", [$this->userId])[0]['count'] ?? 0,
                 'pending_course_requests_for_me' => $this->db->select(
                    "SELECT COUNT(*) as count FROM students_course_requests scr JOIN courses c ON scr.item_id = c.id AND scr.item_type = 'course' WHERE c.teacher_id = ? AND scr.status = 'pending'",
                    [$this->userId]
                 )[0]['count'] ?? 0,
            ];
            // Bu hafta devamsızlık yapanlar (örnek, attendance tablosu ve mantığına göre uyarlanmalı)
            // $data['absent_this_week'] = $this->db->select("SELECT COUNT(DISTINCT student_id) FROM attendance WHERE teacher_id = ? AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status = 'absent'", [$this->userId]);
            // Bu hafta verilen ödevler (assignments tablosuna göre)

        } elseif ($this->userRole === 'student') {
            // Öğrenci için bilgiler
            $data['my_active_courses_count'] = $this->db->select("SELECT COUNT(*) as count FROM student_enrollments WHERE student_id = ? AND status = 'active'", [$this->userId])[0]['count'] ?? 0;
            $data['my_pending_requests_count'] = $this->db->select("SELECT COUNT(*) as count FROM students_course_requests WHERE student_id = ? AND status = 'pending'", [$this->userId])[0]['count'] ?? 0;
        }
        // Veli için benzer bir yapı eklenebilir.

        // Tüm roller için ortak: Son Duyurular, Etkinlikler, Okunmamış Bildirimler
        $data['recent_announcements'] = $this->db->select("SELECT id, title, created_at FROM announcements WHERE (target_role IS NULL OR target_role = ? OR target_role = 'all') AND is_active = 1 ORDER BY created_at DESC LIMIT 3", [$this->userRole]);
        $data['upcoming_activities'] = $this->db->select("SELECT id, title, activity_date FROM activities WHERE status = 'approved' AND activity_date >= CURDATE() ORDER BY activity_date ASC LIMIT 3");
        
        // Okunmamış bildirim sayısı (NotificationsController'daki check mantığına benzer)
        $unread_notifications_sql = "SELECT COUNT(n.id) as count FROM notifications n
            LEFT JOIN notification_read_status rs ON n.id = rs.notification_id AND rs.user_id = ?
            WHERE rs.id IS NULL AND (
                (n.target_role IS NULL AND n.target_user_id IS NULL) OR 
                n.target_role = ? OR 
                n.target_user_id = ?
            )";
        $data['unread_notifications_count'] = $this->db->select($unread_notifications_sql, [$this->userId, $this->userRole, $this->userId])[0]['count'] ?? 0;


        return $data;
    }
}