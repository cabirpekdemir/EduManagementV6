<?php
class ModulemanagerController {
  public function index() {
    if (!isset($_SESSION)) session_start();
    require_once __DIR__ . '/../../core/database.php';
    $db = Database::getInstance()->getConnection();

    // Aktiflik güncelleme
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_module'])) {
      $modId = intval($_POST['mod_id']);
      $current = intval($_POST['current_status']);
      $newStatus = $current ? 0 : 1;
      $stmt = $db->prepare("UPDATE modules_config SET is_active = ? WHERE id = ?");
      $stmt->execute([$newStatus, $modId]);
    }

    // Modül tarama
    $modulesList = $db->query("SELECT * FROM modules_config ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $added = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scan_modules'])) {
      $modulesPath = __DIR__ . '/../';
      $folders = array_filter(glob($modulesPath . '/*'), 'is_dir');
      
      $existingMenus = $db->query("SELECT url FROM menus")->fetchAll(PDO::FETCH_COLUMN);
      $existingConfigs = $db->query("SELECT module_name FROM modules_config")->fetchAll(PDO::FETCH_COLUMN);

      foreach ($folders as $folderPath) {
        $folderName = basename($folderPath);
        $fullUrl = "?module=$folderName&action=index";

        if (!in_array($fullUrl, $existingMenus)) {
          $title = ucwords(str_replace('_', ' ', $folderName));
          $stmt = $db->prepare("INSERT INTO menus (title, url, display_order, is_active) VALUES (?, ?, 99, 1)");
          $stmt->execute([$title, $fullUrl]);
        }

        if (!in_array($folderName, $existingConfigs)) {
          $label = ucwords(str_replace('_', ' ', $folderName));
          $stmt = $db->prepare("INSERT INTO modules_config (module_name, label, is_active) VALUES (?, ?, 1)");
          $stmt->execute([$folderName, $label]);
          $added[] = $folderName;
        }
      }

      $modulesList = $db->query("SELECT * FROM modules_config ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    ob_start();
    include 'modules/modulemanager/index.php';
    $pageContent = ob_get_clean();
    include 'themes/default/layout.php';
  }
}
