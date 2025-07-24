<?php
require_once __DIR__ . '/../../core/database.php';

class Erased_usersController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Listele
    public function index()
    {
        $erased_users = $this->db->select("SELECT * FROM erased_users ORDER BY erased_at DESC");
        return ['erased_users' => $erased_users];
    }

    // Detay göster (opsiyonel)
    public function show()
    {
        $id = $_GET['id'] ?? 0;
        $user = $this->db->select("SELECT * FROM erased_users WHERE id=?", [$id])[0] ?? null;
        return ['user' => $user];
    }

    // Geri yükle (restore)
    public function restore()
    {
        $id = $_GET['id'] ?? 0;
        $user = $this->db->select("SELECT * FROM erased_users WHERE id=?", [$id])[0] ?? null;

        if ($user) {
            $password = $user['password'] ?? password_hash('123456', PASSWORD_DEFAULT);
            $created_at = $user['created_at'] ?? date('Y-m-d H:i:s');
            $email = $user['email'] ?? '';
            $tc_kimlik = $user['tc_kimlik'] ?? '';

            $exists = $this->db->select("SELECT COUNT(*) AS c FROM users WHERE email=? OR tc_kimlik=?", [$email, $tc_kimlik]);
            if (!empty($exists[0]['c']) && $exists[0]['c'] > 0) {
                $_SESSION['error'] = "Bu e-posta veya TC kimlik zaten mevcut. Kayıt eklenmedi.";
                header('Location: index.php?module=erased_users&action=index');
                exit;
            }

            $this->db->getConnection()->prepare(
                "INSERT INTO users (name, email, password, role, okul, sinif, tc_kimlik, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            )->execute([
                $user['name'] ?? '',
                $email,
                $password,
                $user['role'] ?? '',
                $user['okul'] ?? '',
                $user['sinif'] ?? '',
                $tc_kimlik,
                $created_at
            ]);

            $this->db->getConnection()->prepare("DELETE FROM erased_users WHERE id=?")->execute([$id]);
            $_SESSION['success'] = "Kullanıcı başarıyla geri yüklendi.";
        } else {
            $_SESSION['error'] = "Kullanıcı bulunamadı!";
        }

        header('Location: index.php?module=erased_users&action=index');
        exit;
    }

    // Tamamen sil (permanent delete)
    public function delete()
    {
        $id = $_GET['id'] ?? 0;
        $this->db->getConnection()->prepare("DELETE FROM erased_users WHERE id=?")->execute([$id]);
        $_SESSION['success'] = "Kullanıcı tamamen silindi.";
        header('Location: index.php?module=erased_users&action=index');
        exit;
    }
}
