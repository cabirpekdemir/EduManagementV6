<?php
// Düzenleme sayfası için mevcut sınıf verisini, oluşturma sayfası için boş bir dizi ata
$class_data = $class ?? []; 
?>
<form action="<?= $formAction ?? '' ?>" method="post">
    <?php if (isset($class_data['id'])): ?>
        <input type="hidden" name="id" value="<?= $class_data['id'] ?>">
    <?php endif; ?>

    <div class="form-group">
        <label for="name">Sınıf Adı (Örn: 9-A)</label>
        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($class_data['name'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Açıklama</label>
        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($class_data['description'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label for="advisor_teacher_id">Danışman Öğretmen</label>
        <select class="form-control" id="advisor_teacher_id" name="advisor_teacher_id">
            <option value="">-- Danışman Seçilmedi --</option>
            <?php foreach ($teachers as $teacher): ?>
                <option value="<?= $teacher['id'] ?>" <?= (isset($class_data['advisor_teacher_id']) && $class_data['advisor_teacher_id'] == $teacher['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($teacher['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="submit" class="btn btn-primary"><?= isset($class_data['id']) ? 'Güncelle' : 'Oluştur' ?></button>
</form>