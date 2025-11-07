<?php
if (!function_exists('h')) { 
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); } 
}

$oldInput = $_SESSION['old_input'] ?? [];
$errors = $_SESSION['validation_errors'] ?? ($errors ?? []);
unset($_SESSION['old_input'], $_SESSION['validation_errors']);

$isEdit = $isEdit ?? false;
$formAction = $formAction ?? 'index.php?module=rules&action=store';
$rule = $rule ?? [];
$categories = $categories ?? [];
$ruleTypes = $ruleTypes ?? [];
$gradeRanges = $gradeRanges ?? [];

$r = array_merge([
    'name' => '',
    'code' => '',
    'category' => '',
    'grade_range' => '',
    'rule_type' => '',
    'description' => '',
    'conditions' => '',
    'priority' => 0,
    'is_active' => 1
], is_array($rule) ? $rule : [], $oldInput);
?>

<div class="mb-3">
    <a href="index.php?module=rules&action=list" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left"></i> Listeye Dön
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <h5 class="alert-heading">Lütfen aşağıdaki hataları düzeltin:</h5>
        <ul class="mb-0">
            <?php foreach ($errors as $field => $error): ?>
                <li><?= h($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form action="<?= h($formAction) ?>" method="post" novalidate>
    
    <!-- Temel Bilgiler -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><?= $isEdit ? 'Kuralı Düzenle' : 'Yeni Kural' ?></h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Kural Adı <span class="text-danger">*</span></label>
                    <input type="text" name="name" 
                           class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                           required value="<?= h($r['name']) ?>" 
                           placeholder="Örn: İlkokul Ders Tekrarı">
                    <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback d-block"><?= h($errors['name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Kural Kodu <span class="text-danger">*</span></label>
                    <input type="text" name="code" 
                           class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>" 
                           required value="<?= h($r['code']) ?>" 
                           placeholder="ELEM_REPEAT" style="text-transform: uppercase;">
                    <small class="form-text text-muted">Büyük harfle, alt çizgi ile</small>
                    <?php if (isset($errors['code'])): ?>
                        <div class="invalid-feedback d-block"><?= h($errors['code']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select name="category" class="form-select <?= isset($errors['category']) ? 'is-invalid' : '' ?>" required>
                        <option value="">— Seçiniz —</option>
                        <?php foreach ($categories as $key => $name): ?>
                            <option value="<?= h($key) ?>" <?= $r['category'] === $key ? 'selected' : '' ?>>
                                <?= h($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['category'])): ?>
                        <div class="invalid-feedback d-block"><?= h($errors['category']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Sınıf Aralığı</label>
                    <select name="grade_range" class="form-select">
                        <option value="">— Tümü —</option>
                        <?php foreach ($gradeRanges as $key => $name): ?>
                            <option value="<?= h($key) ?>" <?= $r['grade_range'] === $key ? 'selected' : '' ?>>
                                <?= h($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Kural Tipi <span class="text-danger">*</span></label>
                    <select name="rule_type" class="form-select <?= isset($errors['rule_type']) ? 'is-invalid' : '' ?>" required>
                        <option value="">— Seçiniz —</option>
                        <?php foreach ($ruleTypes as $key => $name): ?>
                            <option value="<?= h($key) ?>" <?= $r['rule_type'] === $key ? 'selected' : '' ?>>
                                <?= h($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['rule_type'])): ?>
                        <div class="invalid-feedback d-block"><?= h($errors['rule_type']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-12">
                    <label class="form-label">Açıklama</label>
                    <textarea name="description" class="form-control" rows="3" 
                              placeholder="Kural hakkında detaylı açıklama..."><?= h($r['description']) ?></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Koşullar (JSON)</label>
                    <textarea name="conditions" class="form-control <?= isset($errors['conditions']) ? 'is-invalid' : '' ?>" 
                              rows="5" 
                              placeholder='{"max_enrollment": 1, "applies_to": ["ilkokul", "ortaokul"]}'><?= h($r['conditions']) ?></textarea>
                    <small class="form-text text-muted">
                        Kural detaylarını JSON formatında girin. Örn: {"max_enrollment": 1}
                    </small>
                    <?php if (isset($errors['conditions'])): ?>
                        <div class="invalid-feedback d-block"><?= h($errors['conditions']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Öncelik</label>
                    <input type="number" name="priority" class="form-control" 
                           value="<?= (int)$r['priority'] ?>" min="0" max="100">
                    <small class="form-text text-muted">Yüksek öncelikli kurallar önce uygulanır</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Durum</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" 
                               id="is_active" value="1" 
                               <?= !empty($r['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">
                            Aktif (İşaretlenirse kural uygulanır)
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Buttons -->
    <div class="d-flex gap-2 mb-4">
        <a href="index.php?module=rules&action=list" class="btn btn-outline-secondary">İptal</a>
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Kaydet
        </button>
    </div>
</form>

<script>
// Kod alanını otomatik büyük harfe çevir
document.querySelector('input[name="code"]').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9_]/g, '');
});

// JSON validasyonu (opsiyonel)
document.querySelector('form').addEventListener('submit', function(e) {
    const conditionsField = document.querySelector('textarea[name="conditions"]');
    const conditions = conditionsField.value.trim();
    
    if (conditions) {
        try {
            JSON.parse(conditions);
            conditionsField.classList.remove('is-invalid');
        } catch (err) {
            e.preventDefault();
            conditionsField.classList.add('is-invalid');
            alert('Koşullar geçerli JSON formatında değil!');
        }
    }
});
</script>