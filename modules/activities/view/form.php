<?php
// modules/activities/view/form.php
if (!function_exists('h')) { 
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); } 
}

$isEdit = (bool)($isEdit ?? false);
$activity = $activity ?? [];
$teachers = $teachers ?? [];
$classes = $classes ?? [];
$selected = $selected_class_ids ?? [];
$csrf_token = $csrf_token ?? '';

// Flash mesajları
if (isset($_SESSION['form_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= h($_SESSION['form_error']) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php unset($_SESSION['form_error']); endif;

if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)($v??''), ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('e')) {
  function e($v){ return htmlspecialchars((string)($v??''), ENT_QUOTES, 'UTF-8'); }
}
?>

<div class="row g-3">
  <div class="col-md-12">
    <label class="form-label">Başlık <span class="text-danger">*</span></label>
    <input type="text" 
           name="title" 
           class="form-control" 
           required
           maxlength="255"
           value="<?= h($activity['title'] ?? '') ?>"
           placeholder="Etkinlik başlığını girin">
  </div>

  <div class="col-md-6">
    <label class="form-label">Sorumlu Öğretmen</label>
    <select name="teacher_id" class="form-select">
      <option value="">— Seçiniz —</option>
      <?php foreach ($teachers as $t):
        $sel = ((string)($activity['teacher_id'] ?? '') === (string)$t['id']) ? 'selected' : ''; ?>
        <option value="<?= (int)$t['id'] ?>" <?= $sel ?>><?= h($t['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-6">
    <label class="form-label">Konum</label>
    <input type="text" 
           name="location" 
           class="form-control"
           maxlength="255"
           value="<?= h($activity['location'] ?? '') ?>"
           placeholder="Örn: Konferans Salonu">
  </div>

  <div class="col-md-6">
    <label class="form-label">Başlangıç Tarihi</label>
    <input type="datetime-local" 
           name="start_date" 
           class="form-control"
           value="<?= h(str_replace(' ', 'T', (string)($activity['start_date'] ?? ''))) ?>">
  </div>
  
  <div class="col-md-6">
    <label class="form-label">Bitiş Tarihi</label>
    <input type="datetime-local" 
           name="end_date" 
           class="form-control"
           value="<?= h(str_replace(' ', 'T', (string)($activity['end_date'] ?? ''))) ?>">
  </div>

  <div class="col-md-12">
    <label class="form-label">Sınıflar</label>
    <div class="border rounded p-3 bg-light">
      <?php if (empty($classes)): ?>
        <span class="text-muted">Kayıtlı sınıf bulunamadı.</span>
      <?php else: ?>
        <div class="row">
          <?php foreach ($classes as $cl):
            $checked = in_array((int)$cl['id'], $selected, true) ? 'checked' : ''; ?>
            <div class="col-md-4 col-sm-6 mb-2">
              <div class="form-check">
                <input type="checkbox" 
                       class="form-check-input" 
                       name="class_ids[]" 
                       id="class_<?= (int)$cl['id'] ?>"
                       value="<?= (int)$cl['id'] ?>" 
                       <?= $checked ?>>
                <label class="form-check-label" for="class_<?= (int)$cl['id'] ?>">
                  <?= h($cl['name']) ?>
                </label>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-md-12">
    <label class="form-label">Etkinlik Görseli (Afiş)</label>
    <small class="form-text text-muted d-block mb-2">
      Maksimum 5MB. JPG, PNG, GIF, WEBP
    </small>
    
    <?php if ($isEdit && !empty($activity['image_path'])): ?>
      <div class="mb-2">
        <img src="<?= h(BASE_URL . $activity['image_path']) ?>" 
             class="img-fluid rounded border" 
             alt="Mevcut Görsel"
             style="max-height: 200px; object-fit: cover;">
        <div class="form-check mt-2">
          <input type="checkbox" 
                 class="form-check-input" 
                 name="delete_image" 
                 value="1"
                 id="delete_image">
          <label class="form-check-label text-danger" for="delete_image">
            Bu görseli sil
          </label>
        </div>
      </div>
      <label class="form-label">Yeni Görsel Yükle</label>
    <?php endif; ?>
    
    <input type="file" 
           name="image_path" 
           class="form-control" 
           accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
  </div>

  <div class="col-12">
    <label class="form-label">Açıklama</label>
    <textarea name="description" 
              class="form-control" 
              rows="5"
              placeholder="Etkinlik detaylarını buraya yazın..."><?= h($activity['description'] ?? '') ?></textarea>
  </div>
</div>