<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

// SINIF ADI GÜNCELLENDİ: Standart isimlendirme için 'StudentCoursesController' yapıldı.
class StudentCoursesController 
{
    protected $db;
    protected $currentUser;
    protected $userId;
    protected $userRole;

    // YENİ: Tekrarlanan kodları önlemek için constructor eklendi.
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->currentUser = $_SESSION['user'] ?? null;
        $this->userId = $this->currentUser['id'] ?? null;
        $this->userRole = $this->currentUser['role'] ?? null;

        // Giriş yapmamış kullanıcıları engelle
        if (!$this->userId) {
            redirect('index.php?module=login&action=index');
            exit;
        }
    }

    // DÜZELTİLDİ: Metot artık çıktıyı yakalayıp layout'u kendisi dahil ediyor.
    public function index() 
    {
        $entries_data = []; // View'a aktarılacak değişken adı

        if ($this->userRole === 'teacher') {
            // Öğretmen sadece kendi derslerine yapılan kayıtları görür
            $sql = "
                SELECT sc.id, u.name AS student_name, c.name AS course_name, sc.status
                FROM students_courses sc
                JOIN users u ON sc.student_id = u.id
                JOIN courses c ON sc.course_id = c.id
                WHERE c.teacher_id = ?
                ORDER BY sc.id DESC
            ";
            $entries_data = $this->db->select($sql, [$this->userId]);

        } elseif ($this->userRole === 'student') {
            // Öğrenci sadece kendi kayıtlarını görür
             $sql = "
                SELECT sc.id, u.name AS student_name, c.name AS course_name, sc.status
                FROM students_courses sc
                JOIN users u ON sc.student_id = u.id
                JOIN courses c ON sc.course_id = c.id
                WHERE sc.student_id = ?
                ORDER BY sc.id DESC
            ";
            $entries_data = $this->db->select($sql, [$this->userId]);
        }
        else {
            // Admin veya diğer roller tümünü görür
            $sql = "
                SELECT sc.id, u.name AS student_name, c.name AS course_name, sc.status
                FROM students_courses sc
                JOIN users u ON sc.student_id = u.id
                JOIN courses c ON sc.course_id = c.id
                ORDER BY sc.id DESC
            ";
            $entries_data = $this->db->select($sql);
        }
        
        // DÜZELTME: Çıktıyı yakalama ve layout'u dahil etme
        ob_start();
        $viewPath = __DIR__ . '/../../themes/default/pages/studentcourses/index.php'; 
        
        if (file_exists($viewPath)) {
            // View dosyasına aktarılacak değişkenler
            $entries = $entries_data; // View'da kullanılan değişken adı
            $pageTitle = 'Ders Kayıtları'; // View'da kullanabileceğiniz başlık
            $status_message = $_GET['status'] ?? null; // URL'den gelen status mesajını aktarma
            
            include $viewPath;
        } else {
            log_activity('VIEW_ERROR', 'StudentCourses', null, "Index View dosyası bulunamadı: " . $viewPath);
            echo "Index View file not found: " . $viewPath;
        }
        $pageContent = ob_get_clean();
        include __DIR__ . '/../../themes/default/layout.php'; 
    }
    
    // İYİLEŞTİRİLDİ: Mimariye uygun hale getirildi ve çıktıyı yakalıyor.
    public function create() 
    {
        // DÜZELTME: Çıktıyı yakalama ve layout'u dahil etme
        ob_start();
        $viewPath = __DIR__ . '/../../themes/default/pages/studentcourses/create.php'; // create view'ınızın yolu
        
        if (file_exists($viewPath)) {
            $pageTitle = 'Derse Kayıt Ol'; // View'da kullanabileceğiniz başlık
            // Gerekirse ders listesini de burada çekip aktarabilirsiniz.
            // Örneğin: $all_courses = $this->db->select("SELECT id, name FROM courses");
            // include $viewPath;
            include $viewPath;
        } else {
            log_activity('VIEW_ERROR', 'StudentCourses', null, "Create View dosyası bulunamadı: " . $viewPath);
            echo "Create View file not found: " . $viewPath;
        }
        $pageContent = ob_get_clean();
        include __DIR__ . '/../../themes/default/layout.php'; 
    }

    // İYİLEŞTİRİLDİ: Constructor'daki değişkenleri kullanıyor ve query() yerine prepare/execute.
    public function store() 
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?module=studentcourses&action=index');
            exit;
        }

        $course_ids = $_POST['course_ids'] ?? [];
        if (empty($course_ids)) {
            redirect('index.php?module=studentcourses&action=index&status=no_selection');
            exit;
        }

        try {
            foreach ($course_ids as $course_id) {
                $course_id = intval($course_id);
                
                // Öğrencinin bu derse zaten kayıtlı olup olmadığını kontrol et
                $existing = $this->db->select("SELECT COUNT(*) as count FROM students_courses WHERE student_id = ? AND course_id = ?", [$this->userId, $course_id]);
                
                if ($existing[0]['count'] == 0) {
                    // DÜZELTME: query() yerine prepare/execute kullanıldı.
                    $stmt = $this->db->getConnection()->prepare(
                        "INSERT INTO students_courses (student_id, course_id, status) VALUES (?, ?, 'bekliyor')"
                    );
                    $stmt->execute([$this->userId, $course_id]);
                    log_activity('COURSE_ENROLL_REQUEST', 'StudentCourses', $this->db->getConnection()->lastInsertId(), "Öğrenci {$this->userId} ders {$course_id} için kayıt isteği gönderdi.");
                }
            }
            redirect('index.php?module=studentcourses&action=index&status=success');
        } catch (PDOException $e) {
            log_activity('DB_ERROR', 'StudentCourses', null, "Ders kaydı sırasında veritabanı hatası: " . $e->getMessage());
            redirect('index.php?module=studentcourses&action=index&status=db_error');
        }
        exit;
    }

    // İYİLEŞTİRİLDİ: Constructor'daki değişkenleri kullanıyor ve query() yerine prepare/execute.
    public function updateStatus() 
    {
        // Bu işlemi sadece öğretmen veya admin yapabilmeli
        if ($this->userRole !== 'teacher' && $this->userRole !== 'admin') {
            redirect('index.php?module=home&action=index&status=auth_error');
            exit;
        }

        $id = intval($_GET['id'] ?? 0);
        $newStatus = ($_GET['status'] ?? '') === 'onayla' ? 'onaylandı' : 'reddedildi';

        if ($id > 0) {
            try {
                // DÜZELTME: query() yerine prepare/execute kullanıldı.
                $stmt = $this->db->getConnection()->prepare("UPDATE students_courses SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $id]);
                log_activity('ENROLL_STATUS_UPDATE', 'StudentCourses', $id, "Ders kayıt durumu güncellendi. ID: {$id}, Yeni Durum: {$newStatus}");
                redirect('index.php?module=studentcourses&action=index&status=updated');
            } catch (PDOException $e) {
                log_activity('DB_ERROR', 'StudentCourses', null, "Ders durumu güncellenirken veritabanı hatası: " . $e->getMessage());
                redirect('index.php?module=studentcourses&action=index&status=db_error');
            }
        } else {
            redirect('index.php?module=studentcourses&action=index&status=invalid_id');
        }
        exit;
    }
}