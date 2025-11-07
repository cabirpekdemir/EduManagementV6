<?php
// /modules/parents_students/parents_studentscontroller.php

// Session başlatma (mesajlaşma ve güvenlik token'ı için gerekli)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../core/database.php';

class Parents_StudentsController {

    private $db;
    private $csrf_token;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        
        // Her sayfa yüklemesinde CSRF token'ını hazırla
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $this->csrf_token = $_SESSION['csrf_token'];
    }
    
    /**
     * Veli-Öğrenci ilişkilerini listeler.
     */
    public function index() {
        $pageTitle = "Veli - Öğrenci İlişkileri"; // layout.php için sayfa başlığı
        $csrf_token = $this->csrf_token;

        $relations = $this->db->query("SELECT ps.parent_id, ps.student_id, p.name AS parent_name, s.name AS student_name 
                                     FROM parents_students ps
                                     JOIN users p ON ps.parent_id = p.id AND p.role = 'parent'
                                     JOIN users s ON ps.student_id = s.id AND s.role = 'student'
                                     ORDER BY parent_name, student_name")->fetchAll(PDO::FETCH_ASSOC);
        
        // View dosyasını dahil et ve layout'a gönder
        ob_start();
        include __DIR__ . '/index.php';
        $pageContent = ob_get_clean();
        require_once __DIR__ . '/../../themes/default/layout.php';
    }

    /**
     * Yeni Veli-Öğrenci ilişkisi oluşturma formunu gösterir ve post edilen veriyi işler.
     */
    public function create() {
        $pageTitle = "Yeni Veli - Öğrenci İlişkisi Ekle";
        $csrf_token = $this->csrf_token;
        
        // Kullanıcıları rollerine göre al
        $veliler = $this->db->query("SELECT id, name FROM users WHERE role = 'parent' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $ogrenciler = $this->db->query("SELECT id, name FROM users WHERE role = 'student' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Kontrolü
            if (!isset($_POST['csrf_token']) || !hash_equals($this->csrf_token, $_POST['csrf_token'])) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Güvenlik hatası: Form gönderimi geçersiz.'];
                header("Location: /?module=parents_students&action=create");
                exit;
            }

            $parent_id = filter_input(INPUT_POST, 'parent_id', FILTER_VALIDATE_INT);
            $student_ids = $_POST['student_ids'] ?? [];

            if (!$parent_id || empty($student_ids)) {
                $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Lütfen bir veli ve en az bir öğrenci seçin.'];
                header("Location: /?module=parents_students&action=create");
                exit;
            }
            
            $addedCount = 0;
            $skippedCount = 0;

            foreach ($student_ids as $student_id) {
                $student_id = filter_var($student_id, FILTER_VALIDATE_INT);
                if (!$student_id) continue;

                // Bu ilişki veritabanında zaten var mı diye kontrol et
                $stmt_check = $this->db->prepare("SELECT COUNT(*) FROM parents_students WHERE parent_id = ? AND student_id = ?");
                $stmt_check->execute([$parent_id, $student_id]);
                if ($stmt_check->fetchColumn() > 0) {
                    $skippedCount++;
                    continue; // Varsa atla, sonraki öğrenciye geç
                }

                // Yoksa, yeni ilişkiyi ekle
                $stmt = $this->db->prepare("INSERT INTO parents_students (parent_id, student_id) VALUES (?, ?)");
                if ($stmt->execute([$parent_id, $student_id])) {
                    $addedCount++;
                }
            }
            
            $message = "{$addedCount} yeni ilişki başarıyla eklendi.";
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} ilişki zaten mevcut olduğu için atlandı.";
            }

            $_SESSION['flash_message'] = ['type' => 'success', 'message' => $message];
            header("Location: /?module=parents_students");
            exit;
        }

        // Formu göster
        ob_start();
        include __DIR__ . '/create.php';
        $pageContent = ob_get_clean();
        require_once __DIR__ . '/../../themes/default/layout.php';
    }

    /**
     * Veli-Öğrenci ilişkisini siler.
     */
    public function delete() {
        // CSRF Token Kontrolü (hem GET hem POST için)
        $submitted_token = $_REQUEST['csrf_token'] ?? '';
        if (!hash_equals($this->csrf_token, $submitted_token)) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Güvenlik hatası: Silme işlemi geçersiz.'];
            header("Location: /?module=parents_students");
            exit;
        }

        // Tekli silme (GET metodu ile linkten)
        if (isset($_GET['pid']) && isset($_GET['sid'])) {
            $parent_id = filter_input(INPUT_GET, 'pid', FILTER_VALIDATE_INT);
            $student_id = filter_input(INPUT_GET, 'sid', FILTER_VALIDATE_INT);

            if ($parent_id && $student_id) {
                $stmt = $this->db->prepare("DELETE FROM parents_students WHERE parent_id = ? AND student_id = ?");
                if ($stmt->execute([$parent_id, $student_id])) {
                     $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'İlişki başarıyla silindi.'];
                } else {
                     $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Hata: İlişki silinemedi.'];
                }
            }
        }

        // Çoklu silme (POST metodu ile formdan)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ids'])) {
            $deletedCount = 0;
            foreach ($_POST['delete_ids'] as $val) {
                // 'parent_id-student_id' formatını parçala
                list($pid, $sid) = explode('-', $val);
                $parent_id = filter_var($pid, FILTER_VALIDATE_INT);
                $student_id = filter_var($sid, FILTER_VALIDATE_INT);

                if ($parent_id && $student_id) {
                    $stmt = $this->db->prepare("DELETE FROM parents_students WHERE parent_id = ? AND student_id = ?");
                    if ($stmt->execute([$parent_id, $student_id])) {
                        $deletedCount++;
                    }
                }
            }
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => "{$deletedCount} adet ilişki başarıyla silindi."];
        }

        header("Location: /?module=parents_students");
        exit;
    }
}

// === ROUTER ===
// Gelen 'action' parametresine göre ilgili metodu çalıştır
$action = $_GET['action'] ?? 'index';
$controller = new Parents_StudentsController();

if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    // Geçersiz bir action istenirse hata ver
    http_response_code(404);
    echo "<h1>404 - Sayfa Bulunamadı</h1>";
    echo "<p>İstenen işlem '{$action}' geçersiz.</p>";
    exit;
}