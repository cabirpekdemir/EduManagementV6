<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class Course_requestsController
{
    protected $db;
    protected $currentUser;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->currentUser = $_SESSION['user'] ?? null;

        if (!$this->currentUser || !in_array($this->currentUser['role'], ['admin', 'teacher'])) {
            log_activity('ACCESS_DENIED', 'CourseRequests', null, 'Yetkisiz erişim denemesi (admin/öğretmen değil).');
            die("⛔ Bu modüle sadece adminler ve öğretmenler erişebilir!");
        }
    }

    /**
     * Adminler için tüm bekleyen ('pending', 'teacher_approved') istekleri,
     * Öğretmenler için kendi sorumlu olduğu derslerle ilgili 'pending' durumundaki istekleri listeler.
     */
    public function index()
    {
        $sql = "SELECT scr.id as request_id, scr.student_id, u_student.name as student_name, 
                       scr.item_id, scr.item_type, scr.request_date, scr.status as request_status,
                       scr.notes as request_notes,
                       CASE scr.item_type
                           WHEN 'course' THEN c.name
                           WHEN 'group' THEN cg.name
                       END as item_name,
                       c.teacher_id as course_teacher_id 
                FROM students_course_requests scr
                JOIN users u_student ON scr.student_id = u_student.id
                LEFT JOIN courses c ON scr.item_id = c.id AND scr.item_type = 'course'
                LEFT JOIN course_groups cg ON scr.item_id = cg.id AND scr.item_type = 'group'
                WHERE scr.status NOT IN ('cancelled_by_student', 'admin_approved', 'rejected')";

        $params = [];

        if ($this->currentUser['role'] === 'teacher') {
            // Öğretmenler sadece 'pending' durumundaki ve kendi dersleriyle ilgili bireysel ders isteklerini görür.
            $sql .= " AND scr.status = 'pending' AND scr.item_type = 'course' AND c.teacher_id = ?";
            $params[] = $this->currentUser['id'];
        }
        // Admin tüm geçerli istekleri görür (pending, teacher_approved)

        $sql .= " ORDER BY scr.request_date ASC";
        
        $requests = $this->db->select($sql, $params);

        return [
            'requests' => $requests,
            'userRole' => $this->currentUser['role'],
            'currentUserId' => $this->currentUser['id'], // View'da öğretmen ID'sini kontrol için
            'success_message' => $_GET['success_message'] ?? null,
            'error_message' => $_GET['error_message'] ?? null
        ];
    }

    /**
     * Bir ders/grup isteğini işler (onaylar veya reddeder).
     */
    public function process_request()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?module=course_requests&action=index');
            exit;
        }

        $request_id = $_POST['request_id'] ?? 0;
        $new_status = $_POST['new_status'] ?? ''; // 'teacher_approved', 'admin_approved', 'rejected'
        $notes = $_POST['notes'] ?? '';

        if (!$request_id || !in_array($new_status, ['teacher_approved', 'admin_approved', 'rejected'])) {
            redirect('index.php?module=course_requests&action=index&error_message=Geçersiz işlem veya durum.');
            exit;
        }

        $request = $this->db->select("SELECT * FROM students_course_requests WHERE id = ?", [$request_id])[0] ?? null;
        if (!$request) {
            redirect('index.php?module=course_requests&action=index&error_message=İstek bulunamadı.');
            exit;
        }

        // Yetki kontrolleri
        $can_process = false;
        if ($this->currentUser['role'] === 'admin') {
            $can_process = true; // Admin her zaman işleyebilir
        } elseif ($this->currentUser['role'] === 'teacher') {
            if (($new_status === 'teacher_approved' || $new_status === 'rejected') && $request['status'] === 'pending' && $request['item_type'] === 'course') {
                $course = $this->db->select("SELECT teacher_id FROM courses WHERE id = ?", [$request['item_id']])[0] ?? null;
                if ($course && $course['teacher_id'] == $this->currentUser['id']) {
                    $can_process = true;
                }
            }
        }

        if (!$can_process) {
             log_activity('PROCESS_REQUEST_DENIED', 'CourseRequests', $request_id, "Yetkisiz işlem denemesi. Durum: {$new_status}");
             redirect('index.php?module=course_requests&action=index&error_message=Bu işlemi yapma yetkiniz yok.');
             exit;
        }

        $stmt = $this->db->getConnection()->prepare(
            "UPDATE students_course_requests SET status = ?, notes = ?, processed_by_user_id = ?, processed_at = CURRENT_TIMESTAMP WHERE id = ?"
        );
        $stmt->execute([$new_status, $notes, $this->currentUser['id'], $request_id]);

        log_activity(strtoupper($new_status), 'CourseRequests', $request_id, "İstek {$request_id} durumu güncellendi: {$new_status}. Notlar: {$notes}");

        // Eğer admin onayı geldiyse, öğrenciyi derse/derslere kaydet
        if ($new_status === 'admin_approved') {
            $this->enrollStudentFromRequest($request);
        }
        
        redirect('index.php?module=course_requests&action=index&success_message=İstek başarıyla işlendi.');
    }

    /**
     * Onaylanmış bir isteğe göre öğrenciyi derse/derslere kaydeder.
     */
    private function enrollStudentFromRequest($request)
    {
        $student_id = $request['student_id'];
        $assigned_by_user_id = $this->currentUser['id']; // İşlemi yapan admin veya öğretmen
        $request_id_for_enrollment = $request['id'];

        if ($request['item_type'] === 'course') {
            $course_id = $request['item_id'];
            $existing_enrollment = $this->db->select("SELECT id FROM student_enrollments WHERE student_id = ? AND course_id = ? AND status = 'active'", [$student_id, $course_id]);
            if (empty($existing_enrollment)) {
                $stmt = $this->db->getConnection()->prepare(
                    "INSERT INTO student_enrollments (student_id, course_id, request_id, assigned_by_user_id, status) VALUES (?, ?, ?, ?, 'active')"
                );
                $stmt->execute([$student_id, $course_id, $request_id_for_enrollment, $assigned_by_user_id]);
                log_activity('ENROLL', 'StudentEnrollments', $this->db->getConnection()->lastInsertId(), "Öğrenci (ID: {$student_id}) derse (ID: {$course_id}) istek (ID: {$request_id_for_enrollment}) üzerine kaydedildi.");
            }
        } elseif ($request['item_type'] === 'group') {
            $course_group_id = $request['item_id'];
            $courses_in_group = $this->db->select("SELECT course_id FROM course_group_items WHERE course_group_id = ?", [$course_group_id]);
            
            foreach ($courses_in_group as $course_item) {
                $course_id = $course_item['course_id'];
                $existing_enrollment = $this->db->select("SELECT id FROM student_enrollments WHERE student_id = ? AND course_id = ? AND status = 'active'", [$student_id, $course_id]);
                 if (empty($existing_enrollment)) {
                    $stmt = $this->db->getConnection()->prepare(
                        "INSERT INTO student_enrollments (student_id, course_id, course_group_id, request_id, assigned_by_user_id, status) VALUES (?, ?, ?, ?, ?, 'active')"
                    );
                    $stmt->execute([$student_id, $course_id, $course_group_id, $request_id_for_enrollment, $assigned_by_user_id]);
                    log_activity('ENROLL_VIA_GROUP', 'StudentEnrollments', $this->db->getConnection()->lastInsertId(), "Öğrenci (ID: {$student_id}) derse (ID: {$course_id}) grup (ID: {$course_group_id}) isteği (ID: {$request_id_for_enrollment}) üzerine kaydedildi.");
                }
            }
        }
    }

    /**
     * Adminin doğrudan öğrenciye ders/grup atama formunu gösterir.
     */
    public function assign_item_form() {
        if ($this->currentUser['role'] !== 'admin') {
            log_activity('ACCESS_DENIED', 'CourseRequests', null, 'Doğrudan atama formu için yetkisiz erişim.');
            die("Yetkiniz yok.");
        }
        $students = $this->db->select("SELECT id, name FROM users WHERE role = 'student' ORDER BY name ASC");
        $courses = $this->db->select("SELECT id, name FROM courses ORDER BY name ASC");
        $course_groups = $this->db->select("SELECT id, name FROM course_groups ORDER BY name ASC");

        return [
            'students' => $students,
            'courses' => $courses,
            'course_groups' => $course_groups,
            'error_message' => $_GET['error_message'] ?? null,
            'success_message' => $_GET['success_message'] ?? null
        ];
    }

    /**
     * Adminin doğrudan öğrenciye ders/grup atamasını kaydeder.
     * Bu işlem öğrenciyi direkt 'student_enrollments' tablosuna ekler.
     */
    public function assign_item_store() {
        if ($this->currentUser['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
             redirect('index.php?module=course_requests&action=assign_item_form&error_message=Yetkisiz işlem.');
             exit;
        }

        $student_id = $_POST['student_id'] ?? 0;
        $item_type = $_POST['item_type'] ?? '';
        $item_id = ($item_type === 'course') ? ($_POST['course_id'] ?? 0) : ($_POST['course_group_id'] ?? 0);

        if (!$student_id || !$item_id || !in_array($item_type, ['course', 'group'])) {
            redirect('index.php?module=course_requests&action=assign_item_form&error_message=Eksik veya geçersiz bilgi.');
            exit;
        }
        
        // enrollStudentFromRequest metodunu kullanmak için sahte bir request objesi oluşturuyoruz,
        // çünkü o metod hem bireysel hem de grup dersleri için kayıt mantığını içeriyor.
        // Doğrudan atamada 'request_id' olmayacak.
        $fake_request_for_enrollment = [
            'student_id' => $student_id,
            'item_id' => $item_id,
            'item_type' => $item_type,
            'id' => null // Gerçek bir request ID değil, çünkü bu doğrudan atama
        ];
        // assigned_by_user_id enrollStudentFromRequest içinde $this->currentUser['id'] olarak ayarlanır.
        $this->enrollStudentFromRequest($fake_request_for_enrollment);

        log_activity('DIRECT_ASSIGN', 'CourseRequests', null, "Admin (ID: {$this->currentUser['id']}) öğrenciye (ID: {$student_id}) {$item_type} (ID: {$item_id}) doğrudan atadı.");
        redirect('index.php?module=course_requests&action=assign_item_form&success_message=Atama başarıyla yapıldı.');
    }
}