<?php
require_once __DIR__ . '/../../core/database.php';

class AssignmentsController
{
    protected $db;
    protected $user;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->user = $_SESSION['user'] ?? null;
    }

    public function index()
    {
        $role = $this->user['role'];
        $userId = $this->user['id'];

        if ($role === 'admin') {
            $assignments = $this->db->select("SELECT * FROM assignments ORDER BY created_at DESC");
        } elseif ($role === 'teacher') {
            // Sadece öğretmenin derslerinden gelen ödevler
            $assignments = $this->db->select("
                SELECT a.* FROM assignments a
                JOIN courses c ON a.course_id = c.id
                WHERE c.teacher_id = ?
                ORDER BY a.created_at DESC
            ", [$userId]);
        } else {
            // Öğrenci veya veli ise, sadece ilgili öğrenciye ait ödevleri getir
            $assignments = $this->db->select("
                SELECT a.* FROM assignments a
                JOIN assignment_students s ON a.id = s.assignment_id
                WHERE s.student_id = ?
                ORDER BY a.created_at DESC
            ", [$userId]);
        }

        return ['assignments' => $assignments];
    }

    public function create()
    {
        $courses = [];
        $students = [];

        if ($this->user['role'] === 'admin') {
            $courses = $this->db->select("SELECT * FROM courses");
            $students = $this->db->select("SELECT * FROM users WHERE role = 'student'");
        } elseif ($this->user['role'] === 'teacher') {
            $courses = $this->db->select("SELECT * FROM courses WHERE teacher_id = ?", [$this->user['id']]);
            $students = $this->db->select("
                SELECT u.* FROM users u
                JOIN teachers_students ts ON u.id = ts.student_id
                WHERE ts.teacher_id = ?
            ", [$this->user['id']]);
        }

        return ['courses' => $courses, 'students' => $students];
    }

    public function store()
    {
        $course_id = $_POST['course_id'];
        $title = $_POST['title'];
        $selected_students = $_POST['students'] ?? [];
        $filename = '';

        // Dosya yükleme işlemi
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['file']['tmp_name'];
            $filename = 'uploads/' . basename($_FILES['file']['name']);
            move_uploaded_file($tmp_name, $filename);
        }

        $this->db->insert("INSERT INTO assignments (course_id, title, filename, student_id) VALUES (?, ?, ?, 0)", [
            $course_id, $title, $filename
        ]);

        $assignment_id = $this->db->lastInsertId();

        // Seçilen öğrencilere ödevi ilişkilendir
        foreach ($selected_students as $student_id) {
            $this->db->insert("INSERT INTO assignment_students (assignment_id, student_id) VALUES (?, ?)", [
                $assignment_id, $student_id
            ]);
        }

        header("Location: index.php?module=assignments");
        exit;
    }

    public function edit()
    {
        $id = $_GET['id'];
        $assignment = $this->db->select("SELECT * FROM assignments WHERE id = ?", [$id])[0] ?? null;
        $students = $this->db->select("
            SELECT u.* FROM users u
            JOIN assignment_students s ON u.id = s.student_id
            WHERE s.assignment_id = ?
        ", [$id]);

        return ['assignment' => $assignment, 'students' => $students];
    }

    public function update()
    {
        $id = $_POST['id'];
        $grade = $_POST['grade'] ?? null;
        $note = $_POST['teacher_note'] ?? null;

        $this->db->update("UPDATE assignments SET grade = ?, teacher_note = ? WHERE id = ?", [
            $grade, $note, $id
        ]);

        header("Location: index.php?module=assignments");
        exit;
    }

    public function delete()
    {
        $id = $_GET['id'];
        $this->db->delete("DELETE FROM assignments WHERE id = ?", [$id]);
        $this->db->delete("DELETE FROM assignment_students WHERE assignment_id = ?", [$id]);

        header("Location: index.php?module=assignments");
        exit;
    }
}