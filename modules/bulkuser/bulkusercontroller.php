<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class BulkUserController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
        if ($_SESSION['user']['role'] !== 'admin') {
            die("Bu alana erişim yetkiniz yok.");
        }
    }

    public function index() {
        return [
            'pageTitle' => 'Toplu Kullanıcı Ekleme',
            'success_count' => $_GET['success_count'] ?? 0,
            'skipped_count' => $_GET['skipped_count'] ?? 0,
            'error_message' => $_GET['error_message'] ?? null
        ];
    }

    public function paste_upload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['user_data'])) {
            redirect('index.php?module=bulkuser&action=index&error_message=no_data');
            exit;
        }

        $pasted_data = trim($_POST['user_data']);
        $lines = explode("\n", $pasted_data);

        $success_count = 0;
        $skipped_count = 0;
        $pdo = $this->db->getConnection();

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            $row = explode("\t", trim($line));

            $name = $row[0] ?? null;
            $email = $row[1] ?? null;
            $password = $row[2] ?? null;
            $role = $row[3] ?? null;
            $class_id = !empty($row[4]) ? intval($row[4]) : null;
            $tc_kimlik = $row[5] ?? null;

            if (empty($name) || empty($email) || empty($password) || empty($tc_kimlik) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped_count++;
                continue;
            }

            if ($role === 'student' && $class_id !== null) {
                $class_stmt = $pdo->prepare("SELECT id FROM classes WHERE id = ?");
                $class_stmt->execute([$class_id]);
                if (!$class_stmt->fetch()) {
                    $skipped_count++;
                    continue;
                }
            }

            $email_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $email_stmt->execute([$email]);
            if ($email_stmt->fetch()) {
                $skipped_count++;
                continue;
            }

            // --- YENİ KONTROL: TC Kimlik Numarası mükerrer mi? ---
            $tc_stmt = $pdo->prepare("SELECT id FROM users WHERE tc_kimlik = ?");
            $tc_stmt->execute([$tc_kimlik]);
            if ($tc_stmt->fetch()) {
                // Sağlanan tc_kimlik veritabanında zaten var.
                $skipped_count++;
                continue; // Bu kullanıcıyı atla ve bir sonrakine geç.
            }
            // --- YENİ KONTROL SONU ---


            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $insert_stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, class_id, tc_kimlik) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->execute([$name, $email, $hashed_password, $role, $class_id, $tc_kimlik]);
            $success_count++;
        }
        
        redirect("index.php?module=bulkuser&action=index&success_count={$success_count}&skipped_count={$skipped_count}");
        exit;
    }
}