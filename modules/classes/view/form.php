<?php
if (!function_exists('h')) { 
    function h($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); } 
}

// Old input ve hatalar
$oldInput = $_SESSION['old_input'] ?? [];
$errors = $_SESSION['validation_errors'] ?? ($errors ?? []);
unset($_SESSION['old_input'], $_SESSION['validation_errors']);

$isEdit = $isEdit ?? false;
$formAction = $formAction ?? 'index.php?module=classes&action=store';
$class = $class ?? [];
$teachers = $teachers ?? [];
$selectedAdvisor = $selectedAdvisor ?? ($class['advisor_teacher_id'] ?? null);

// Merge data
$c = array_merge([
    'name' => '',
    'description' => '',
    'advisor_teacher_id' => null
], is_array($class) ? $class : [], $oldInput);
?>

<div class="mb-3">
    <a href="index.php?module=classes&action=list" class="btn btn-outline-secondary btn-sm">
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
    
    <!-- Ana Bilgiler -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><?= $isEdit ? 'Sınıfı Düzenle' : 'Yeni Sınıf' ?></h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Sınıf Adı <span class="text-danger">*</span></label>
                    <input type="text" name="name" 
                           class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                           required value="<?= h($c['name']) ?>" placeholder="Örn: 9-A, 10/B">
                    <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback d-block"><?= h($errors['name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Danışman Öğretmen</label>
                    <select name="advisor_teacher_id" class="form-select">
                        <option value="">— Seçilmedi —</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?= (int)$teacher['id'] ?>" 
                                    <?= ((int)$selectedAdvisor === (int)$teacher['id']) ? 'selected' : '' ?>>
                                <?= h($teacher['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Açıklama</label>
                    <textarea name="description" class="form-control" rows="3" 
                              placeholder="Sınıf hakkında not ekleyebilirsiniz..."><?= h($c['description']) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Buttons -->
    <div class="d-flex gap-2 mb-4">
        <a href="index.php?module=classes&action=list" class="btn btn-outline-secondary">İptal</a>
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Kaydet
        </button>
    </div>
</form>