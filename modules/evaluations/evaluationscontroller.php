<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class EvaluationsController 
{
    protected $db;
    protected $userId;
    protected $userRole;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->userId = $_SESSION['user']['id'] ?? null;
        $this->userRole = $_SESSION['user']['role'] ?? null;
        if (!$this->userId) {
            redirect('index.php?module=login&action=index');
            exit;
        }
    }

    private function getEvaluationTypes()
    {
        return ['Yazılı Sınav', 'Sözlü', 'Ödev', 'Etkinlik', 'Davranış', 'Proje', 'Diğer'];
    }

    public function index() 
    {
        $evaluations = $this->db->select("SELECT * FROM evaluations ORDER BY exam_date DESC");
        return ['pageTitle' => 'Tüm Değerlendirmeler', 'evaluations' => $evaluations];
    }

    public function create() 
    {
        $all_students = $this->db->select("SELECT id, name FROM users WHERE role = 'student' ORDER BY name ASC");
        $all_teachers = $this->db->select("SELECT id, name FROM users WHERE role = 'teacher' ORDER BY name ASC");
        $courses = $this->db->select("SELECT id, name FROM courses ORDER BY name ASC");
        $classes = $this->db->select("SELECT id, name FROM classes ORDER BY name ASC");
        
        return [
            'pageTitle' => 'Yeni Değerlendirme Oluştur',
            'courses' => $courses,
            'classes' => $classes,
            'all_students' => $all_students,
            'all_teachers' => $all_teachers,
            'evaluation_types' => $this->getEvaluationTypes()
        ];
    }

    public function store()
    {
        $pdo = $this->db->getConnection();
        try {
            $pdo->beginTransaction();
            $sql = "INSERT INTO evaluations (name, description, evaluation_type, exam_date, course_id, class_id, creator_id, max_score, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['name'], $_POST['description'], $_POST['evaluation_type'], $_POST['exam_date'] ?: null, $_POST['course_id'] ?: null, $_POST['class_id'] ?: null, $this->userId, $_POST['max_score'] ?: 100.00, $_POST['status'] ?? 'draft']);
            $evaluation_id = $pdo->lastInsertId();

            if (!empty($_POST['students'])) {
                $student_sql = "INSERT INTO evaluation_students (evaluation_id, student_id) VALUES (?, ?)";
                $student_stmt = $pdo->prepare($student_sql);
                foreach ($_POST['students'] as $student_id) {
                    $student_stmt->execute([$evaluation_id, $student_id]);
                }
            }

            if (!empty($_POST['teachers'])) {
                $teacher_sql = "INSERT INTO evaluation_teachers (evaluation_id, teacher_id, role) VALUES (?, ?, ?)";
                $teacher_stmt = $pdo->prepare($teacher_sql);
                foreach ($_POST['teachers'] as $teacher_assignment) {
                    if (!empty($teacher_assignment['id']) && !empty($teacher_assignment['role'])) {
                        $teacher_stmt->execute([$evaluation_id, $teacher_assignment['id'], $teacher_assignment['role']]);
                    }
                }
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Hata: " . $e->getMessage());
        }
        redirect('index.php?module=evaluations&action=index&status=success');
        exit;
    }

    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) redirect('index.php?module=evaluations&action=index');

        $evaluation = $this->db->select("SELECT * FROM evaluations WHERE id = ?", [$id])[0] ?? null;
        if (!$evaluation) die("Değerlendirme bulunamadı!");
        
        $all_students = $this->db->select("SELECT id, name FROM users WHERE role = 'student' ORDER BY name ASC");
        $all_teachers = $this->db->select("SELECT id, name FROM users WHERE role = 'teacher' ORDER BY name ASC");
        $courses = $this->db->select("SELECT id, name FROM courses ORDER BY name ASC");
        $classes = $this->db->select("SELECT id, name FROM classes ORDER BY name ASC");
        
        $assigned_students_raw = $this->db->select("SELECT student_id FROM evaluation_students WHERE evaluation_id = ?", [$id]);
        $assigned_students = array_column($assigned_students_raw, 'student_id');

        $assigned_teachers = $this->db->select("SELECT teacher_id, role FROM evaluation_teachers WHERE evaluation_id = ?", [$id]);

        return [
            'pageTitle' => 'Değerlendirmeyi Düzenle',
            'evaluation' => $evaluation,
            'courses' => $courses,
            'classes' => $classes,
            'all_students' => $all_students,
            'all_teachers' => $all_teachers,
            'assigned_students' => $assigned_students,
            'assigned_teachers' => $assigned_teachers,
            'evaluation_types' => $this->getEvaluationTypes()
        ];
    }

    public function update()
    {
        $evaluation_id = $_POST['id'] ?? null;
        if (!$evaluation_id) redirect('index.php?module=evaluations&action=index');
        
        $pdo = $this->db->getConnection();
        try {
            $pdo->beginTransaction();
            $sql = "UPDATE evaluations SET name = ?, description = ?, evaluation_type = ?, exam_date = ?, course_id = ?, class_id = ?, max_score = ?, status = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['name'], $_POST['description'], $_POST['evaluation_type'], $_POST['exam_date'] ?: null, $_POST['course_id'] ?: null, $_POST['class_id'] ?: null, $_POST['max_score'] ?: 100.00, $_POST['status'] ?? 'draft', $evaluation_id]);

            $pdo->prepare("DELETE FROM evaluation_students WHERE evaluation_id = ?")->execute([$evaluation_id]);
            $pdo->prepare("DELETE FROM evaluation_teachers WHERE evaluation_id = ?")->execute([$evaluation_id]);

            if (!empty($_POST['students'])) {
                $student_sql = "INSERT INTO evaluation_students (evaluation_id, student_id) VALUES (?, ?)";
                $student_stmt = $pdo->prepare($student_sql);
                foreach ($_POST['students'] as $student_id) {
                    $student_stmt->execute([$evaluation_id, $student_id]);
                }
            }
            if (!empty($_POST['teachers'])) {
                $teacher_sql = "INSERT INTO evaluation_teachers (evaluation_id, teacher_id, role) VALUES (?, ?, ?)";
                $teacher_stmt = $pdo->prepare($teacher_sql);
                foreach ($_POST['teachers'] as $teacher_assignment) {
                    if (!empty($teacher_assignment['id']) && !empty($teacher_assignment['role'])) {
                        $teacher_stmt->execute([$evaluation_id, $teacher_assignment['id'], $teacher_assignment['role']]);
                    }
                }
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Hata: " . $e->getMessage());
        }
        redirect("index.php?module=evaluations&action=edit&id={$evaluation_id}&status=updated");
        exit;
    }
    
    // =============================================================
    // GÜNCELLENEN METOT
    // =============================================================
    public function results()
    {
        $evaluation_id = $_GET['id'] ?? null;
        if (!$evaluation_id) redirect('index.php?module=evaluations&action=index');

        $evaluation = $this->db->select("SELECT * FROM evaluations WHERE id = ?", [$evaluation_id])[0] ?? null;
        if (!$evaluation) die("Değerlendirme bulunamadı!");

        $students = [];

        // 1. ÖNCELİK: Bireysel olarak atanmış öğrenci var mı diye kontrol et.
        $assigned_students = $this->db->select(
            "SELECT u.id, u.name FROM users u JOIN evaluation_students es ON u.id = es.student_id WHERE es.evaluation_id = ? ORDER BY u.name ASC",
            [$evaluation_id]
        );

        if (!empty($assigned_students)) {
            $students = $assigned_students;
        } 
        // 2. ÖNCELİK: Eğer bireysel atama yoksa, atanmış bir sınıf var mı diye kontrol et.
        elseif (!empty($evaluation['class_id'])) {
            $students = $this->db->select("SELECT id, name FROM users WHERE class_id = ? AND role = 'student' ORDER BY name ASC", [$evaluation['class_id']]);
        }
        
        // Mevcut girilmiş sonuçları çek
        $existing_results_raw = $this->db->select("SELECT * FROM evaluation_results WHERE evaluation_id = ?", [$evaluation_id]);
        $existing_results = array_column($existing_results_raw, null, 'student_id');

        return [
            'pageTitle' => ($evaluation['name'] ?? '') . ' Sonuç Girişi',
            'evaluation' => $evaluation,
            'students' => $students,
            'existing_results' => $existing_results
        ];
    }

    public function store_results()
    {
        $evaluation_id = $_POST['evaluation_id'] ?? null;
        if (!$evaluation_id) die("Değerlendirme ID eksik!");
        
        $results = $_POST['results'] ?? [];

        foreach($results as $student_id => $data){
            $score = $data['score'] !== '' ? $data['score'] : null;
            $comments = $data['comments'] ?? '';

            $existing_result = $this->db->select("SELECT id FROM evaluation_results WHERE evaluation_id = ? AND student_id = ?", [$evaluation_id, $student_id]);

            if(!empty($existing_result)){
                $result_id = $existing_result[0]['id'];
                $this->db->getConnection()->prepare("UPDATE evaluation_results SET score = ?, comments = ? WHERE id = ?")->execute([$score, $comments, $result_id]);
            } else {
                if($score !== null || !empty($comments)){
                    $this->db->getConnection()->prepare("INSERT INTO evaluation_results (evaluation_id, student_id, score, comments, entry_by_user_id) VALUES (?, ?, ?, ?, ?)")->execute([$evaluation_id, $student_id, $score, $comments, $this->userId]);
                }
            }
        }
        
        redirect("index.php?module=evaluations&action=results&id={$evaluation_id}&status=saved");
        exit;
    }
}