<h2><?= $isEdit ? "Etkinliği Düzenle" : "Yeni Etkinlik Oluştur" ?></h2>

<?php if (isset($_GET['error_message'])): ?>
    <p style="color: red; border:1px solid red; padding:10px; margin-bottom:15px;">
        <?= htmlspecialchars($_GET['error_message']) ?>
    </p>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars($formAction) ?>" enctype="multipart/form-data">
    <?php if ($isEdit && isset($activity['id'])): ?>
        <input type="hidden" name="id" value="<?= e($activity['id']) ?>">
    <?php endif; ?>

    <label for="title">Başlık:</label><br>
    <input type="text" id="title" name="title" value="<?= e($activity['title'] ?? ($_POST['title'] ?? '')) ?>" required><br><br>

    <label for="description">Açıklama:</label><br>
    <textarea id="description" name="description" rows="5" cols="50"><?= e($activity['description'] ?? ($_POST['description'] ?? '')) ?></textarea><br><br>

    <label for="activity_date">Tarih ve Saat:</label><br>
    <input type="datetime-local" id="activity_date" name="activity_date" value="<?= e(date('Y-m-d\TH:i', strtotime($activity['activity_date'] ?? ($_POST['activity_date'] ?? date('Y-m-d H:i'))))) ?>" required><br><br>

    <label for="location">Yer:</label><br>
    <input type="text" id="location" name="location" value="<?= e($activity['location'] ?? ($_POST['location'] ?? '')) ?>"><br><br>

    <label for="category_id">Kategori:</label><br>
    <select name="category_id" id="category_id">
        <option value="">Kategori Seçin</option>
        <?php if (!empty($all_categories)): ?>
            <?php 
            $selected_category_id = $activity['category_id'] ?? ($_POST['category_id'] ?? null);
            foreach ($all_categories as $category): ?>
                <option value="<?= e($category['id']) ?>" 
                    <?= ((string)$selected_category_id === (string)$category['id']) ? 'selected' : '' ?>>
                    <?= e($category['name']) ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select><br><br>

    <label for="class_ids">İlgili Sınıflar (Çoklu Seçim):</label><br>
    <select name="class_ids[]" id="class_ids" multiple="multiple" size="5" style="width: 100%; min-height: 100px;">
        <?php if (!empty($all_classes)): ?>
            <?php 
            // Seçili sınıf ID'lerini belirle
            // Düzenleme modundaysa $selected_class_ids kullan
            // Form gönderiminden dönüldüyse $_POST['class_ids'] kullan
            $current_selected_class_ids = $selected_class_ids ?? ($_POST['class_ids'] ?? []);
            ?>
            <?php foreach ($all_classes as $class): ?>
                <option value="<?= e($class['id']) ?>" 
                    <?= in_array($class['id'], $current_selected_class_ids) ? 'selected' : '' ?>>
                    <?= e($class['name']) ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select><br><br>

    <label for="image">Görsel:</label><br>
    <input type="file" id="image" name="image"><br>
    <?php if ($isEdit && !empty($activity['image_path'])): ?>
        <p style="margin-top: 5px;">Mevcut Görsel: <a href="<?= e($activity['image_path']) ?>" target="_blank">Görseli Görüntüle</a></p>
        <img src="<?= e($activity['image_path']) ?>" alt="Etkinlik Görseli" style="max-width: 150px; height: auto; display: block; margin-top: 5px;"><br>
    <?php endif; ?>

    <input type="checkbox" id="include_parents" name="include_parents" value="1" <?= (($activity['include_parents'] ?? ($_POST['include_parents'] ?? 0)) == 1) ? 'checked' : '' ?>>
    <label for="include_parents">Velileri Dahil Et (Bildirimler için)</label><br><br>

    <?php if ($isEdit && ($userRole ?? 'guest') === 'admin'): // Sadece admin düzenleme yaparken status değiştirebilir ?>
        <label for="status">Durum:</label><br>
        <select name="status" id="status">
            <option value="pending" <?= (($activity['status'] ?? ($_POST['status'] ?? '')) == 'pending') ? 'selected' : '' ?>>Beklemede</option>
            <option value="approved" <?= (($activity['status'] ?? ($_POST['status'] ?? '')) == 'approved') ? 'selected' : '' ?>>Onaylandı</option>
            <option value="rejected" <?= (($activity['status'] ?? ($_POST['status'] ?? '')) == 'rejected') ? 'selected' : '' ?>>Reddedildi</option>
        </select><br><br>
    <?php endif; ?>

    <button type="submit"><?= $isEdit ? "Güncelle" : "Oluştur" ?></button>
    <a href="index.php?module=activities&action=index" class="btn">Vazgeç</a>
</form>