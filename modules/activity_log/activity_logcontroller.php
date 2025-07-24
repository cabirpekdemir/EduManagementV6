<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class Activity_logController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        
        // Bu modüle sadece adminler erişebilir
        // Sizin standart oturum yapınıza göre güncellendi:
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            die("Bu sayfaya erişim yetkiniz yok.");
        }
    }

    // Logları listele
    public function index()
    {
        $where = " WHERE 1=1 ";
        $params = [];
        
        $filter_user = $_GET['user_id'] ?? '';
        $filter_module = $_GET['module'] ?? '';
        $filter_date_start = $_GET['date_start'] ?? '';

        if ($filter_user) { $where .= " AND user_id = ? "; $params[] = $filter_user; }
        if ($filter_module) { $where .= " AND module = ? "; $params[] = $filter_module; }
        if ($filter_date_start) { $where .= " AND created_at >= ? "; $params[] = $filter_date_start; }
        
        $logs = $this->db->select("SELECT * FROM activity_log $where ORDER BY id DESC LIMIT 500", $params);
        $users = $this->db->select("SELECT id, name FROM users ORDER BY name ASC");

        return ['logs' => $logs, 'users' => $users, 'filters' => $_GET];
    }

    // Logları CSV olarak indir
    public function export()
    {
        $logs = $this->db->select("SELECT * FROM activity_log ORDER BY id ASC");

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=activity_log_' . date('Y-m-d') . '.csv');
        $output = fopen('php://output', 'w');
        
        fputcsv($output, ['ID', 'Kullanici ID', 'Kullanici Adi', 'Eylem', 'Modul', 'Kayit ID', 'Aciklama', 'Tarih']);

        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['user_id'],
                $log['user_name'],
                $log['action'],
                $log['module'],
                $log['record_id'],
                $log['description'],
                $log['created_at']
            ]);
        }
        
        log_activity('EXPORT', 'ActivityLog', null, 'Tüm aktivite logları CSV olarak indirildi.');
        exit;
    }

    // Belirli bir tarihten önceki logları kalıcı olarak sil
    public function purge()
    {
        $purge_date = $_POST['purge_date'] ?? '';
        if (empty($purge_date)) {
            die("Lütfen geçerli bir tarih seçin.");
        }

        $this->db->getConnection()->prepare("DELETE FROM activity_log WHERE created_at < ?")->execute([$purge_date]);
        
        log_activity('PURGE', 'ActivityLog', null, "Tüm aktivite logları $purge_date tarihinden öncesi için kalıcı olarak silindi.");
        
        header('Location: index.php?module=activity_log&action=index&purged=true');
        exit;
    }
}