<?php
require_once __DIR__ . '/../../core/database.php';

class MessagesController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index()
    {
        $user_id = $_SESSION['user']['id'];
        $pdo = $this->db->getConnection();

        // Gelen mesajlar
        $incoming = $pdo->prepare("
            SELECT m.*, u.name AS sender_name
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            JOIN messages_targets mt ON m.id = mt.message_id
            WHERE mt.receiver_id = ?
            ORDER BY m.created_at DESC
        ");
        $incoming->execute([$user_id]);
        $incomingMessages = $incoming->fetchAll(PDO::FETCH_ASSOC);

        // Gönderilen mesajlar
        $sent = $pdo->prepare("
            SELECT m.*,
                (SELECT COUNT(*) FROM messages_targets mt WHERE mt.message_id = m.id) AS receiver_count
            FROM messages m
            WHERE m.sender_id = ?
            ORDER BY m.created_at DESC
        ");
        $sent->execute([$user_id]);
        $sentMessages = $sent->fetchAll(PDO::FETCH_ASSOC);

        return [
            'incomingMessages' => $incomingMessages,
            'sentMessages' => $sentMessages
        ];
    }

    public function create()
    {
        $pdo = $this->db->getConnection();
        $user = $_SESSION['user'];
        $role = $user['role'];

        if ($role === 'admin') {
            $users = $pdo->query("SELECT id, name, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
            $courses = $pdo->query("SELECT id, name FROM courses")->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($role === 'teacher') {
            $users = $pdo->prepare("
                SELECT u.id, u.name, u.role
                FROM users u
                JOIN teachers_students ts ON u.id = ts.student_id
                WHERE ts.teacher_id = ?
            ");
            $users->execute([$user['id']]);
            $users = $users->fetchAll(PDO::FETCH_ASSOC);

            $courses = $pdo->prepare("SELECT id, name FROM courses WHERE teacher_id = ?");
            $courses->execute([$user['id']]);
            $courses = $courses->fetchAll(PDO::FETCH_ASSOC);
        } else {
            die("Yetkisiz erişim");
        }

        return ['users' => $users, 'courses' => $courses];
    }

    public function store()
    {
        $sender_id = $_SESSION['user']['id'];
        $course_id = $_POST['course_id'] ?? null;
        $sinif     = $_POST['sinif'] ?? null;
        $subject   = $_POST['subject'] ?? '';
        $content   = $_POST['content'] ?? '';
        $receiver_ids = $_POST['receiver_ids'] ?? [];

        if (empty($receiver_ids)) {
            die("En az bir alıcı seçmelisiniz.");
        }

        $pdo = $this->db->getConnection();

        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, course_id, sinif, subject, content) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$sender_id, $course_id, $sinif, $subject, $content]);

        $message_id = $pdo->lastInsertId();

        foreach ($receiver_ids as $rid) {
            $stmt2 = $pdo->prepare("INSERT INTO messages_targets (message_id, receiver_id) VALUES (?, ?)");
            $stmt2->execute([$message_id, $rid]);
        }

        header("Location: index.php?module=messages");
        exit;
    }

    public function view()
    {
        $id = $_GET['id'];
        $pdo = $this->db->getConnection();

        $stmt = $pdo->prepare("
            SELECT m.*, u.name AS sender_name 
            FROM messages m 
            JOIN users u ON m.sender_id = u.id 
            WHERE m.id = ?
        ");
        $stmt->execute([$id]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);

        $recipients = $pdo->prepare("
            SELECT u.name, u.role 
            FROM messages_targets mt
            JOIN users u ON mt.receiver_id = u.id
            WHERE mt.message_id = ?
        ");
        $recipients->execute([$id]);
        $recipientList = $recipients->fetchAll(PDO::FETCH_ASSOC);

        return [
            'message' => $message,
            'recipients' => $recipientList
        ];
    }
}
