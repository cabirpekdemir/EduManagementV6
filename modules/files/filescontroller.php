<?php
require_once __DIR__ . '/../../core/database.php';

class FilesController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index()
    {
        $pdo = $this->db->getConnection();
        $user_id = $_SESSION['user']['id'];
        $role = $_SESSION['user']['role'];

        if ($role === 'admin') {
            // Admin her dosyayı görür
            $stmt = $pdo->query("SELECT * FROM files ORDER BY uploaded_at DESC");
            $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Diğerleri sadece kendilerine paylaşılan dosyaları görür
            $stmt = $pdo->query("SELECT * FROM files ORDER BY uploaded_at DESC");
            $allFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $files = [];

            foreach ($allFiles as $file) {
                $shared_with = explode(',', $file['shared_with'] ?? '');
                if (in_array($user_id, $shared_with)) {
                    $files[] = $file;
                }
            }
        }

        return ['files' => $files];
    }

    public function create()
    {
        $pdo = $this->db->getConnection();
        $user = $_SESSION['user'];
        $user_id = $user['id'];
        $role = $user['role'];

        $users = [];

        if ($role === 'admin') {
            $users = $pdo->query("SELECT id, name FROM users")->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($role === 'teacher') {
            $stmt = $pdo->prepare("
                SELECT u.id, u.name FROM users u
                WHERE u.id IN (
                    SELECT student_id FROM teachers_students WHERE teacher_id = ?
                    UNION
                    SELECT parent_id FROM parents_students
                    WHERE student_id IN (SELECT student_id FROM teachers_students WHERE teacher_id = ?)
                )
            ");
            $stmt->execute([$user_id, $user_id]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($role === 'student') {
            $stmt = $pdo->prepare("
                SELECT DISTINCT u.id, u.name FROM users u
                WHERE u.id IN (
                    SELECT teacher_id FROM teachers_students WHERE student_id = ?
                )
            ");
            $stmt->execute([$user_id]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($role === 'parent') {
            $stmt = $pdo->prepare("
                SELECT DISTINCT u.id, u.name FROM users u
                WHERE u.id IN (
                    SELECT teacher_id FROM teachers_students
                    WHERE student_id IN (
                        SELECT student_id FROM parents_students WHERE parent_id = ?
                    )
                )
            ");
            $stmt->execute([$user_id]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return ['users' => $users];
    }

    public function store()
    {
        $pdo = $this->db->getConnection();
        $filename = '';
        $description = $_POST['description'] ?? '';
        $shared_with_ids = $_POST['shared_with'] ?? [];

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['file']['tmp_name'];
            $filename = 'uploads/' . basename($_FILES['file']['name']);
            move_uploaded_file($tmp, $filename);
        }

        $shared_with = implode(',', $shared_with_ids);

        $stmt = $pdo->prepare("INSERT INTO files (filename, description, shared_with) VALUES (?, ?, ?)");
        $stmt->execute([$filename, $description, $shared_with]);

        header("Location: index.php?module=files");
        exit;
    }
}
