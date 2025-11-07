<?php
// views/partials/action_buttons.php
// Tüm listelerde tek tip "Gör / Düzenle / Sil" butonları

if (!function_exists('ab_h')) {
  function ab_h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

/**
 * render_action_buttons([
 *   'view'   => 'index.php?module=...&action=show&id=1',
 *   'edit'   => 'index.php?module=...&action=edit&id=1',   // optional
 *   'delete' => 'index.php?module=...&action=destroy&id=1' // optional (POST)
 * ]);
 */
if (!function_exists('render_action_buttons')) {
  function render_action_buttons(array $urls, array $labels = null, string $size = 'sm'): void {
    $labels = $labels ?? ['view' => 'Gör', 'edit' => 'Düzenle', 'delete' => 'Sil'];
    $btnClass = 'btn btn-'.$size;

    echo '<div class="btn-group btn-group-'.$size.'" role="group">';

    if (!empty($urls['view'])) {
      echo '<a href="'.ab_h($urls['view']).'" class="'.$btnClass.' btn-info">';
      echo '<span class="me-1" aria-hidden="true">👁️</span>'.ab_h($labels['view']).'</a>';
    }

    if (!empty($urls['edit'])) {
      echo '<a href="'.ab_h($urls['edit']).'" class="'.$btnClass.' btn-warning">';
      echo '<span class="me-1" aria-hidden="true">✏️</span>'.ab_h($labels['edit']).'</a>';
    }

    if (!empty($urls['delete'])) {
      echo '<form action="'.ab_h($urls['delete']).'" method="post" class="d-inline"';
      echo ' onsubmit="return confirm(\'Bu kaydı silmek istediğinize emin misiniz?\');">';
      echo '<button type="submit" class="'.$btnClass.' btn-danger">';
      echo '<span class="me-1" aria-hidden="true">🗑️</span>'.ab_h($labels['delete']).'</button>';
      echo '</form>';
    }

    echo '</div>';
  }
}
