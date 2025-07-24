<?php
if (file_exists(__DIR__ . '/../../core/database.php')) {
    require_once __DIR__ . '/../../core/database.php';
}
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    $db = Database::getInstance();
    $role = $_SESSION['user']['role'] ?? 'guest';
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';

    $sql = "SELECT m.id, m.parent_id, m.title, m.url, m.icon FROM menus m INNER JOIN menu_roles mr ON m.id = mr.menu_id WHERE m.is_active = 1 AND LOWER(mr.role) = LOWER(?) ORDER BY m.parent_id, m.display_order ASC";
    $all_items = $db->select($sql, [$role]);

    $menu_tree = [];
    if (!empty($all_items)) {
        $items_by_id = [];
        foreach ($all_items as $item) {
            $items_by_id[$item['id']] = $item;
            $items_by_id[$item['id']]['children'] = [];
        }
        foreach ($items_by_id as $id => &$item) {
            if ($item['parent_id'] != 0 && isset($items_by_id[$item['parent_id']])) {
                $items_by_id[$item['parent_id']]['children'][] = &$item;
            }
        }
        unset($item);
        foreach ($items_by_id as $id => $item) {
            if ($item['parent_id'] == 0) {
                $menu_tree[] = $item;
            }
        }
    }

    if (!function_exists('draw_menu_items_final')) {
        function draw_menu_items_final($items, $current_uri) {
            $is_any_child_in_this_branch_active = false;
            foreach ($items as $item) {
                $has_children = !empty($item['children']);
                
                parse_str(parse_url($current_uri, PHP_URL_QUERY) ?? '', $current_query_params);
                parse_str(parse_url($item['url'], PHP_URL_QUERY) ?? '', $item_query_params);
                $is_current_item_active = (($current_query_params['module'] ?? '') === ($item_query_params['module'] ?? null));

                $children_html = '';
                $is_a_child_active = false;
                if ($has_children) {
                    ob_start();
                    $is_a_child_active = draw_menu_items_final($item['children'], $current_uri);
                    $children_html = ob_get_clean();
                }

                $is_active = $is_current_item_active || $is_a_child_active;
                $li_class = 'nav-item';
                if ($has_children) $li_class .= ' has-treeview';
                if ($is_active && $has_children) $li_class .= ' menu-open';
                
                $a_class = 'nav-link';
                if ($is_active) $a_class .= ' active';
                
                echo "<li class='" . $li_class . "'>";
                echo "<a href='" . htmlspecialchars($item['url']) . "' class='" . $a_class . "'>";
                if (!empty($item['icon'])) {
                    echo "<i class='nav-icon fa " . htmlspecialchars($item['icon']) . "'></i> ";
                }
                echo "<p>" . htmlspecialchars($item['title']);
                if ($has_children) {
                    echo "<i class='right fa fa-angle-left'></i>";
                }
                echo "</p></a>";
                if ($has_children) {
                    echo "<ul class='nav nav-treeview'>" . $children_html . "</ul>";
                }
                echo "</li>";

                if ($is_active) $is_any_child_in_this_branch_active = true;
            }
            return $is_any_child_in_this_branch_active;
        }
    }

    if (!empty($menu_tree)) {
        draw_menu_items_final($menu_tree, $request_uri);
    } elseif (isset($_SESSION['user'])) {
        echo "<li class='nav-item'><a href='#' class='nav-link'><p>Bu rol için menü bulunamadı.</p></a></li>";
    }
} catch (Exception $e) {
    error_log("Menu Error: " . $e->getMessage());
    echo "<li class='nav-item'><a href='#' class='nav-link text-danger'><p>Menü yüklenemedi!</p></a></li>";
}
?>
