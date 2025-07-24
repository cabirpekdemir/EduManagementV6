<?php
class AnnouncementsController {
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
    }

    private function checkAdminAccess()
    {
        if ($this->userRole !== 'admin') {
            log_activity('ACCESS_DENIED', 'Announcements', null, 'Yetkisiz duyuru yönetim işlemi denemesi.');
            redirect('index.php?module=dashboard&error_message=Bu işlem için yetkiniz yok.');
            exit;
        }
    }
    
    private function checkCreateAccess()
    {
        if (!in_array($this->userRole, ['admin', 'teacher'])) {
            log_activity('ACCESS_DENIED', 'Announcements', null, 'Yetkisiz duyuru oluşturma denemesi.');
            redirect('index.php?module=dashboard&error_message=Duyuru oluşturma yetkiniz yok.');
            exit;
        }
    }

    public function index() {
        if ($this->userRole === 'guest') { redirect('index.php?module=login'); exit; }
        
        $sql = "SELECT a.*, u.name AS creator_name FROM announcements a JOIN users u ON a.created_by = u.id";
        $params = [];
        
        if ($this->userRole === 'admin') {
            // Admin her şeyi görür.
        } elseif ($this->userRole === 'teacher') {
            $sql .= " WHERE (a.status = 'approved' AND (a.target_role = ? OR a.target_role = 'all')) OR (a.created_by = ?)";
            $params = [$this->userRole, $this->userId];
        } else {
            $sql .= " WHERE a.status = 'approved' AND (a.target_role = ? OR a.target_role = 'all')";
            $params = [$this->userRole];
        }
        $sql .= " ORDER BY a.created_at DESC";

        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { $announcements = []; }

        return [
            'pageTitle' => 'Duyurular',
            'announcements' => $announcements,
            'userRole' => $this->userRole
        ];
    }
    
    public function view() {
        // ... (Bu fonksiyon önceki cevaptaki gibi kalabilir, doğru çalışıyor) ...
        if ($this->userRole === 'guest') { redirect('index.php?module=login'); exit; }
        $id = $_GET['id'] ?? 0;
        if (!$id) { redirect('index.php?module=announcements&action=index&error_message=Duyuru IDsi eksik.'); exit; }

        try {
            $stmt = $this->db->getConnection()->prepare("SELECT a.*, u.name AS creator_name FROM announcements a JOIN users u ON a.created_by = u.id WHERE a.id = ?");
            $stmt->execute([$id]);
            $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

            if (empty($announcement)) { redirect('index.php?module=announcements&action=index&error_message=Duyuru bulunamadı.'); exit; }

            $can_view = false;
            if ($this->userRole === 'admin' || ($this->userRole === 'teacher' && $announcement['created_by'] == $this->userId)) { $can_view = true; } 
            elseif ($announcement['status'] === 'approved' && ($announcement['target_role'] === 'all' || $announcement['target_role'] === $this->userRole)) { $can_view = true; }

            if (!$can_view) { redirect('index.php?module=announcements&action=index&error_message=Bu duyuruyu görüntüleme yetkiniz yok.'); exit; }
        } catch (PDOException $e) { redirect('index.php?module=announcements&action=index&error_message=Duyuru yüklenirken bir hata oluştu.'); exit; }
        
        return [ 'pageTitle' => $announcement['title'], 'announcement' => $announcement ];
    }

    public function create() {
        $this->checkCreateAccess();
        return [ 'pageTitle' => 'Yeni Duyuru Oluştur' ];
    }

    public function store() {
        $this->checkCreateAccess();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('index.php?module=announcements&action=index'); exit; }

        $title = $_POST['title'] ?? ''; 
        $content = $_POST['content'] ?? '';
        $target_role = $_POST['audience'] ?? 'all'; 
        $created_by = $this->userId;
        $status = ($this->userRole === 'admin') ? 'approved' : 'pending';

        if (empty($title) || empty($content)) { redirect('index.php?module=announcements&action=create&error_message=Lütfen tüm alanları doldurun.'); exit; }

        try {
            $stmt = $this->db->getConnection()->prepare("INSERT INTO announcements (title, content, status, target_role, created_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $content, $status, $target_role, $created_by]);
            $success_message = ($status === 'pending') ? 'Duyurunuz başarıyla oluşturuldu ve yönetici onayına gönderildi.' : 'Duyuru başarıyla eklendi.';
            redirect('index.php?module=announcements&action=index&status_message=' . urlencode($success_message));
        } catch (PDOException $e) { redirect('index.php?module=announcements&action=create&error_message=Duyuru eklenirken bir hata oluştu.'); }
        exit;
    }

    public function approve() {
        $this->checkAdminAccess();
        $id = $_GET['id'] ?? 0;
        if($id) {
            $this->db->getConnection()->prepare("UPDATE announcements SET status = 'approved' WHERE id = ?")->execute([$id]);
        }
        redirect('index.php?module=announcements&action=index&status_message=Duyuru onaylandı.');
    }

    public function reject() {
        $this->checkAdminAccess();
        $id = $_GET['id'] ?? 0;
         if($id) {
            $this->db->getConnection()->prepare("UPDATE announcements SET status = 'rejected' WHERE id = ?")->execute([$id]);
        }
        redirect('index.php?module=announcements&action=index&status_message=Duyuru reddedildi.');
    }
}
