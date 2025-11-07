<?php
// modules/teacher_assignments/Teacher_assignmentsController.php
require_once __DIR__ . '/../../core/database.php';

class Teacher_assignmentsController
{
    /** @var Database */
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /* ---------------- helpers ---------------- */
    private function tableHas(string $table, string $col): bool
    {
        try {
            $rows = $this->db->select("DESCRIBE `$table`") ?? [];
            foreach ($rows as $r) {
                if (isset($r['Field']) && strcasecmp($r['Field'], $col) === 0) return true;
            }
        } catch (\Throwable $e) {}
        return false;
    }

    private function flash(string $type, string $msg): void
    {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }

    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /* =======================================================================
       MERKEZİ STORE
       POST: teacher_id (int), class_ids[] (opt), course_ids[] (opt), group_ids[] (opt)
       Sadece "başlığı boş" pivot kayıtlarını temizler ve yeniden ekler
    ======================================================================= */
    public function store()
    {
        $teacherId = (int)($_POST['teacher_id'] ?? 0);
        $classIds  = is_array($_POST['class_ids']  ?? null) ? array_filter(array_map('intval', $_POST['class_ids']))  : [];
        $courseIds = is_array($_POST['course_ids'] ?? null) ? array_filter(array_map('intval', $_POST['course_ids'])) : [];
        $groupIds  = is_array($_POST['group_ids']  ?? null) ? array_filter(array_map('intval', $_POST['group_ids']))  : [];

        if ($teacherId <= 0) {
            $this->flash('danger', 'Geçersiz öğretmen.');
            $this->redirect('index.php?module=teachers&action=index');
        }

        $hasCreatedAt = $this->tableHas('teacher_assignments','created_at');
        $hasTitle     = $this->tableHas('teacher_assignments','title');
        $hasClass     = $this->tableHas('teacher_assignments','class_id');
        $hasCourse    = $this->tableHas('teacher_assignments','course_id');
        $hasGroup     = $this->tableHas('teacher_assignments','course_group_id');

        if (!$hasClass && !$hasCourse && !$hasGroup) {
            $this->flash('danger','teacher_assignments tablosunda class_id / course_id / course_group_id kolonları yok.');
            $this->redirect('index.php?module=teachers&action=show&id='.$teacherId);
        }

        $titleCond = $hasTitle ? " AND (title IS NULL OR title='')" : '';

        // ----- Sınıf atamaları -----
        if ($hasClass) {
            $this->db->select(
                "DELETE FROM `teacher_assignments`
                 WHERE `teacher_id`=? AND `class_id` IS NOT NULL
                   AND (`course_id` IS NULL OR `course_id`=0)
                   AND (`course_group_id` IS NULL OR `course_group_id`=0)
                   $titleCond", [$teacherId]
            );
            foreach ($classIds as $cid) {
                if ($cid <= 0) continue;
                if ($hasCreatedAt) {
                    $this->db->select(
                        "INSERT INTO `teacher_assignments` (`teacher_id`,`class_id`,`created_at`) VALUES (?,?,NOW())",
                        [$teacherId, $cid]
                    );
                } else {
                    $this->db->select(
                        "INSERT INTO `teacher_assignments` (`teacher_id`,`class_id`) VALUES (?,?)",
                        [$teacherId, $cid]
                    );
                }
            }
        }

        // ----- Ders atamaları -----
        if ($hasCourse) {
            $this->db->select(
                "DELETE FROM `teacher_assignments`
                 WHERE `teacher_id`=? AND `course_id` IS NOT NULL
                   AND (`class_id` IS NULL OR `class_id`=0)
                   AND (`course_group_id` IS NULL OR `course_group_id`=0)
                   $titleCond", [$teacherId]
            );
            foreach ($courseIds as $coid) {
                if ($coid <= 0) continue;
                if ($hasCreatedAt) {
                    $this->db->select(
                        "INSERT INTO `teacher_assignments` (`teacher_id`,`course_id`,`created_at`) VALUES (?,?,NOW())",
                        [$teacherId, $coid]
                    );
                } else {
                    $this->db->select(
                        "INSERT INTO `teacher_assignments` (`teacher_id`,`course_id`) VALUES (?,?)",
                        [$teacherId, $coid]
                    );
                }
            }
        }

        // ----- Ders grubu atamaları -----
        if ($hasGroup) {
            $this->db->select(
                "DELETE FROM `teacher_assignments`
                 WHERE `teacher_id`=? AND `course_group_id` IS NOT NULL
                   AND (`class_id` IS NULL OR `class_id`=0)
                   AND (`course_id` IS NULL OR `course_id`=0)
                   $titleCond", [$teacherId]
            );
            foreach ($groupIds as $gid) {
                if ($gid <= 0) continue;
                if ($hasCreatedAt) {
                    $this->db->select(
                        "INSERT INTO `teacher_assignments` (`teacher_id`,`course_group_id`,`created_at`) VALUES (?,?,NOW())",
                        [$teacherId, $gid]
                    );
                } else {
                    $this->db->select(
                        "INSERT INTO `teacher_assignments` (`teacher_id`,`course_group_id`) VALUES (?,?)",
                        [$teacherId, $gid]
                    );
                }
            }
        }

        $this->flash('success','Atamalar güncellendi.');
        $this->redirect('index.php?module=teachers&action=show&id='.$teacherId);
    }

    /* İstersen kısa yolları da kullanabilirsin: */
    public function store_assign_class() { $_POST['course_ids']=[]; $_POST['group_ids']=[]; $this->store(); }
    public function store_assign_course(){ $_POST['class_ids']=[]; $this->store(); }
}
