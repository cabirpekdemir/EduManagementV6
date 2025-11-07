<?php
/**
 * sol_menu.php - MODERN VERSİYON
 * - Modern ikon seti
 * - Gradient hover efektleri
 * - Smooth animasyonlar
 */

if (session_status() === PHP_SESSION_NONE) @session_start();

function sm_h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function normalize_icon_fa47(?string $icon): string {
    $i = trim((string)$icon);
    if ($i === '' || $i === '#' || $i === '-') return 'fa fa-circle-o';
    $i = preg_replace('/\b(fas|far|fal|fab)\s+/', 'fa ', $i);
    if (strpos($i, 'fa ') !== 0) $i = 'fa ' . ltrim($i);
    $i = preg_replace('/\s+/', ' ', $i);
    return $i;
}

try {
    if (!class_exists('Database')) require_once __DIR__ . '/../core/database.php';
    $pdo = Database::getInstance()->getConnection();

    $roleName = $_SESSION['user']['role'] ?? $_SESSION['role'] ?? null;
    $roleId   = $_SESSION['user']['role_id'] ?? $_SESSION['role_id'] ?? null;

    $isGuest = empty($_SESSION['user']['id']) && empty($_SESSION['user_id']) && !$roleName;
    if ($isGuest) { 
        echo '<!-- sidebar hidden for guests -->'; 
        return; 
    }

    $params = [];
    $sql = "
        SELECT m.id, COALESCE(m.parent_id,0) AS parent_id, m.title, m.url, m.icon,
               COALESCE(m.display_order,0) AS display_order, COALESCE(m.is_active,1) AS is_active
        FROM menus m
        WHERE COALESCE(m.is_active,1) = 1
          AND (
            NOT EXISTS (SELECT 1 FROM menu_roles r WHERE r.menu_id = m.id)
            OR EXISTS (SELECT 1 FROM menu_roles r2 WHERE r2.menu_id = m.id AND r2.role = :roleName)
          )
        ORDER BY COALESCE(m.parent_id,0), COALESCE(m.display_order,0), m.id
    ";
    $params[':roleName'] = (string)$roleName;
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    // Eksik parent'ları ekle
    $byId = [];
    foreach ($rows as $r) { $byId[(int)$r['id']] = $r; }

    $missingParentIds = [];
    foreach ($rows as $r) {
        $pid = (int)$r['parent_id'];
        if ($pid !== 0 && !isset($byId[$pid])) $missingParentIds[$pid] = true;
    }
    
    if ($missingParentIds) {
        $ids = array_keys($missingParentIds);
        $in = implode(',', array_fill(0, count($ids), '?'));
        $st2 = $pdo->prepare("
            SELECT id, COALESCE(parent_id,0) AS parent_id, title, url, icon,
                   COALESCE(display_order,0) AS display_order
            FROM menus WHERE id IN ($in)
        ");
        $st2->execute($ids);
        $parents = $st2->fetchAll(PDO::FETCH_ASSOC);
        foreach ($parents as $p) {
            $byId[(int)$p['id']] = $p;
        }
    }

    // Ağaç oluştur
    $nodes = array_values($byId);
    usort($nodes, function($a,$b){
        $c = ($a['parent_id'] <=> $b['parent_id']);
        if ($c !== 0) return $c;
        $d = ($a['display_order'] <=> $b['display_order']);
        if ($d !== 0) return $d;
        return ($a['id'] <=> $b['id']);
    });

    $map = [];
    foreach ($nodes as $n) {
        $n['children'] = [];
        $map[(int)$n['id']] = $n;
    }
    
    foreach ($map as $id => $n) {
        $pid = (int)$n['parent_id'];
        if ($pid !== 0 && isset($map[$pid])) {
            $map[$pid]['children'][] = &$map[$id];
        }
    }
    
    $tree = [];
    foreach ($map as $id => $n) {
        $pid = (int)$n['parent_id'];
        if ($pid === 0 || !isset($map[$pid])) $tree[] = $n;
    }

} catch (\Throwable $e) {
    echo '<ul class="nav nav-pills nav-sidebar flex-column">
            <li class="nav-item">
                <a href="index.php" class="nav-link">
                    <i class="fa fa-home"></i>
                    <p>Ana Sayfa</p>
                </a>
            </li>
          </ul>';
    return;
}

// === MODERN RENDER ===
?>
<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
    <?php
    $render = function ($items, $level = 0) use (&$render) {
        foreach ($items as $it) {
            $hasChildren = !empty($it['children']);
            $icon = normalize_icon_fa47($it['icon'] ?? '');
            $url  = trim((string)($it['url'] ?? '#'));
            $title = sm_h($it['title'] ?? '');
            $menuId = (int)$it['id'];

            // Aktif menü kontrolü
            $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
            $isActive = ($url !== '#' && strpos($currentUrl, $url) !== false) ? 'active' : '';

            if ($hasChildren) {
                echo '<li class="nav-item has-treeview" data-menu-id="'.$menuId.'">';
                echo '<a href="#" class="nav-link '.$isActive.'">';
                echo '<i class="nav-icon '.sm_h($icon).'"></i>';
                echo '<p>'.$title.' <i class="right fa fa-angle-left"></i></p>';
                echo '</a>';
                echo '<ul class="nav nav-treeview">';
                $render($it['children'], $level + 1);
                echo '</ul>';
                echo '</li>';
            } else {
                echo '<li class="nav-item">';
                echo '<a href="'.sm_h($url).'" class="nav-link '.$isActive.'">';
                echo '<i class="nav-icon '.sm_h($icon).'"></i>';
                echo '<p>'.$title.'</p>';
                echo '</a>';
                echo '</li>';
            }
        }
    };

    $render($tree);
    ?>
</ul>

<style>
/* Modern Menü Stilleri */
.nav-sidebar .nav-link {
    border-radius: 8px;
    margin: 4px 8px;
    transition: all 0.3s ease;
}

.nav-sidebar .nav-link:hover {
    background: rgba(255, 255, 255, 0.1) !important;
    transform: translateX(5px);
}

.nav-sidebar .nav-link.active {
    background: rgba(255, 255, 255, 0.15) !important;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.nav-sidebar .nav-icon {
    margin-right: 0.75rem;
    width: 20px;
    text-align: center;
}

/* Treeview animasyonu */
.nav-treeview {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
// Modern Accordion
$(function() {
    // Treeview toggle
    $('[data-widget="treeview"]').each(function() {
        $(this).on('click', '.has-treeview > a', function(e) {
            e.preventDefault();
            
            const $parent = $(this).parent();
            const $siblings = $parent.siblings('.has-treeview');
            
            // Kardeşleri kapat
            $siblings.removeClass('menu-open');
            $siblings.find('> .nav-treeview').slideUp(300);
            
            // Kendini aç/kapat
            $parent.toggleClass('menu-open');
            $parent.find('> .nav-treeview').slideToggle(300);
        });
    });
});
</script>