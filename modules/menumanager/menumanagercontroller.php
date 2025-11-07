<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class MenumanagerController
{
    protected $db;
    protected $currentUser;
    // parent_id değerlerini normalize edip ağaç çıkarır
private function mm_fetchAllMenusTree(): array {
    $rows = $this->db->select("
        SELECT id, title,
               COALESCE(NULLIF(parent_id, ''), 0) AS parent_id,
               COALESCE(display_order, 0) AS display_order
        FROM menus
        ORDER BY parent_id ASC, display_order ASC, title ASC
    ") ?? [];

    $byId = [];
    foreach ($rows as $r) {
        $id = (int)$r['id'];
        $byId[$id] = [
            'id'            => $id,
            'title'         => $r['title'] ?? '',
            'parent_id'     => (int)$r['parent_id'],
            'display_order' => (int)$r['display_order'],
            'children'      => [],
        ];
    }
    foreach ($byId as $id => &$node) {
        $pid = (int)$node['parent_id'];
        if ($pid !== 0 && isset($byId[$pid])) {
            $byId[$pid]['children'][] = &$node;
        }
    }
    unset($node);
    return $byId;
}

private function mm_flattenOptions(array $nodes, int $depth = 0, array $exclude = []): array {
    // Görüntü sırası: display_order, sonra title
    usort($nodes, function($a,$b){
        $cmp = ($a['display_order'] <=> $b['display_order']);
        return $cmp !== 0 ? $cmp : strcmp($a['title'] ?? '', $b['title'] ?? '');
    });

    $out = [];
    foreach ($nodes as $n) {
        if (!in_array($n['id'], $exclude, true)) {
            $out[] = ['id'=>$n['id'], 'title'=>$n['title'], 'depth'=>$depth];
            if (!empty($n['children'])) {
                $out = array_merge($out, $this->mm_flattenOptions($n['children'], $depth+1, $exclude));
            }
        }
    }
    return $out;
}

private function mm_parentOptions(?int $excludeRootId = null): array {
    $byId  = $this->mm_fetchAllMenusTree();

    // kökler
    $roots = [];
    foreach ($byId as $id => $n) {
        $pid = (int)$n['parent_id'];
        if ($pid === 0 || !isset($byId[$pid])) {
            $roots[] = $n;
        }
    }

    // kendini ve varsa altlarını dışla (edit için)
    $exclude = [];
    if ($excludeRootId && isset($byId[$excludeRootId])) {
        $stack = [$byId[$excludeRootId]];
        while ($stack) {
            $cur = array_pop($stack);
            $exclude[] = $cur['id'];
            foreach ($cur['children'] as $ch) { $stack[] = $ch; }
        }
    }
    return $this->mm_flattenOptions($roots, 0, $exclude);
}
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->currentUser = $_SESSION['user'] ?? null;

        // Sadece admin erişimi
        if (($this->currentUser['role'] ?? 'guest') !== 'admin') {
            log_activity('ACCESS_DENIED', 'MenuManager', null, 'Yetkisiz erişim denemesi.');
            die("⛔ Bu modüle sadece adminler erişebilir!");
        }
    }
    
    public function index()
    {
        $sql = "SELECT m.*, GROUP_CONCAT(mr.role) AS assigned_roles
                FROM menus m
                LEFT JOIN menu_roles mr ON m.id = mr.menu_id
                GROUP BY m.id
                ORDER BY m.parent_id ASC, m.display_order ASC, m.title ASC";
        $all = $this->db->select($sql);

        $byId = [];
        foreach ($all as $row) {
            $row['assigned_roles_array'] = $row['assigned_roles'] ? explode(',', $row['assigned_roles']) : [];
            $row['children'] = [];
            $row['parent_id'] = (int)($row['parent_id'] ?? 0);
            $byId[(int)$row['id']] = $row;
        }

        foreach ($byId as $id => $item) {
            $pid = (int)$item['parent_id'];
            if ($pid !== 0 && isset($byId[$pid])) {
                $byId[$pid]['children'][] = &$byId[$id];
            }
        }

        $tree = [];
        foreach ($byId as $id => $item) {
            $pid = (int)$item['parent_id'];
            if ($pid === 0 || !isset($byId[$pid])) {
                $tree[$id] = $item;
            }
        }

        $sortFn = function (&$nodes) use (&$sortFn) {
            usort($nodes, function($a, $b) {
                $cmp = ($a['display_order'] <=> $b['display_order']);
                if ($cmp === 0) {
                    return $a['title'] <=> $b['title'];
                }
                return $cmp;
            });
            foreach ($nodes as &$n) {
                if (!empty($n['children'])) {
                    $sortFn($n['children']);
                }
            }
        };
        $roots = array_values($tree);
        $sortFn($roots);

        return [
            'menus_tree' => $roots,
            'all_roles'  => ['admin', 'teacher', 'student', 'parent'],
        ];
    }

public function create()
{
    $parent_options = $this->db->select("
        SELECT id, title
        FROM menus
        WHERE COALESCE(NULLIF(parent_id, ''), 0) = 0
        ORDER BY COALESCE(display_order,0) ASC, title ASC
    ") ?? [];

    return [
        'menu'            => null,
        'parent_options'  => $parent_options,
        'parent_menus'    => $parent_options, // bazı view'lar bu ismi bekliyor
        'all_roles'       => ['admin','teacher','student','parent'],
        'assigned_roles'  => [],
        'isEdit'          => false,
        'formAction'      => 'index.php?module=menumanager&action=store',
    ];
}
public function store()
{
    $parent_id     = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : 0;
    $title         = trim($_POST['title'] ?? '');
    $url           = trim($_POST['url'] ?? '#');
    $icon          = $_POST['icon'] ?? null;
    $display_order = (int)($_POST['display_order'] ?? 0);
    $is_active     = isset($_POST['is_active']) ? 1 : 0;
    $roles         = $_POST['roles'] ?? [];

    if ($title === '') {
        redirect('index.php?module=menumanager&action=create&error=empty_title');
        exit;
    }

    try {
        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO menus (parent_id, title, url, icon, display_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$parent_id, $title, $url, $icon, $display_order, $is_active]);
        $menu_id = (int)$this->db->getConnection()->lastInsertId();

        if (!empty($roles)) {
            $ins = $this->db->getConnection()->prepare("INSERT INTO menu_roles (menu_id, role) VALUES (?, ?)");
            foreach ($roles as $r) { $ins->execute([$menu_id, $r]); }
        }

        log_activity('CREATE', 'MenuManager', $menu_id, "Menü oluşturuldu: '$title'");
        redirect('index.php?module=menumanager&action=index&status=created');
    } catch (PDOException $e) {
        log_activity('ERROR', 'MenuManager', null, "Menü oluşturulurken hata: " . $e->getMessage());
        redirect('index.php?module=menumanager&action=create&error=db_error&msg=' . urlencode($e->getMessage()));
    }
    exit;
}
public function edit()
{
    $id   = (int)($_GET['id'] ?? 0);
    $menu = $this->db->select("SELECT * FROM menus WHERE id = ?", [$id])[0] ?? null;
    if (!$menu) {
        redirect('index.php?module=menumanager&action=index&error=not_found');
        exit;
    }

    $parent_options = $this->db->select("
        SELECT id, title
        FROM menus
        WHERE COALESCE(NULLIF(parent_id, ''), 0) = 0
          AND id <> ?
        ORDER BY COALESCE(display_order,0) ASC, title ASC
    ", [$id]) ?? [];

    $assigned_roles_raw = $this->db->select("SELECT role FROM menu_roles WHERE menu_id = ?", [$id]) ?? [];
    $assigned_roles     = array_column($assigned_roles_raw, 'role');

    return [
        'menu'            => $menu,
        'parent_options'  => $parent_options,
        'parent_menus'    => $parent_options,
        'all_roles'       => ['admin','teacher','student','parent'],
        'assigned_roles'  => $assigned_roles,
        'isEdit'          => true,
        'formAction'      => 'index.php?module=menumanager&action=update&id='.$id,
    ];
}
public function update()
{
    $id            = (int)($_POST['id'] ?? 0);
    $parent_id     = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : 0;
    $title         = trim($_POST['title'] ?? '');
    $url           = trim($_POST['url'] ?? '#');
    $icon          = $_POST['icon'] ?? null;
    $display_order = (int)($_POST['display_order'] ?? 0);
    $is_active     = isset($_POST['is_active']) ? 1 : 0;
    $roles         = $_POST['roles'] ?? [];

    if (!$id || $title === '') {
        redirect('index.php?module=menumanager&action=edit&id=' . $id . '&error=invalid_data');
        exit;
    }
    if ($id === $parent_id) {
        redirect('index.php?module=menumanager&action=edit&id=' . $id . '&error=invalid_parent');
        exit;
    }

    try {
        $stmt = $this->db->getConnection()->prepare(
            "UPDATE menus SET parent_id=?, title=?, url=?, icon=?, display_order=?, is_active=? WHERE id=?"
        );
        $stmt->execute([$parent_id, $title, $url, $icon, $display_order, $is_active, $id]);

        $this->db->getConnection()->prepare("DELETE FROM menu_roles WHERE menu_id = ?")->execute([$id]);
        if (!empty($roles)) {
            $role_stmt = $this->db->getConnection()->prepare("INSERT INTO menu_roles (menu_id, role) VALUES (?, ?)");
            foreach ($roles as $role) { $role_stmt->execute([$id, $role]); }
        }

        log_activity('UPDATE', 'MenuManager', $id, "Menü güncellendi: '$title'");
        redirect('index.php?module=menumanager&action=index&status=updated');
    } catch (PDOException $e) {
        log_activity('ERROR', 'MenuManager', $id, "Menü güncellenirken hata: " . $e->getMessage());
        redirect('index.php?module=menumanager&action=edit&id=' . $id . '&error=db_error&msg=' . urlencode($e->getMessage()));
    }
    exit;
}

    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            redirect('index.php?module=menumanager&action=index&error=missing_id');
            exit;
        }

        $this->db->getConnection()->prepare("UPDATE menus SET parent_id = 0 WHERE parent_id = ?")->execute([$id]);
        $this->db->getConnection()->prepare("DELETE FROM menu_roles WHERE menu_id = ?")->execute([$id]);

        $menu = $this->db->select("SELECT title FROM menus WHERE id = ?", [$id])[0] ?? null;
        $this->db->getConnection()->prepare("DELETE FROM menus WHERE id = ?")->execute([$id]);

        if ($menu) {
            log_activity('DELETE', 'MenuManager', $id, "Menü silindi: '{$menu['title']}'");
        }
        redirect('index.php?module=menumanager&action=index&status=deleted');
        exit;
    }

    public function auto_add_modules()
    {
        $module_root_path = __DIR__ . '/../../modules/';
        $module_folders   = array_filter(glob($module_root_path . '*'), 'is_dir');

        $existing_menu_urls = $this->db->select("SELECT url FROM menus");
        $existing_modules = [];
        foreach ($existing_menu_urls as $menu_url) {
            $query = parse_url($menu_url['url'], PHP_URL_QUERY);
            if ($query) {
                parse_str($query, $params);
                if (!empty($params['module'])) {
                    $existing_modules[] = $params['module'];
                }
            }
        }
        $existing_modules = array_unique($existing_modules);

        $added_count = 0;
        foreach ($module_folders as $folder) {
            $module_name = basename($folder);
            if ($module_name === 'menumanager') continue;

            if (!in_array($module_name, $existing_modules, true)) {
                $title = ucwords(str_replace('_', ' ', $module_name));
                $url   = "index.php?module={$module_name}&action=index";
                $icon  = 'fa-folder';

                $stmt = $this->db->getConnection()->prepare(
                    "INSERT INTO menus (parent_id, title, url, icon, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([0, $title, $url, $icon, 99, 1]);
                $parent_menu_id = (int)$this->db->getConnection()->lastInsertId();
                $added_count++;

                log_activity('AUTO_ADD', 'MenuManager', $parent_menu_id, "Modül menüye eklendi: '$title'");

                $this->db->getConnection()->prepare("INSERT INTO menu_roles (menu_id, role) VALUES (?, 'admin')")->execute([$parent_menu_id]);
            }
        }

        redirect('index.php?module=menumanager&action=index&status=modules_added&count=' . $added_count);
        exit;
    }
}
