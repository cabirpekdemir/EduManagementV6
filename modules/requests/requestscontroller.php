<?php
require_once __DIR__ . '/../../core/database.php';

class RequestsController
{
    protected $db;
    protected static $colCache = [];   // "table.col" => true/false
    protected static $tableCache = []; // "table" => true/false

    public function __construct() { $this->db = Database::getInstance(); }

    /* ===================== PUBLIC ACTIONS ===================== */

    // LISTE
    public function index()
    {
        $currentUser = $_SESSION['user'] ?? ['id'=>0, 'role'=>'guest'];

        // Geniş alan kümesi; hangisi varsa onu çekeriz.
        $want = [
            'id',
            // Başlık varyantları
            'title','subject','request_title',
            // İçerik varyantları (liste için şart değil ama normalize ederiz)
            'body','message','content','description','text',
            // Diğerleri
            'created_at','updated_at','type','status','priority',
            'student_name','student_id','parent_id',
            'created_by','sender_id','user_id','owner_id','opened_by',
            'assigned_role'
        ];
        $cols = $this->existingColumns('requests', $want);

        $sql = !empty($cols)
            ? 'SELECT ' . implode(',', array_map([$this,'qIdent'],$cols)) .
              ' FROM '.$this->qIdent('requests').' ORDER BY '.$this->qIdent('id').' DESC'
            : 'SELECT * FROM '.$this->qIdent('requests').' ORDER BY '.$this->qIdent('id').' DESC';

        $rows = $this->db->select($sql) ?? [];
        $requests = [];
        foreach ($rows as $r) {
            $requests[] = $this->normalizeRequestRow($r); // title/body garanti
        }

        return [
            'view'        => 'requests/index',
            'requests'    => $requests,
            'currentUser' => $currentUser,
        ];
    }

    // FORM (GET)
    public function create()
    {
        $currentUser = $_SESSION['user'] ?? ['id'=>0, 'role'=>'guest'];
        return [
            'view'        => 'requests/create',
            'currentUser' => $currentUser,
        ];
    }

    // KAYDET (POST)
    public function store()
    {
        // Yalnızca POST
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $_SESSION['flash'] = ['type'=>'warning','message'=>'Formu göndererek deneyin.'];
            $this->redirect('?module=requests&action=create');
        }

        $currentUser  = $_SESSION['user'] ?? ['id'=>0, 'role'=>'guest'];
        $userId       = (int)($currentUser['id'] ?? 0);

        // Form girdileri
        $formTitle    = trim($_POST['title'] ?? '');
        $formBody     = trim($_POST['body'] ?? '');
        $type         = trim($_POST['type'] ?? '');
        $priority     = trim($_POST['priority'] ?? '');
        $student_name = trim($_POST['student_name'] ?? '');
        $recipient    = trim($_POST['recipient'] ?? '');  // admin | advisor_teacher | other

        if ($formTitle === '' || $formBody === '') {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Başlık ve açıklama zorunludur.'];
            $this->redirect('?module=requests&action=create');
        }

        // Hedef kolonları şemaya göre belirle
        $titleField = $this->firstExistingColumn('requests', ['title','subject','request_title']);
        $bodyField  = $this->firstExistingColumn('requests', ['body','message','content','description','text']);

        $data = [];

        // Zorunlu eşleştirmeler (hangi isim varsa ona yaz)
        if ($titleField) $data[$titleField] = $formTitle;
        if ($bodyField)  $data[$bodyField]  = $formBody;

        // Ortak alanlar (varsa yaz)
        if ($this->hasColumn('requests','created_at'))  $data['created_at'] = date('Y-m-d H:i:s');
        if ($this->hasColumn('requests','type') && $type!=='')      $data['type'] = $type;
        if ($this->hasColumn('requests','priority'))                $data['priority'] = $priority ?: null;
        if ($this->hasColumn('requests','status'))                  $data['status'] = 'open';
        if ($this->hasColumn('requests','student_name') && $student_name!=='')
            $data['student_name'] = $student_name;

        // Gönderen alanı: tabloda hangisi varsa onu doldur
        $senderField = $this->firstExistingColumn('requests', ['sender_id','created_by','user_id','owner_id','opened_by']);
        if ($senderField) $data[$senderField] = $userId;

        // Muhatap (rol)
        if ($this->hasColumn('requests','assigned_role') && $recipient!=='') {
            $allowed = ['admin','advisor_teacher','other'];
            $data['assigned_role'] = in_array($recipient,$allowed,true) ? $recipient : 'other';
        }

