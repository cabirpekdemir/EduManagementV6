<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php'; // Tutarlılık için eklendi

class NotesController {
    private $db;

    public function __construct() {
        // Not: Bu controller zaten doğru mimari deseniyle yazılmış.
        // Sadece sorgu mantığını düzeltiyoruz.
        $this->db = Database::getInstance()->getConnection();
    }

    public function index() {
        // DÜZELTME: 'students' tablosu yerine 'users' tablosu ile JOIN yapıldı.
        $stmt = $this->db->query("
            SELECT n.*, u.name AS student_name, c.name AS course_name
            FROM notes n
            JOIN users u ON u.id = n.student_id
            JOIN courses c ON c.id = n.course_id
            ORDER BY n.created_at DESC
        ");
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['notes' => $notes, 'pageTitle' => 'Tüm Notlar'];
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $this->db->prepare("INSERT INTO notes (student_id, course_id, content, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([
                $_POST['student_id'],
                $_POST['course_id'],
                $_POST['content']
            ]);
            header("Location: /?module=notes&action=index");
            exit;
        }

        // DÜZELTME: 'students' tablosu yerine 'users' tablosundan rolü 'student' olanlar çekildi.
        $students = $this->db->query("SELECT id, name FROM users WHERE role = 'student' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $courses = $this->db->query("SELECT id, name FROM courses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        return compact('students', 'courses');
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            // Hata durumunda View'a göndermek daha iyi bir pratik olabilir.
            return ['error' => 'Not ID eksik'];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $this->db->prepare("UPDATE notes SET student_id = ?, course_id = ?, content = ? WHERE id = ?");
            $stmt->execute([
                $_POST['student_id'],
                $_POST['course_id'],
                $_POST['content'],
                $id
            ]);
            header("Location: /?module=notes&action=index");
            exit;
        }

        $stmt = $this->db->prepare("SELECT * FROM notes WHERE id = ?");
        $stmt->execute([$id]);
        $note = $stmt->fetch(PDO::FETCH_ASSOC);

        // DÜZELTME: 'students' tablosu yerine 'users' tablosundan rolü 'student' olanlar çekildi.
        $students = $this->db->query("SELECT id, name FROM users WHERE role = 'student' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $courses = $this->db->query("SELECT id, name FROM courses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        return compact('note', 'students', 'courses');
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $this->db->prepare("DELETE FROM notes WHERE id = ?");
            $stmt->execute([$id]);
        }
        header("Location: /?module=notes&action=index");
        exit;
    }
}