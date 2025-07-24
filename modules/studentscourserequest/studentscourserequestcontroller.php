<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class StudentsCourseRequestController 
{
    protected $db;
    protected $userId;
    protected $userRole;

    // YENİ: Tekrarlanan kodları önlemek için constructor eklendi.
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->userId = $_SESSION['user']['id'] ?? null;
        $this->userRole = $_SESSION['user']['role'] ?? null;
    }

    // DÜZELTİLDİ: Metot artık veriyi hazırlayıp return ediyor.
    public function index() 
    {
        $data = [
            'pageTitle' => 'Ders Taleplerim',
            'requests' => [] // Değişkeni başlangıçta boş bir dizi olarak garantiye alıyoruz.
        ];

        // Sadece öğrenci veya veli ise talepleri listele
        if ($this->userRole === 'student' || $this->userRole === 'parent') {
            $sql = "
                SELECT sc.id, c.name AS course_name, u.name AS teacher_name, sc.status
                FROM students_course_requests sc
                INNER JOIN courses c ON sc.course_id = c.id
                LEFT JOIN users u ON c.teacher_id = u.id
                WHERE sc.student_id = ?
                ORDER BY sc.requested_at DESC
            ";
            $data['requests'] = $this->db->select($sql, [$this->userId]);
        }
        
        // Veriyi ana yönlendiriciye (index.php) gönderiyoruz.
        return $data;
    }

    // İYİLEŞTİRİLDİ: Bu metot da artık veriyi return ediyor.
    public function create() 
    {
        $data = [
            'pageTitle' => 'Yeni Ders Talebi Oluştur',
            'courses' => $this->db->select("SELECT id, name FROM courses ORDER BY name ASC")
        ];
        
        return $data;
    }

    // İYİLEŞTİRİLDİ: Constructor'daki değişkenleri kullanıyor.
    public function store() 
    {
        $selectedCourses = $_POST['courses'] ?? [];

        if (!$this->userId || empty($selectedCourses)) {
            // Hata yönetimi için bir mesaj eklemek daha iyi olur.
            redirect('index.php?module=studentscourserequest&action=index&status=error');
            exit;
        }

        foreach ($selectedCourses as $courseId) {
            $this->db->query(
                "INSERT INTO students_course_requests (student_id, course_id, status, requested_at) VALUES (?, ?, 'pending', NOW())", 
                [$this->userId, $courseId]
            );
        }

        redirect('index.php?module=studentscourserequest&action=index&status=success');
        exit;
    }
}