        try {
            $this->safeInsert('requests', $data);
            $_SESSION['flash'] = ['type'=>'success','message'=>'Talep oluşturuldu.'];
        } catch (\Throwable $e) {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Kayıt sırasında hata: '.$e->getMessage()];
            $this->redirect('?module=requests&action=create');
        }

        $this->redirect('?module=requests&action=index');
    }

    // DETAY (GET)
    public function show()
    {
        $currentUser = $_SESSION['user'] ?? ['id'=>0, 'role'=>'guest'];
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Geçersiz talep.'];
            $this->redirect('?module=requests&action=index');
        }

        $reqWant = [
            'id',
            'title','subject','request_title',
            'body','message','content','description','text',
            'created_at','updated_at','type','status','priority',
            'student_name','student_id','parent_id',
            'created_by','sender_id','user_id','owner_id','opened_by',
            'assigned_role'
        ];
        $reqCols = $this->existingColumns('requests', $reqWant);

        $sql = !empty($reqCols)
            ? 'SELECT '.implode(',', array_map([$this,'qIdent'],$reqCols)).
              ' FROM '.$this->qIdent('requests').' WHERE '.$this->qIdent('id').'=?'
            : 'SELECT * FROM '.$this->qIdent('requests').' WHERE '.$this->qIdent('id').'=?';

        $row = $this->db->select($sql, [$id]);
        $request = is_array($row) && isset($row[0]) ? $row[0] : ($row ?? []);
        $request = $this->normalizeRequestRow($request); // title/body garanti

        // Replies (tamamen şema-keşifli)
        $replies = [];
        if ($this->tableExists('request_replies')) {
            $repWant = [
                'id','request_id',
                'user_id','sender_id','created_by',
                'body','message','content','description','text',
                'created_at','updated_at'
            ];
            $repCols = $this->existingColumns('request_replies', $repWant);

            if (!empty($repCols)) {
                $repSql = 'SELECT '.implode(',', array_map([$this,'qIdent'],$repCols)).
                          ' FROM '.$this->qIdent('request_replies').
                          ' WHERE '.$this->qIdent('request_id').'=?';
                if ($this->hasColumn('request_replies','id')) {
                    $repSql .= ' ORDER BY '.$this->qIdent('id').' ASC';
                } elseif ($this->hasColumn('request_replies','created_at')) {
                    $repSql .= ' ORDER BY '.$this->qIdent('created_at').' ASC';
                }
            } else {
                $repSql = 'SELECT * FROM '.$this->qIdent('request_replies').
                          ' WHERE '.$this->qIdent('request_id').'=?';
            }

            $raw = $this->db->select($repSql, [$id]) ?? [];

            $authorField = $this->firstExistingColumn('request_replies', ['user_id','sender_id','created_by']);
            $bodyField   = $this->firstExistingColumn('request_replies', ['body','message','content','description','text']);

            foreach ($raw as $r) {
                $replies[] = [
                    'user_id'    => $authorField && array_key_exists($authorField,$r) ? $r[$authorField] : null,
                    'body'       => $bodyField   && array_key_exists($bodyField,  $r) ? $r[$bodyField]   : '',
                    'created_at' => $r['created_at'] ?? ($r['updated_at'] ?? ''),
                ];
            }
        }

        return [
            'view'        => 'requests/show',
            'request'     => $request,
            'replies'     => $replies,
            'currentUser' => $currentUser,
        ];
    }

    // CEVAP KAYDET (POST)
    public function store_reply()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $_SESSION['flash'] = ['type'=>'warning','message'=>'Cevap formundan gönderin.'];
            $this->redirect('?module=requests&action=index');
        }

        $currentUser = $_SESSION['user'] ?? ['id'=>0, 'role'=>'guest'];
        $userId      = (int)($currentUser['id'] ?? 0);
        $request_id  = (int)($_POST['request_id'] ?? 0);
        $body        = trim($_POST['body'] ?? '');
        $newStatus   = $_POST['status'] ?? '';

        if ($request_id<=0 || $body==='') {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Geçersiz veri.'];
            $this->redirect('?module=requests&action=index');
        }

        try {
            if ($this->tableExists('request_replies')) {
                $whoField  = $this->firstExistingColumn('request_replies', ['user_id','sender_id','created_by']);
                $bodyField = $this->firstExistingColumn('request_replies', ['body','message','content','description','text']);

                $reply = ['request_id' => $request_id];
                if ($whoField)   $reply[$whoField]  = $userId;
                if ($bodyField)  $reply[$bodyField] = $body;
                if ($this->hasColumn('request_replies','created_at'))
                    $reply['created_at'] = date('Y-m-d H:i:s');

                $this->safeInsert('request_replies', $reply);
            }

            if ($this->hasColumn('requests','status') && $newStatus!=='') {
                $this->exec(
                    'UPDATE '.$this->qIdent('requests').' SET '.$this->qIdent('status').'=? WHERE '.$this->qIdent('id').'=?',
                    [$newStatus, $request_id]
                );
            }

            $_SESSION['flash'] = ['type'=>'success','message'=>'Yanıt kaydedildi.'];
        } catch (\Throwable $e) {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Yanıt sırasında hata: '.$e->getMessage()];
        }

        $this->redirect('?module=requests&action=show&id='.$request_id);
    }

    /* ===================== PRIVATE HELPERS ===================== */

    // Database yazma metodu uyumluluğu (execute/run/nonQuery/query; yoksa select'e düşer)
    protected function exec(string $sql, array $params = [])
    {
        foreach (['execute','run','nonQuery','query'] as $m) {
            if (method_exists($this->db, $m)) return $this->db->$m($sql, $params);
        }
        return $this->db->select($sql, $params);
    }

    protected function tableExists(string $table): bool
    {
        $table = trim($table);
        if ($table === '') return false;
        if (array_key_exists($table, self::$tableCache)) return self::$tableCache[$table];

        try {
            $this->db->select('DESCRIBE '.$this->qIdent($table));
            return self::$tableCache[$table] = true;
        } catch (\Throwable $e) {
            return self::$tableCache[$table] = false;
        }
    }

    protected function hasColumn(string $table, string $col): bool
    {
        $key = strtolower($table.'.'.$col);
        if (array_key_exists($key, self::$colCache)) return self::$colCache[$key];
        if (!$this->tableExists($table)) return self::$colCache[$key] = false;

        try {
            $rows = $this->db->select('DESCRIBE '.$this->qIdent($table));
            foreach ($rows as $r) {
                if (isset($r['Field']) && strtolower($r['Field']) === strtolower($col)) {
                    return self::$colCache[$key] = true;
                }
            }
        } catch (\Throwable $e) {
            try {
                $dbNameRow = $this->db->select('SELECT DATABASE() AS dbname');
                $dbName = $dbNameRow[0]['dbname'] ?? null;
                if ($dbName) {
                    $hit = $this->db->select(
                        'SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=? LIMIT 1',
                        [$dbName, $table, $col]
                    );
                    if (!empty($hit)) return self::$colCache[$key] = true;
                }
            } catch (\Throwable $e2) {}
        }
        return self::$colCache[$key] = false;
    }

    protected function safeInsert(string $table, array $data): void
    {
        if (!$this->tableExists($table)) return;

        $fields = [];
        $values = [];
        $holders = [];
        foreach ($data as $k=>$v) {
            if ($this->hasColumn($table, $k)) {
                $fields[]  = $this->qIdent($k);
                $values[]  = $v;
                $holders[] = '?';
            }
        }
        if (empty($fields)) return;

        $sql = 'INSERT INTO '.$this->qIdent($table).' ('.implode(',', $fields).') VALUES ('.implode(',', $holders).')';
        $this->exec($sql, $values);
    }

    // İlk mevcut kolonu döndür (ör. ['title','subject'] listesinde ilk bulunan)
    protected function firstExistingColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $c) {
            if ($this->hasColumn($table, $c)) return $c;
        }
        return null;
    }

    // Bir tabloda mevcut olan kolonların listesini döndür
    protected function existingColumns(string $table, array $candidates): array
    {
        $out = [];
        foreach ($candidates as $c) if ($this->hasColumn($table, $c)) $out[] = $c;
        return $out;
    }

    // View’lar için title/body’yi garanti altına al
    protected function normalizeRequestRow(array $row): array
    {
        if (!isset($row['title']) || $row['title'] === '' || $row['title'] === null) {
            foreach (['subject','request_title'] as $alt) {
                if (isset($row[$alt]) && $row[$alt] !== '') { $row['title'] = $row[$alt]; break; }
            }
        }
        if (!isset($row['body']) || $row['body'] === '' || $row['body'] === null) {
            foreach (['message','content','description','text'] as $alt) {
                if (isset($row[$alt]) && $row[$alt] !== '') { $row['body'] = $row[$alt]; break; }
            }
        }
        return $row;
    }

    protected function qIdent(string $name): string
    {
        $name = str_replace('`','``',$name);
        return '`'.$name.'`';
    }

    protected function redirect(string $queryString): void
    {
        $base = $_SERVER['PHP_SELF'] ?? '/index.php';
        header('Location: '.$base.$queryString);
        exit;
    }
}
