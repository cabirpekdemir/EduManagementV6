<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class Activity_categoriesController // Sınıf adını dosya adına uygun hale getirdim
{
    protected $db;
    protected $currentUser;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->currentUser = $_SESSION['user'] ?? null;

        // Bu modüle sadece adminler erişebilir
        if (($this->currentUser['role'] ?? 'guest') !== 'admin') {
            log_activity('ACCESS_DENIED', 'ActivityCategories', null, 'Yetkisiz erişim denemesi.');
            die("⛔ Bu modüle erişim yetkiniz yok!");
        }
    }

    /**
     * Etkinlik kategorilerini listeler.
     */
    public function index()
    {
        $categories = $this->db->select("SELECT * FROM activity_categories ORDER BY name ASC");
        return ['categories' => $categories];
    }

    /**
     * Yeni kategori oluşturma formunu gösterir.
     */
    public function create()
    {
        return [
            'category' => null, 
            'isEdit' => false,
            'formAction' => 'index.php?module=activity_categories&action=store'
        ];
    }

    /**
     * Yeni bir kategoriyi kaydeder.
     */
    public function store()
    {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';

        if (empty($name)) {
            redirect('index.php?module=activity_categories&action=create&error=empty_name');
            exit;
        }

        // Kategori adının benzersiz olup olmadığını kontrol et
        $existing = $this->db->select("SELECT id FROM activity_categories WHERE name = ?", [$name]);
        if (!empty($existing)) {
            redirect('index.php?module=activity_categories&action=create&error=name_exists');
            exit;
        }

        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO activity_categories (name, description) VALUES (?, ?)"
        );
        $stmt->execute([$name, $description]);
        $category_id = $this->db->getConnection()->lastInsertId();

        log_activity('CREATE', 'ActivityCategories', $category_id, "Etkinlik kategorisi oluşturdu: '$name'");
        redirect('index.php?module=activity_categories&action=index&status=created');
    }

    /**
     * Mevcut bir kategoriyi düzenleme formunu gösterir.
     */
    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $category = $this->db->select("SELECT * FROM activity_categories WHERE id = ?", [$id])[0] ?? null;

        if (!$category) {
            redirect('index.php?module=activity_categories&action=index&error=not_found');
            exit;
        }

        return [
            'category' => $category,
            'isEdit' => true,
            'formAction' => 'index.php?module=activity_categories&action=update&id=' . $id
        ];
    }

    /**
     * Mevcut bir kategoriyi günceller.
     */
    public function update()
    {
        $id = $_POST['id'] ?? ($_GET['id'] ?? 0);
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';

        if (empty($name) || !$id) {
            redirect('index.php?module=activity_categories&action=edit&id=' . $id . '&error=empty_name');
            exit;
        }

        // Kategori adının benzersiz olup olmadığını kontrol et (kendisi hariç)
        $existing = $this->db->select("SELECT id FROM activity_categories WHERE name = ? AND id != ?", [$name, $id]);
        if (!empty($existing)) {
            redirect('index.php?module=activity_categories&action=edit&id=' . $id . '&error=name_exists');
            exit;
        }

        $stmt = $this->db->getConnection()->prepare(
            "UPDATE activity_categories SET name = ?, description = ? WHERE id = ?"
        );
        $stmt->execute([$name, $description, $id]);
        
        log_activity('UPDATE', 'ActivityCategories', $id, "Etkinlik kategorisini güncelledi: '$name'");
        redirect('index.php?module=activity_categories&action=index&status=updated');
    }

    /**
     * Bir kategoriyi siler.
     * Not: Bu kategoriye bağlı etkinliklerin category_id'si ON DELETE SET NULL ile NULL olacaktır.
     */
    public function delete()
    {
        $id = $_GET['id'] ?? 0;
        if (!$id) {
            redirect('index.php?module=activity_categories&action=index&error=missing_id');
            exit;
        }

        // Kategoriye bağlı etkinlik olup olmadığını kontrol etmek isteyebilirsiniz.
        // $activityCount = $this->db->select("SELECT COUNT(*) as count FROM activities WHERE category_id = ?", [$id])[0]['count'];
        // if ($activityCount > 0) {
        //    redirect('index.php?module=activity_categories&action=index&error=category_in_use&id='.$id);
        //    exit;
        // }

        $category = $this->db->select("SELECT name FROM activity_categories WHERE id = ?", [$id])[0] ?? null;
        
        $this->db->getConnection()->prepare("DELETE FROM activity_categories WHERE id = ?")->execute([$id]);
        
        if ($category) {
            log_activity('DELETE', 'ActivityCategories', $id, "Etkinlik kategorisini sildi: '{$category['name']}'");
        }
        redirect('index.php?module=activity_categories&action=index&status=deleted');
    }
}