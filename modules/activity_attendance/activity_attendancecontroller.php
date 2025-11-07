<?php
// modules/activity_attendance/activity_attendancecontroller.php

// Bu modül, BaseController'a bağımlı olmadan çalışsın diye
// kendi içinde DB erişimini yönetir.
class Activity_attendanceController
{
    /** @var Database */
    private $db;

    public function __construct()
{
    // 1) Global db nesnesi varsa onu kullan
    if (isset($GLOBALS['db']) && $GLOBALS['db'] instanceof Database) {
        $this->db = $GLOBALS['db'];
        return;
    }

    // 2) Singleton erişim metotlarından biri varsa onu dene
    if (method_exists('Database', 'getInstance')) {
        $this->db = Database::getInstance();
        return;
    }
    if (method_exists('Database', 'instance')) {
        $this->db = Database::instance();
        return;
    }
    if (method_exists('Database', 'get')) {
        $this->db = Database::get();
        return;
    }

    // 3) Son çare: core/database.php henüz load edilmediyse yüklemeyi dene
    if (!class_exists('Database', false)) {
        $core = __DIR__ . '/../../core/database.php';
        if (is_file($core)) require_once $core;
    }
    if (isset($GLOBALS['db']) && $GLOBALS['db'] instanceof Database) {
        $this->db = $GLOBALS['db'];
        return;
    }

    // Bu noktaya düşmemesi gerekir; yine de anlaşılır bir mesaj verelim
    trigger_error('Database singleton erişimi bulunamadı (getInstance()/instance()).', E_USER_ERROR);
}

    /* --------------------- helpers --------------------- */
    private function redir(string $url)
    {
        header('Location: '.$url);
        exit;
    }

    private function fetchList(array $filters = []): array
    {
        $where = [];
        $args  = [];

        if (!empty($filters['activity_id'])) {
            $where[] = "aa.activity_id = ?";
            $args[]  = (int)$filters['activity_id'];
        }
        if (!empty($filters['from'])) {
            $where[] = "aa.attendance_date >= ?";
            $args[]  = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $where[] = "aa.attendance_date <= ?";
            $args[]  = $filters['to'];
        }

        $whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

        return $this->db->select("
            SELECT
                aa.id,
                aa.attendance_date,
                aa.status,
                aa.notes,
                aa.activity_id,
                aa.class_id,
                aa.entry_by_user_id,
                aa.entry_at,
                act.title           AS activity_title,
                s.id                AS student_id,
                s.name              AS student_name,
                c.name              AS class_name,
                u.name              AS entry_by_name
            FROM activity_attendance aa
            JOIN activities act ON act.id = aa.activity_id
            JOIN users s        ON s.id   = aa.student_id
            LEFT JOIN classes c ON c.id   = aa.class_id
            LEFT JOIN users u   ON u.id   = aa.entry_by_user_id
            $whereSql
            ORDER BY aa.attendance_date DESC, act.title, s.name
        ", $args);
    }

    private function fetchOne(int $id): ?array
    {
        return $this->db->fetch("
            SELECT
                aa.id,
                aa.attendance_date,
                aa.status,
                aa.notes,
                aa.activity_id,
                aa.class_id,
                aa.entry_by_user_id,
                aa.entry_at,
                act.title           AS activity_title,
                s.id                AS student_id,
                s.name              AS student_name,
                c.name              AS class_name,
                u.name              AS entry_by_name
            FROM activity_attendance aa
            JOIN activities act ON act.id = aa.activity_id
            JOIN users s        ON s.id   = aa.student_id
            LEFT JOIN classes c ON c.id   = aa.class_id
            LEFT JOIN users u   ON u.id   = aa.entry_by_user_id
            WHERE aa.id = ?
            LIMIT 1
        ", [$id]) ?: null;
    }

    /* --------------------- actions --------------------- */

    // GET /index
    public function index()
    {
        $filters = [
            'activity_id' => (int)($_GET['activity_id'] ?? 0),
            'from'        => $_GET['from'] ?? null,
            'to'          => $_GET['to']   ?? null,
        ];

        $rows = $this->fetchList($filters);

        return [
            'view'        => 'activity_attendance/index.php',
            'title'       => 'Etkinlik Yoklamaları',
            'rows'        => $rows,
            'activity_id' => $filters['activity_id'],
            'from'        => $filters['from'],
            'to'          => $filters['to'],
        ];
    }

    // GET /show&id=?
    public function show()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) return $this->redir('index.php?module=activity_attendance&action=index');

        $row = $this->fetchOne($id);
        if (!$row) return $this->redir('index.php?module=activity_attendance&action=index');

        return [
            'view'  => 'activity_attendance/show.php',
            'title' => 'Yoklama Detayı',
            'row'   => $row,
        ];
    }

    // GET /edit&id=?
    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) return $this->redir('index.php?module=activity_attendance&action=index');

        $row = $this->fetchOne($id);
        if (!$row) return $this->redir('index.php?module=activity_attendance&action=index');

        return [
            'view'  => 'activity_attendance/edit.php',
            'title' => 'Yoklama Düzenle',
            'row'   => $row,
        ];
    }

    // POST /update
    public function update()
    {
        $id     = (int)($_POST['id'] ?? 0);
        $status = (string)($_POST['status'] ?? '');
        $notes  = (string)($_POST['notes']  ?? '');

        if ($id <= 0) return $this->redir('index.php?module=activity_attendance&action=index');

        $valid = ['Geldi','Gelmedi','İzinli'];
        if (!in_array($status, $valid, true)) $status = 'Geldi';

        $this->db->execute("UPDATE activity_attendance SET status=?, notes=? WHERE id=?", [
            $status, $notes, $id
        ]);

        return $this->redir('index.php?module=activity_attendance&action=index');
    }

    // POST /delete
    public function delete()
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->db->execute("DELETE FROM activity_attendance WHERE id=?", [$id]);
        }
        return $this->redir('index.php?module=activity_attendance&action=index');
    }
}
