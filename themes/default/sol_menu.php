<?php
/**
 * sol_menu.php
 * - FA 4.7 ikon uyumu
 * - menu_roles tablosuna göre rol bazlı görünürlük
 * - Görünür alt menünün ebeveynini de otomatik ekler (taşıyıcı)
 * - Akordeon: aynı seviyede tek açık; durum localStorage ile saklanır
 */

if (session_status() === PHP_SESSION_NONE) @session_start();

function sm_h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/** FA 4.7 için ikon sınıfını normalize et */
function normalize_icon_fa47(?string $icon): string {
  $i = trim((string)$icon);
  if ($i === '' || $i === '#' || $i === '-') return 'fa fa-circle';
  // FA5/6 sınıf öneklerini FA4'e indir
  $i = preg_replace('/\b(fas|far|fal|fab)\s+/', 'fa ', $i);
  // Başta 'fa ' yoksa ekle
  if (strpos($i, 'fa ') !== 0) $i = 'fa ' . ltrim($i);
  // Çift boşlukları tekleyelim
  $i = preg_replace('/\s+/', ' ', $i);
  return $i;
}

try {
  // DB
  if (!class_exists('Database')) require_once __DIR__ . '/../core/database.php';
  $pdo = Database::getInstance()->getConnection();

  // Aktif oturum ve rol
  $roleName = $_SESSION['user']['role'] ?? $_SESSION['role'] ?? null;     // 'admin'|'teacher'|'student'|'parent'
  $roleId   = $_SESSION['user']['role_id'] ?? $_SESSION['role_id'] ?? null;

  // Misafir ise menüyü hiç göstermeyelim (istersen küçük bir giriş linki koyabilirsin)
  $isGuest = empty($_SESSION['user']['id']) && empty($_SESSION['user_id']) && !$roleName;
  if ($isGuest) { echo '<!-- sidebar hidden for guests -->'; return; }

  // 1) Rol filtreli menüleri çek
  // Kural:
  //  - Eğer bir menünün menu_roles’ta kaydı varsa, SADECE o roller görür.
  //  - Eğer hiç kaydı yoksa, HERKESE görünür (geriye dönük uyum).
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

  // 2) Görünür alt menüler varsa ebeveyn(ler) taşıyıcı olarak eklensin
  $byId = [];
  foreach ($rows as $r) { $byId[(int)$r['id']] = $r; }

  $missingParentIds = [];
  foreach ($rows as $r) {
    $pid = (int)$r['parent_id'];
    if ($pid !== 0 && !isset($byId[$pid])) $missingParentIds[$pid] = true;
  }
  if ($missingParentIds) {
    $ids = array_keys($missingParentIds);
    // Güvenli IN
    $in = implode(',', array_fill(0, count($ids), '?'));
    $st2 = $pdo->prepare("
      SELECT id, COALESCE(parent_id,0) AS parent_id, title, url, icon,
             COALESCE(display_order,0) AS display_order, COALESCE(is_active,1) AS is_active
      FROM menus
      WHERE id IN ($in)
      ORDER BY COALESCE(parent_id,0), COALESCE(display_order,0), id
    ");
    $st2->execute($ids);
    $parents = $st2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($parents as $p) {
      $byId[(int)$p['id']] = $p; // taşıyıcı olarak ekle (bu kayıtta rol kısıtı aranmaz)
    }
  }

  // 3) Ağaç kur
  // Önce index’li dizi
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
  // Hata durumunda fail-safe küçük bir menü göster
  echo '<ul class="sidebar-menu"><li><a href="index.php"><i class="fa fa-home"></i> <span>Ana Sayfa</span></a></li></ul>';
  return;
}

// === RENDER ===
?>
<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
  <?php
  // AdminLTE 3 için render fonksiyonu güncellendi
  $render = function ($items) use (&$render) {
    foreach ($items as $it) {
      $hasChildren = !empty($it['children']);
      // 'nav-icon' sınıfını ekliyoruz
      $icon = 'nav-icon ' . normalize_icon_fa47($it['icon'] ?? ''); 
      $url  = trim((string)($it['url'] ?? '#'));
      $title = sm_h($it['title'] ?? '');

      if ($hasChildren) {
        // AdminLTE 3: 'nav-item has-treeview'
        echo '<li class="nav-item has-treeview">';
        // AdminLTE 3: 'nav-link'
        echo '<a href="#" class="nav-link">';
        echo '<i class="'.sm_h($icon).'"></i>';
        // 'pull-right' yerine 'right' ve <p> etiketi
        echo '<p>' . $title . '<i class="right fa fa-angle-left"></i></p>'; 
        echo '</a>';
        // AdminLTE 3: 'nav nav-treeview'
        echo '<ul class="nav nav-treeview">';
        $render($it['children']);
        echo '</ul>';
        echo '</li>';
      } else {
        // AdminLTE 3: 'nav-item'
        echo '<li class="nav-item">';
        // AdminLTE 3: 'nav-link'
        echo '<a href="'.sm_h($url).'" class="nav-link">';
        // İkon için <p> etiketi
        echo '<i class="'.sm_h($icon).'"></i> <p>'.$title.'</p>';
        echo '</a></li>';
      }
    }
  };

  $render($tree);
  ?>
</ul>
