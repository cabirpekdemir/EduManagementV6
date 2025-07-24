<?php
require_once __DIR__ . '/../../core/database.php';

class GradesController
{
    protected $db;
    protected $currentUserRole;
    protected $currentUserId;

    public function __construct()
    {
        $this->db = Database::getInstance();
        // Oturumdan kullanıcı bilgilerini al (Bu kısmı kendi sisteminize göre uyarlayın)
        $this->currentUserRole = $_SESSION['user_role'] ?? 'guest';
        $this->currentUserId = $_SESSION['user_id'] ?? 0;
    }

    // Not listesi
    public function index()
    {
        $sql = "SELECT g.*, s.name as student_name, c.name as course_name 
                FROM grades g
                JOIN users s ON g.student_id = s.id
                JOIN courses c ON g.course_id = c.id";

        $params = [];

        if ($this->currentUserRole === 'teacher') {
            $sql .= " WHERE g.teacher_id = ?";
            $params[] = $this->currentUserId;
        }

        $sql .= " ORDER BY g.grade_date DESC, s.name ASC";
        
        $grades = $this->db->select($sql, $params);
        return ['grades' => $grades, 'userRole' => $this->currentUserRole];
    }

    // Not ekleme formu
    public function create()
    {
        if ($this->currentUserRole === 'guest') die("Yetkiniz yok!");

        $students = $this->db->select("SELECT id, name FROM users WHERE role='student' ORDER BY name");
        $courses = $this->db->select("SELECT id, name FROM courses ORDER BY name");
        
        $grade = ['student_id'=>'', 'course_id'=>'', 'grade'=>'', 'grade_date'=>date('Y-m-d'), 'comments'=>''];

        return [
            'grade' => $grade, 
            'students' => $students,
            'courses' => $courses,
            'isEdit' => false, 
            'formAction' => "index.php?module=grades&action=store"
        ];
    }

    // Yeni notu kaydet
    public function store()
    {
        if ($this->currentUserRole === 'guest') die("Yetkiniz yok!");

        $student_id = $_POST['student_id'];
        $course_id = $_POST['course_id'];
        $grade = $_POST['grade'];
        $grade_date = $_POST['grade_date'];
        $comments = $_POST['comments'];
        $teacher_id = $this->currentUserId; // Notu ekleyen öğretmen

        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO grades (student_id, course_id, teacher_id, grade, grade_date, comments) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$student_id, $course_id, $teacher_id, $grade, $grade_date, $comments]);

        header('Location: index.php?module=grades&action=index');
        exit;
    }

    // Not düzenleme formu
    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $grade = $this->db->select("SELECT * FROM grades WHERE id=?", [$id])[0] ?? null;

        if (!$grade) die("Not bulunamadı!");

        // Yetki kontrolü: Admin değilse ve notu kendi eklememişse düzenleyemez
        if ($this->currentUserRole !== 'admin' && $grade['teacher_id'] != $this->currentUserId) {
            die("Bu notu düzenleme yetkiniz yok!");
        }

        $students = $this->db->select("SELECT id, name FROM users WHERE role='student' ORDER BY name");
        $courses = $this->db->select("SELECT id, name FROM courses ORDER BY name");
        
        return [
            'grade' => $grade, 
            'students' => $students,
            'courses' => $courses,
            'isEdit' => true, 
            'formAction' => "index.php?module=grades&action=update&id=$id"
        ];
    }

    // Notu güncelle
    public function update()
    {
        $id = $_GET['id'];
        $gradeData = $this->db->select("SELECT teacher_id FROM grades WHERE id=?", [$id])[0] ?? null;

        if (!$gradeData) die("Not bulunamadı!");

        if ($this->currentUserRole !== 'admin' && $gradeData['teacher_id'] != $this->currentUserId) {
            die("Bu notu güncelleme yetkiniz yok!");
        }

        $student_id = $_POST['student_id'];
        $course_id = $_POST['course_id'];
        $grade = $_POST['grade'];
        $grade_date = $_POST['grade_date'];
        $comments = $_POST['comments'];

        $this->db->getConnection()->prepare(
            "UPDATE grades SET student_id=?, course_id=?, grade=?, grade_date=?, comments=? WHERE id=?"
        )->execute([$student_id, $course_id, $grade, $grade_date, $comments, $id]);

        header('Location: index.php?module=grades&action=index');
        exit;
    }

    // Notu sil
    public function delete()
    {
        $id = $_GET['id'] ?? 0;
        $gradeData = $this->db->select("SELECT teacher_id FROM grades WHERE id=?", [$id])[0] ?? null;

        if (!$gradeData) die("Not bulunamadı!");

        if ($this->currentUserRole !== 'admin' && $gradeData['teacher_id'] != $this->currentUserId) {
            die("Bu notu silme yetkiniz yok!");
        }

        $this->db->getConnection()->prepare("DELETE FROM grades WHERE id=?")->execute([$id]);
        header('Location: index.php?module=grades&action=index');
        exit;
    }
}