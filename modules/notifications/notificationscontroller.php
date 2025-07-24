<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class NotificationsController
{
    protected $db;
    protected $currentUser;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->currentUser = $_SESSION['user'] ?? null;
    }

    // Admin için bildirim listesi ve oluşturma formu
    public function index()
    {
        if (($this->currentUser['role'] ?? '') !== 'admin') {
            die("Yetkiniz yok.");
        }
        $notifications = $this->db->select("SELECT * FROM notifications ORDER BY id DESC");
        $users = $this->db->select("SELECT id, name, role FROM users ORDER BY name ASC");
        return ['notifications' => $notifications, 'users' => $users];
    }

    // Yeni bildirimi kaydet
    public function store()
    {
        if (($this->currentUser['role'] ?? '') !== 'admin') die("Yetkiniz yok.");

        $creator_id = $this->currentUser['id'];
        $title = $_POST['title'];
        $message = $_POST['message'];
        $url = $_POST['url'];
        $target = $_POST['target'];
        
        $target_role = null;
        $target_user_id = null;

        if ($target === 'all') {
            // Hiçbir şey yapma, ikisi de null kalacak
        } elseif ($target === 'students' || $target === 'teachers' || $target === 'parents') {
            $target_role = $target;
        } else {
            $target_user_id = (int)$target;
        }

        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO notifications (creator_id, title, message, url, target_role, target_user_id) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$creator_id, $title, $message, $url, $target_role, $target_user_id]);
        
        log_activity('CREATE', 'Notifications', $this->db->getConnection()->lastInsertId(), "Bildirim oluşturdu: '$title'");
        
        redirect('index.php?module=notifications&action=index');
    }

    // AJAX için okunmamış bildirimleri kontrol et
    public function check()
    {
        header('Content-Type: application/json');
        if (!$this->currentUser) {
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
            exit;
        }

        $userId = $this->currentUser['id'];
        $userRole = $this->currentUser['role'];

        $sql = "SELECT n.* FROM notifications n
                LEFT JOIN notification_read_status rs ON n.id = rs.notification_id AND rs.user_id = ?
                WHERE rs.id IS NULL AND (
                    (n.target_role IS NULL AND n.target_user_id IS NULL) OR 
                    n.target_role = ? OR 
                    n.target_user_id = ?
                )
                ORDER BY n.id DESC";
        
        $unread_notifications = $this->db->select($sql, [$userId, $userRole, $userId]);

        echo json_encode(['success' => true, 'notifications' => $unread_notifications]);
        exit;
    }

    // AJAX için bildirimi okundu olarak işaretle
    public function mark_as_read()
    {
        header('Content-Type: application/json');
        if (!$this->currentUser) {
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
            exit;
        }

        $notification_id = $_POST['notification_id'] ?? 0;
        if (!$notification_id) {
            echo json_encode(['success' => false, 'error' => 'Invalid notification ID']);
            exit;
        }

        $stmt = $this->db->getConnection()->prepare(
            "INSERT IGNORE INTO notification_read_status (notification_id, user_id) VALUES (?, ?)"
        );
        $stmt->execute([$notification_id, $this->currentUser['id']]);

        echo json_encode(['success' => true]);
        exit;
    }
}