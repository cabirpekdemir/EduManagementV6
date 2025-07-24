<?php
require_once __DIR__ . '/core/database.php';
$db = Database::getInstance()->getConnection();

$modulesPath = __DIR__ . '/modules/';
$modules = array_filter(glob($modulesPath . '*'), 'is_dir');

foreach ($modules as $modulePath) {
    $module = basename($modulePath);
    $url = "?module=$module&action=index";

    // 🔒 Daha önce eklenmiş mi?
    $stmt = $db->prepare("SELECT COUNT(*) FROM menus WHERE url = ?");
    $stmt->execute([$url]);
    $exists = $stmt->fetchColumn();

    if ($exists == 0) {
        // ⛳ Başlık ilk harf büyük
        $title = ucfirst($module);

        $stmt = $db->prepare("INSERT INTO menus (title, url, role, display_order, is_active) VALUES (?, ?, 'admin', 99, 1)");
        $stmt->execute([$title, $url]);

        echo "✅ Eklendi: $module\n";
    } else {
        echo "⏩ Zaten var: $module\n";
    }
}
