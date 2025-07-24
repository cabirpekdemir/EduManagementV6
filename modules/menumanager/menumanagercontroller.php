<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class MenumanagerController
{
    protected $db;
    protected $currentUser;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->currentUser = $_SESSION['user'] ?? null;

        // Yetkilendirme kontrolü
        if (($this->currentUser['role'] ?? 'guest') !== 'admin') {
            log_activity('ACCESS_DENIED', 'MenuManager', null, 'Yetkisiz erişim denemesi.');
            die("⛔ Bu modüle sadece adminler erişebilir!");
        }
    }

    /**
     * Menü öğelerini hiyerarşik olarak listeler.
     */
    public function index()
    {
        // Tüm menüleri ve rollerini çekelim
        $sql = "SELECT m.*, GROUP_CONCAT(mr.role) as assigned_roles
                FROM menus m
                LEFT JOIN menu_roles mr ON m.id = mr.menu_id
                GROUP BY m.id
                ORDER BY m.parent_id ASC, m.display_order ASC, m.title ASC";
        $all_menus_flat = $this->db->select($sql);

        // Menüleri hiyerarşik hale getirelim
        $menus_tree = [];
        $items_by_id = []; // Tüm öğeleri ID'lerine göre indeksle

        foreach ($all_menus_flat as $menu) {
            $menu['assigned_roles_array'] = $menu['assigned_roles'] ? explode(',', $menu['assigned_roles']) : [];
            $menu['children'] = []; // Her öğe için çocuk dizisini başlat
            $items_by_id[$menu['id']] = $menu;
        }

        // Çocukları ebeveynlerine bağla
        foreach ($items_by_id as $id => &$item) { // & ile referans alarak orijinal diziyi değiştir
            if ($item['parent_id'] !== null && $item['parent_id'] !== '0' && isset($items_by_id[$item['parent_id']])) {
                $items_by_id[$item['parent_id']]['children'][] = &$item;
            }
        }
        unset($item); // Referansı kaldır

        // Ana menüleri (parent_id 0 veya null olanlar) ağacın köküne ekle
        foreach ($items_by_id as $id => $item) {
            // parent_id'si 0 olanlar ana menü, veya hiç atanmamışsa (null)
            if ($item['parent_id'] === 0 || $item['parent_id'] === null || !isset($items_by_id[$item['parent_id']])) {
                $menus_tree[$item['id']] = $item;
            }
        }
        
        // Menü ağacını display_order'a göre sırala
        uasort($menus_tree, function($a, $b) {
            return $a['display_order'] <=> $b['display_order'];
        });

        // Çocuk menüleri de kendi içinde sırala (isteğe bağlı, draw_menu_items'da da yapılabilir)
        foreach ($menus_tree as &$root_menu) {
            if (!empty($root_menu['children'])) {
                uasort($root_menu['children'], function($a, $b) {
                    return $a['display_order'] <=> $b['display_order'];
                });
            }
        }
        unset($root_menu);


        return ['menus_tree' => $menus_tree, 'all_roles' => ['admin', 'teacher', 'student', 'parent']];
    }

    /**
     * Yeni menü öğesi oluşturma formunu gösterir.
     */
    public function create()
    {
        // parent_id'si 0 olan veya NULL olan menüleri üst menü olarak çek
        $parent_menus = $this->db->select("SELECT id, title FROM menus WHERE parent_id = 0 OR parent_id IS NULL ORDER BY title ASC");
        return [
            'menu' => null,
            'parent_menus' => $parent_menus,
            'all_roles' => ['admin', 'teacher', 'student', 'parent'],
            'assigned_roles' => [],
            'isEdit' => false,
            'formAction' => 'index.php?module=menumanager&action=store'
        ];
    }

    /**
     * Yeni menü öğesini kaydeder.
     */
    public function store()
    {
        // Düzeltme: parent_id boşsa null yerine 0 ata
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0; 
        $title = $_POST['title'] ?? '';
        $url = $_POST['url'] ?? '#';
        $icon = $_POST['icon'] ?? null;
        $display_order = (int)($_POST['display_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $roles = $_POST['roles'] ?? [];

        if (empty($title)) {
            redirect('index.php?module=menumanager&action=create&error=empty_title');
            exit;
        }

        try {
            $stmt = $this->db->getConnection()->prepare(
                "INSERT INTO menus (parent_id, title, url, icon, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$parent_id, $title, $url, $icon, $display_order, $is_active]);
            $menu_id = $this->db->getConnection()->lastInsertId();

            if ($menu_id && !empty($roles)) {
                $role_stmt = $this->db->getConnection()->prepare("INSERT INTO menu_roles (menu_id, role) VALUES (?, ?)");
                foreach ($roles as $role) {
                    $role_stmt->execute([$menu_id, $role]);
                }
            }

            log_activity('CREATE', 'MenuManager', $menu_id, "Menü öğesi oluşturdu: '$title'");
            redirect('index.php?module=menumanager&action=index&status=created');
        } catch (PDOException $e) {
            log_activity('ERROR', 'MenuManager', null, "Menü öğesi oluşturulurken hata: " . $e->getMessage());
            redirect('index.php?module=menumanager&action=create&error=db_error&msg=' . urlencode($e->getMessage()));
        }
        exit;
    }

    /**
     * Menü öğesi düzenleme formunu gösterir.
     */
    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $menu = $this->db->select("SELECT * FROM menus WHERE id = ?", [$id])[0] ?? null;

        if (!$menu) {
            redirect('index.php?module=menumanager&action=index&error=not_found');
            exit;
        }

        // Kendisi üst menü olarak seçilemez, ancak kendi çocukları da üst menü olarak seçilemez.
        // Hiyerarşik bir döngüyü engellemek için daha sofistike bir kontrol gerekebilir,
        // şimdilik sadece kendini dışarıda bırakalım.
        // parent_id'si 0 olan veya NULL olan menüleri üst menü olarak çek
        $parent_menus = $this->db->select("SELECT id, title FROM menus WHERE (parent_id = 0 OR parent_id IS NULL) AND id != ? ORDER BY title ASC", [$id]);
        
        $assigned_roles_raw = $this->db->select("SELECT role FROM menu_roles WHERE menu_id = ?", [$id]);
        $assigned_roles = array_column($assigned_roles_raw, 'role');

        return [
            'menu' => $menu,
            'parent_menus' => $parent_menus,
            'all_roles' => ['admin', 'teacher', 'student', 'parent'],
            'assigned_roles' => $assigned_roles,
            'isEdit' => true,
            'formAction' => 'index.php?module=menumanager&action=update&id=' . $id
        ];
    }

    /**
     * Menü öğesini günceller.
     */
    public function update()
    {
        $id = $_POST['id'] ?? 0;
        // Düzeltme: parent_id boşsa null yerine 0 ata
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0; 
        $title = $_POST['title'] ?? '';
        $url = $_POST['url'] ?? '#';
        $icon = $_POST['icon'] ?? null;
        $display_order = (int)($_POST['display_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $roles = $_POST['roles'] ?? [];

        if (empty($title) || !$id) {
            redirect('index.php?module=menumanager&action=edit&id=' . $id . '&error=invalid_data');
            exit;
        }
        // Kendisinin parent'ı olamaz veya kendi alt menüsünün alt menüsü olamaz (döngü engelleme)
        if ($id == $parent_id) { 
             redirect('index.php?module=menumanager&action=edit&id=' . $id . '&error=invalid_parent');
             exit;
        }
        // Daha sofistike bir döngü kontrolü: Eğer parent_id, güncellenen menünün kendisinin veya çocuklarının ID'si ise
        // Bu fonksiyonu implement etmek daha kompleks olabilir:
        // if ($this->isCircularParent($id, $parent_id)) {
        //     redirect('index.php?module=menumanager&action=edit&id=' . $id . '&error=circular_parent_detected');
        //     exit;
        // }


        try {
            $stmt = $this->db->getConnection()->prepare(
                "UPDATE menus SET parent_id = ?, title = ?, url = ?, icon = ?, display_order = ?, is_active = ? WHERE id = ?"
            );
            // Hatanın olduğu satır 170 burasıydı. parent_id'nin null olmaması sağlanıyor.
            $stmt->execute([$parent_id, $title, $url, $icon, $display_order, $is_active, $id]); 

            // Önceki rol atamalarını sil
            $this->db->getConnection()->prepare("DELETE FROM menu_roles WHERE menu_id = ?")->execute([$id]);
            // Yeni rolleri ekle
            if (!empty($roles)) {
                $role_stmt = $this->db->getConnection()->prepare("INSERT INTO menu_roles (menu_id, role) VALUES (?, ?)");
                foreach ($roles as $role) {
                    $role_stmt->execute([$id, $role]);
                }
            }

            log_activity('UPDATE', 'MenuManager', $id, "Menü öğesini güncelledi: '$title'");
            redirect('index.php?module=menumanager&action=index&status=updated');
        } catch (PDOException $e) {
            log_activity('ERROR', 'MenuManager', $id, "Menü öğesi güncellenirken hata: " . $e->getMessage());
            redirect('index.php?module=menumanager&action=edit&id=' . $id . '&error=db_error&msg=' . urlencode($e->getMessage()));
        }
        exit;
    }

    /**
     * Menü öğesini siler.
     */
    public function delete()
    {
        $id = $_GET['id'] ?? 0;
        if (!$id) {
            redirect('index.php?module=menumanager&action=index&error=missing_id');
            exit;
        }
        
        // Bir menüyü silmeden önce varsa alt menülerini de silmek veya başka bir üst menüye atamak isteyebilirsiniz.
        // Foreign key ile CASCADE DELETE ayarlıysa otomatik silinir.
        // Eğer cascade delete yoksa ve alt menüler var ise hata alırsınız.
        // Güvenli bir yöntem: Silmeden önce alt menülerin parent_id'sini 0 veya null yapın.
        $this->db->getConnection()->prepare("UPDATE menus SET parent_id = 0 WHERE parent_id = ?")->execute([$id]);
        
        // menu_roles tablosundan da ilgili girişleri sil
        $this->db->getConnection()->prepare("DELETE FROM menu_roles WHERE menu_id = ?")->execute([$id]);

        $menu = $this->db->select("SELECT title FROM menus WHERE id = ?", [$id])[0] ?? null;
        $this->db->getConnection()->prepare("DELETE FROM menus WHERE id = ?")->execute([$id]);
        
        if ($menu) {
            log_activity('DELETE', 'MenuManager', $id, "Menü öğesini sildi: '{$menu['title']}'");
        }
        redirect('index.php?module=menumanager&action=index&status=deleted');
        exit;
    }

    /**
     * Modules klasöründeki modülleri tarar ve menüde eksik olanları ekler.
     */
    public function auto_add_modules() {
        $module_root_path = __DIR__ . '/../../modules/';
        $module_folders = array_filter(glob($module_root_path . '*'), 'is_dir');
        
        $existing_menu_urls = $this->db->select("SELECT url FROM menus");
        $existing_module_names_in_menu = [];
        foreach($existing_menu_urls as $menu_url){
            parse_str(parse_url($menu_url['url'], PHP_URL_QUERY), $params);
            if(isset($params['module'])){
                $existing_module_names_in_menu[] = $params['module'];
            }
        }
        $existing_module_names_in_menu = array_unique($existing_module_names_in_menu);

        $added_count = 0;
        foreach ($module_folders as $folder) {
            $module_name = basename($folder);
            if ($module_name === 'menumanager') continue; 

            if (!in_array($module_name, $existing_module_names_in_menu)) {
                $title = ucwords(str_replace('_', ' ', $module_name));
                $url = "index.php?module={$module_name}&action=index";

                // Ana modül menüsünü ekle
                $stmt = $this->db->getConnection()->prepare(
                    "INSERT INTO menus (parent_id, title, url, icon, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)"
                );
                $icon = 'fa-folder'; 
                // parent_id olarak 0 atıyoruz, çünkü ana menü olacak
                $stmt->execute([0, $title, $url, $icon, 99, 1]); 
                $parent_menu_id = $this->db->getConnection()->lastInsertId();
                $added_count++;
                log_activity('AUTO_ADD', 'MenuManager', $parent_menu_id, "Modülü menüye otomatik ekledi: '$title'");

                // Bu ana menüye admin rolünü ata
                $this->db->getConnection()->prepare("INSERT INTO menu_roles (menu_id, role) VALUES (?, 'admin')")->execute([$parent_menu_id]);
                
            }
        }
        redirect('index.php?module=menumanager&action=index&status=modules_added&count='.$added_count);
        exit;
    }
}