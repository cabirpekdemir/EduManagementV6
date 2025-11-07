<?php
require_once __DIR__ . '/../../core/database.php';

class StudentParentController
{
    protected $db;
    protected static $tableCache = [];
    protected static $colCache   = [];

    public function __construct(){ $this->db = Database::getInstance(); }

    /* ===================== PUBLIC ACTIONS ===================== */

    public function attach_from_student()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') $this->backWith('warning','Formu gönderin.');
        $student_id = (int)($_POST['student_id'] ?? 0);
        $parent_id  = (int)($_POST['parent_id'] ?? 0);
        $relation   = trim($_POST['relation'] ?? '');

        if ($student_id<=0 || $parent_id<=0) $this->backWith('danger','Geçersiz öğrenci/veli.');

        try {
            [$tbl,$stuCol,$parCol] = $this->ensurePivot(); // <-- yoksa oluştur
            $data = [$stuCol=>$student_id, $parCol=>$parent_id];
            if ($this->hasColumn($tbl,'relation') && $relation!=='') $data['relation']=$relation;
            $this->safeInsert($tbl,$data);
            $this->backWith('success','Veli ilişkilendirildi.');
        } catch (\Throwable $e) {
            $this->backWith('danger','Veli bağlama hatası: '.$e->getMessage());
        }
    }

    public function detach_from_student()
    {
        $student_id = (int)($_GET['student_id'] ?? 0);
        $parent_id  = (int)($_GET['parent_id'] ?? 0);
        $return     = $_GET['return'] ?? 'students';
        if ($student_id<=0 || $parent_id<=0) $this->backWith('danger','Geçersiz veri.');

        try {
            [$tbl,$stuCol,$parCol] = $this->ensurePivot();
            $this->exec("DELETE FROM `{$tbl}` WHERE `{$stuCol}`=? AND `{$parCol}`=?", [$student_id,$parent_id]);
            $this->backWith('success','Veli bağlantısı kaldırıldı.', $return==='users'
                ? "?module=users&action=edit&id={$parent_id}"
                : "?module=students&action=edit&id={$student_id}");
        } catch (\Throwable $e) {
            $this->backWith('danger','Silme hatası: '.$e->getMessage());
        }
    }

    public function attach_from_parent()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') $this->backWith('warning','Formu gönderin.');
        $parent_id  = (int)($_POST['parent_id'] ?? 0);
        $student_id = (int)($_POST['student_id'] ?? 0);
        $relation   = trim($_POST['relation'] ?? '');

        if ($student_id<=0 || $parent_id<=0) $this->backWith('danger','Geçersiz öğrenci/veli.');

        try {
            [$tbl,$stuCol,$parCol] = $this->ensurePivot();
            $data = [$stuCol=>$student_id, $parCol=>$parent_id];
            if ($this->hasColumn($tbl,'relation') && $relation!=='') $data['relation']=$relation;
            $this->safeInsert($tbl,$data);
            $this->backWith('success','Öğrenci ilişkilendirildi.', "?module=users&action=edit&id={$parent_id}");
        } catch (\Throwable $e) {
            $this->backWith('danger','İlişkilendirme hatası: '.$e->getMessage(), "?module=users&action=edit&id={$parent_id}");
        }
    }

    public function detach_from_parent()
    {
        $parent_id  = (int)($_GET['parent_id'] ?? 0);
        $student_id = (int)($_GET['student_id'] ?? 0);
        if ($student_id<=0 || $parent_id<=0) $this->backWith('danger','Geçersiz veri.');

        try {
            [$tbl,$stuCol,$parCol] = $this->ensurePivot();
            $this->exec("DELETE FROM `{$tbl}` WHERE `{$stuCol}`=? AND `{$parCol}`=?", [$student_id,$parent_id]);
            $this->backWith('success','Bağlantı kaldırıldı.', "?module=users&action=edit&id={$parent_id}");
        } catch (\Throwable $e) {
            $this->backWith('danger','Silme hatası: '.$e->getMessage(), "?module=users&action=edit&id={$parent_id}");
        }
    }

    /* ===================== JSON SEARCH ENDPOINTS ===================== */

    public function search_parents()
    {
        header('Content-Type: application/json; charset=utf-8');
        $q = trim($_GET['q'] ?? ''); if ($q===''){ echo '[]'; exit; }

        $rows = $this->db->select(
            "SELECT id,
                    COALESCE(name, fullname, adsoyad, CONCAT(first_name,' ',last_name), username, email) AS label,
                    email, role
             FROM `users`
             WHERE (LOWER(COALESCE(name, fullname, adsoyad, CONCAT(first_name,' ',last_name), username, email)) LIKE ?)
               AND (LOWER(COALESCE(role,'')) IN ('parent','veli'))
             ORDER BY id DESC LIMIT 15",
            ['%'.mb_strtolower($q,'UTF-8').'%']
        );

        $out = [];
        foreach ($rows ?? [] as $r) { $out[] = ['id'=>(int)$r['id'],'label'=>(string)($r['label']??''),'meta'=>(string)($r['email']??'')]; }
        echo json_encode($out); exit;
    }

    public function search_students()
    {
        header('Content-Type: application/json; charset=utf-8');
        $q = trim($_GET['q'] ?? ''); if ($q===''){ echo '[]'; exit; }

        $nameExpr = "COALESCE(name, fullname, adsoyad, CONCAT(first_name,' ',last_name))";
        $rows = $this->db->select(
            "SELECT id, {$nameExpr} AS label, email
             FROM `students`
             WHERE LOWER({$nameExpr}) LIKE ?
             ORDER BY id DESC LIMIT 15",
            ['%'.mb_strtolower($q,'UTF-8').'%']
        );

        $out = [];
        foreach ($rows ?? [] as $r) { $out[] = ['id'=>(int)$r['id'],'label'=>(string)($r['label']??''),'meta'=>(string)($r['email']??'')]; }
        echo json_encode($out); exit;
    }

    /* ===================== READ HELPERS (KUTULAR İÇİN) ===================== */

    public static function parentsOf($student_id)
    {
        $self = new self();
        // tablo yoksa boş liste döndür (fatal yok)
        if (!$self->pivotExists()) return [];
        [$tbl,$stuCol,$parCol] = $self->ensurePivot(false);
        $sql = "SELECT p.*, sp.relation
                FROM `{$tbl}` sp
                JOIN `users` p ON p.id = sp.`{$parCol}`
                WHERE sp.`{$stuCol}`=?
                ORDER BY p.id DESC";
        return $self->db->select($sql, [$student_id]) ?? [];
    }

    public static function studentsOf($parent_id)
    {
        $self = new self();
        if (!$self->pivotExists()) return [];
        [$tbl,$stuCol,$parCol] = $self->ensurePivot(false);
        $sql = "SELECT s.*, sp.relation
                FROM `{$tbl}` sp
                JOIN `students` s ON s.id = sp.`{$stuCol}`
                WHERE sp.`{$parCol}`=?
                ORDER BY s.id DESC";
        return $self->db->select($sql, [$parent_id]) ?? [];
    }

    /* ===================== LOW-LEVEL ===================== */

    // Pivot tablo var mı?
    protected function pivotExists(): bool
    {
        foreach (['student_parents','students_parents','parent_students','parent_student','student_parent_map'] as $t) {
            if ($this->tableExists($t)) return true;
        }
        return false;
    }

    // Varsa bul, yoksa (createIfMissing=true) varsayılan tabloyu oluştur ve onu döndür
    protected function ensurePivot(bool $createIfMissing = true): array
    {
        // Önce adaylar arasından mevcut olanı bul
        $candidates = ['student_parents','students_parents','parent_students','parent_student','student_parent_map'];
        $stuNames   = ['student_id','sid','stu_id'];
        $parNames   = ['parent_id','pid','user_id']; // bazı projelerde parent users tablosunda tutuluyor

        foreach ($candidates as $t) {
            if (!$this->tableExists($t)) continue;
            $stuCol = null; foreach ($stuNames as $c) if ($this->hasColumn($t,$c)) { $stuCol=$c; break; }
            $parCol = null; foreach ($parNames as $c) if ($this->hasColumn($t,$c)) { $parCol=$c; break; }
            if ($stuCol && $parCol) return [$t,$stuCol,$parCol];
        }

        // Hiçbiri yoksa ve izinliysek oluştur
        if ($createIfMissing) {
            $sql = "CREATE TABLE IF NOT EXISTS `student_parents` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `student_id` INT NOT NULL,
                        `parent_id`  INT NOT NULL,
                        `relation`   VARCHAR(20) NULL,
                        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE KEY `uk_sp` (`student_id`,`parent_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            try { $this->exec($sql, []); } catch (\Throwable $e) { /* sessizce geç */ }
            // geri dön
            return ['student_parents','student_id','parent_id'];
        }

        // Oluşturma yoksa fallback olarak varsayılan isimleri döndür (kutu tarafı zaten boş listeyle idare ediyor)
        return ['student_parents','student_id','parent_id'];
    }

    protected function tableExists(string $table): bool
    {
        if (isset(self::$tableCache[$table])) return self::$tableCache[$table];
        try { $this->db->select("DESCRIBE `{$table}`"); return self::$tableCache[$table]=true; }
        catch (\Throwable $e) { return self::$tableCache[$table]=false; }
    }

    protected function hasColumn(string $table,string $col): bool
    {
        $key="$table.$col"; if (isset(self::$colCache[$key])) return self::$colCache[$key];
        try {
            $rows=$this->db->select("DESCRIBE `{$table}`");
            foreach ($rows as $r) if (isset($r['Field']) && strcasecmp($r['Field'],$col)===0) return self::$colCache[$key]=true;
        } catch (\Throwable $e) {}
        return self::$colCache[$key]=false;
    }

    protected function safeInsert(string $table,array $data): void
    {
        $fields=[];$holders=[];$values=[];
        foreach ($data as $k=>$v) if ($this->hasColumn($table,$k)) { $fields[]="`$k`"; $holders[]='?'; $values[]=$v; }
        if (!$fields) return;
        $sql='INSERT IGNORE INTO `'.$table.'` ('.implode(',',$fields).') VALUES ('.implode(',',$holders).')';
        $this->exec($sql,$values);
    }

    protected function exec(string $sql,array $params=[]){
        foreach (['execute','run','nonQuery','query'] as $m) if (method_exists($this->db,$m)) return $this->db->$m($sql,$params);
        return $this->db->select($sql,$params);
    }

    protected function backWith(string $type,string $msg,string $to=null){
        $_SESSION['flash']=['type'=>$type,'message'=>$msg];
        $base=$_SERVER['PHP_SELF']??'/index.php';
        if(!$to){ $to=$_SERVER['HTTP_REFERER']??'?module=students&action=index'; }
        header('Location: '.$base.$to); exit;
    }
}
