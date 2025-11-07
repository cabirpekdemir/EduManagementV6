<?php
// Beklenen değişkenler:
// $menu (veya null), $parent_options (id,title,depth), $exclude_ids (dizi), $all_roles, $formAction
$exclude_ids = $exclude_ids ?? [];
?>
<h2><?= !empty($menu) ? 'Menü Öğesini Düzenle' : 'Yeni Menü Öğesi Ekle' ?></h2>

<form method="post" action="<?= e($formAction) ?>">
  <?php if (!empty($menu)): ?>
    <input type="hidden" name="id" value="<?= e($menu['id']) ?>">
  <?php endif; ?>

  <div class="form-group">
    <label for="parent_id">Üst Menü (sınırsız seviye):</label>
    <select name="parent_id" id="parent_id" class="form-control" style="max-width:420px">
      <option value="">— Ana Menü —</option>
      <?php foreach ($parent_options as $opt):
        $id    = (int)$opt['id'];
        if (in_array($id, $exclude_ids, true)) continue; // kendisi ve altı listelenmesin (edit'te)
        $depth = (int)$opt['depth'];
        $pad   = str_repeat('— ', $depth);
        $sel   = (!empty($menu) && (int)($menu['parent_id'] ?? 0) === $id) ? 'selected' : '';
      ?>
        <option value="<?= e($id) ?>" <?= $sel ?>><?= e($pad.$opt['title']) ?></option>
      <?php endforeach; ?>
    </select>
    <small class="form-text text-muted">Alt/alt-alt/alt-alt-alt… oluşturmak için üst menüyü seçin.</small>
  </div>

  <div class="form-group">
    <label for="title">Başlık:</label>
    <input type="text" name="title" id="title" class="form-control" required
           value="<?= e($menu['title'] ?? '') ?>" style="max-width:420px">
  </div>

  <div class="form-group">
    <label for="url">URL (örn: <code>?module=students&action=index</code>):</label>
    <input type="text" name="url" id="url" class="form-control"
           value="<?= e($menu['url'] ?? '') ?>" placeholder="?module=...&action=..." style="max-width:520px">
  </div>

  <div class="form-group">
    <label for="icon">İkon (Font Awesome sınıfı, isteğe bağlı):</label>
    <input type="text" name="icon" id="icon" class="form-control"
           value="<?= e($menu['icon'] ?? '') ?>" placeholder="fa-users" style="max-width:320px">
  </div>

  <div class="form-group">
    <label for="display_order">Görüntülenme Sırası:</label>
    <input type="number" name="display_order" id="display_order" class="form-control"
           value="<?= e($menu['display_order'] ?? 0) ?>" style="max-width:140px">
  </div>

  <div class="form-group">
    <label>Görünecek Roller:</label><br>
    <?php $assigned = $assigned_roles ?? []; ?>
    <?php foreach ($all_roles as $r): ?>
      <label class="mr-3">
        <input type="checkbox" name="roles[]" value="<?= e($r) ?>" <?= in_array($r, $assigned, true) ? 'checked' : '' ?>>
        <?= e(ucfirst($r)) ?>
      </label>
    <?php endforeach; ?>
  </div>

  <div class="form-group form-check">
    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
           <?= (int)($menu['is_active'] ?? 1) === 1 ? 'checked' : '' ?>>
    <label class="form-check-label" for="is_active">Aktif mi?</label>
  </div>

  <button type="submit" class="btn btn-primary"><?= !empty($menu) ? 'Güncelle' : 'Oluştur' ?></button>
  <a href="index.php?module=menumanager&action=index" class="btn btn-link">Vazgeç</a>
</form>
