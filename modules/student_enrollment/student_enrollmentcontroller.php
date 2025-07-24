<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class Student_enrollmentController
{
    protected $db;
    protected $currentUser;
    protected $studentId;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->currentUser = $_SESSION['user'] ?? null;

        if (!$this->currentUser || $this->currentUser['role'] !== 'student') {
            log_activity('ACCESS_DENIED', 'StudentEnrollment', null, 'Yetkisiz erişim denemesi (öğrenci değil).');
            die("⛔ Bu modüle sadece öğrenciler erişebilir!");
        }
        $this->studentId = $this->currentUser['id'];
    }

    public function index()
    {
        $all_courses_raw = $this->db->select(
            "SELECT DISTINCT c.id, c.name, c.description 
             FROM courses c
             LEFT JOIN course_group_items cgi ON c.id = cgi.course_id
             WHERE cgi.course_id IS NULL OR cgi.is_individually_selectable = TRUE
             ORDER BY c.name ASC"
        );
        
        $all_groups_raw = $this->db->select("SELECT id, name, description FROM course_groups ORDER BY name ASC");
        $all_groups = [];
        foreach ($all_groups_raw as $group) {
            $courses_in_group = $this->db->select(
                "SELECT c.id, c.name FROM courses c 
                 JOIN course_group_items cgi ON c.id = cgi.course_id 
                 WHERE cgi.course_group_id = ?",
                [$group['id']]
            );
            $group['courses'] = $courses_in_group;

            $group_enrollment_check = $this->db->select(
                "SELECT id FROM student_enrollments WHERE student_id = ? AND course_group_id = ? AND status = 'active'",
                [$this->studentId, $group['id']]
            );
            $group['is_student_enrolled_in_group'] = !empty($group_enrollment_check);
            $all_groups[] = $group;
        }

        $my_requests_query = "SELECT scr.*, 
            CASE scr.item_type
                WHEN 'course' THEN c.name
                WHEN 'group' THEN cg.name
            END as item_name
            FROM students_course_requests scr
            LEFT JOIN courses c ON scr.item_id = c.id AND scr.item_type = 'course'
            LEFT JOIN course_groups cg ON scr.item_id = cg.id AND scr.item_type = 'group'
            WHERE scr.student_id = ? 
            ORDER BY scr.request_date DESC";
        $my_requests = $this->db->select($my_requests_query, [$this->studentId]);

        $enrolled_course_ids = [];
        $direct_enrollments = $this->db->select("SELECT course_id FROM student_enrollments WHERE student_id = ? AND status = 'active' AND course_id IS NOT NULL", [$this->studentId]);
        foreach($direct_enrollments as $de){ $enrolled_course_ids[] = $de['course_id']; }

        $group_enrollments_for_individual_courses = $this->db->select("SELECT course_group_id FROM student_enrollments WHERE student_id = ? AND course_group_id IS NOT NULL AND status = 'active'", [$this->studentId]);
        foreach($group_enrollments_for_individual_courses as $ge){
            $courses_in_enrolled_group = $this->db->select("SELECT course_id FROM course_group_items WHERE course_group_id = ?", [$ge['course_group_id']]);
            foreach($courses_in_enrolled_group as $cieg){ $enrolled_course_ids[] = $cieg['course_id']; }
        }
        $enrolled_course_ids = array_unique($enrolled_course_ids);

        return [
            'all_courses' => $all_courses_raw,
            'all_groups' => $all_groups,
            'my_requests' => $my_requests,
            'enrolled_course_ids' => $enrolled_course_ids,
            'success_message' => $_GET['success_message'] ?? null,
            'error_message' => $_GET['error_message'] ?? null
        ];
    }
    
    public function make_request()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?module=student_enrollment&action=index');
            exit;
        }

        $item_id = $_POST['item_id'] ?? 0;
        $item_type = $_POST['item_type'] ?? '';

        if (!$item_id || !in_array($item_type, ['course', 'group'])) {
            redirect('index.php?module=student_enrollment&action=index&error_message=Geçersiz istek.');
            exit;
        }
        
        if ($item_type === 'course') {
            $course_rules = $this->db->select(
                "SELECT cgi.is_individually_selectable FROM courses c
                 LEFT JOIN course_group_items cgi ON c.id = cgi.course_id
                 WHERE c.id = ? AND cgi.course_id IS NOT NULL AND cgi.is_individually_selectable = FALSE",
                [$item_id]
            );
            if (!empty($course_rules)) {
                 redirect('index.php?module=student_enrollment&action=index&error_message=Bu ders sadece bir grup içinde seçilebilir.');
                 exit;
            }
        }

        $existing_request = $this->db->select(
            "SELECT id FROM students_course_requests WHERE student_id = ? AND item_id = ? AND item_type = ? AND status = 'pending'",
            [$this->studentId, $item_id, $item_type]
        );
        if (!empty($existing_request)) {
            redirect('index.php?module=student_enrollment&action=index&error_message=Bu ders/grup için zaten beklemede bir isteğiniz var.');
            exit;
        }
        
        if ($item_type === 'course') {
            $is_enrolled = $this->db->select("SELECT id FROM student_enrollments WHERE student_id = ? AND course_id = ? AND status = 'active'", [$this->studentId, $item_id]);
            if(!empty($is_enrolled)){
                 redirect('index.php?module=student_enrollment&action=index&error_message=Bu derse zaten kayıtlısınız.');
                 exit;
            }
        }
        
        if ($item_type === 'group') {
            $is_enrolled_group = $this->db->select("SELECT id FROM student_enrollments WHERE student_id = ? AND course_group_id = ? AND status = 'active'", [$this->studentId, $item_id]);
             if(!empty($is_enrolled_group)){
                 redirect('index.php?module=student_enrollment&action=index&error_message=Bu ders grubuna zaten kayıtlısınız.');
                 exit;
            }
        }
        
        // DÜZELTME: Hatalı olan 'query' metodu yerine, projenin kullandığı doğru yöntem olan 'prepare/execute' kullanıldı.
        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO students_course_requests (student_id, item_id, item_type, status) VALUES (?, ?, ?, 'pending')"
        );
        $stmt->execute([$this->studentId, $item_id, $item_type]);
        $request_id = $this->db->getConnection()->lastInsertId();

        log_activity('REQUEST_COURSE_GROUP', 'StudentEnrollment', $request_id, "Öğrenci (ID: {$this->studentId}) {$item_type} (ID: {$item_id}) için istekte bulundu.");
        redirect('index.php?module=student_enrollment&action=index&success_message=İsteğiniz başarıyla alındı.');
    }

    public function cancel_request()
    {
        $request_id = $_GET['request_id'] ?? 0;

        if (!$request_id) {
            redirect('index.php?module=student_enrollment&action=index&error_message=Geçersiz istek IDsi.');
            exit;
        }

        $request = $this->db->select(
            "SELECT id, item_id, item_type FROM students_course_requests WHERE id = ? AND student_id = ? AND status = 'pending'",
            [$request_id, $this->studentId]
        );

        if (empty($request)) {
            redirect('index.php?module=student_enrollment&action=index&error_message=İptal edilecek geçerli bir istek bulunamadı veya yetkiniz yok.');
            exit;
        }

        // DÜZELTME: Bu metodda da 'query' yerine 'prepare/execute' kullanıldı.
        $stmt = $this->db->getConnection()->prepare(
            "UPDATE students_course_requests SET status = 'cancelled_by_student', processed_at = CURRENT_TIMESTAMP WHERE id = ?"
        );
        $stmt->execute([$request_id]);

        log_activity('CANCEL_REQUEST', 'StudentEnrollment', $request_id, "Öğrenci (ID: {$this->studentId}) {$request[0]['item_type']} (ID: {$request[0]['item_id']}) için yaptığı isteği iptal etti.");
        redirect('index.php?module=student_enrollment&action=index&success_message=İsteğiniz başarıyla iptal edildi.');
    }
}