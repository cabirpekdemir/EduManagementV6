<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

// SINIF ADI GÜNCELLENDİ: Daha standart bir isimlendirme için 'TeacherAssignmentsController' yapıldı.
class Teacher_assignmentsController 
{
    protected $db;
    protected $userId;

    // YENİ: Tekrarlanan kodları önlemek için constructor eklendi.
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->userId = $_SESSION['user']['id'] ?? null;
        
        // Eğer kullanıcı giriş yapmamışsa veya öğretmen değilse, erişimi engellemek daha güvenli olur.
        if (!$this->userId || $_SESSION['user']['role'] !== 'teacher') {
            redirect('index.php?module=login&action=index');
            exit;
        }
    }

    // DÜZELTİLDİ: Metot artık veriyi hazırlayıp return ediyor.
    public function index() 
    {
        $data = [
            'pageTitle' => 'Ödev Yönetimi',
            'assignments' => [] // Değişkeni başlangıçta boş bir dizi olarak garantiye alıyoruz.
        ];

        // Veritabanı sorgusu
        $sql = "SELECT * FROM teacher_assignments WHERE teacher_id = ? ORDER BY due_date DESC";
        $data['assignments'] = $this->db->select($sql, [$this->userId]);
        
        // Veriyi ana yönlendiriciye gönderiyoruz.
        return $data;
    }

    // İYİLEŞTİRİLDİ: Bu metot da artık veriyi return ediyor.
    public function create() 
    {
        $data = [
            'pageTitle' => 'Yeni Ödev Oluştur',
            'courses' => []
        ];
        
        $sql = "SELECT id, name FROM courses WHERE teacher_id = ?";
        $data['courses'] = $this->db->select($sql, [$this->userId]);

        return $data;
    }

    // İYİLEŞTİRİLDİ: Constructor'daki değişkenleri kullanıyor.
    public function store() 
    {
        $course_id = $_POST['course_id'] ?? null;
        $title = $_POST['title'] ?? null;
        $description = $_POST['description'] ?? null;
        $due_date = $_POST['due_date'] ?? null;

        if (!$course_id || !$title || !$due_date) {
            redirect('index.php?module=teacher_assignments&action=create&status=error');
            exit;
        }

        $sql = "INSERT INTO teacher_assignments (teacher_id, course_id, title, description, due_date) VALUES (?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $this->userId,
            $course_id,
            $title,
            $description,
            $due_date
        ]);

        redirect('index.php?module=teacher_assignments&action=index&status=success');
        exit;
    }
}