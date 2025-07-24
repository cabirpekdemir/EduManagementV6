<h2><?= $isEdit ? "Etkinlik Kategorisini Düzenle" : "Yeni Etkinlik Kategorisi Ekle" ?></h2>

<form method="post" action="<?= e($formAction) ?>">
    <?php if ($isEdit && isset($category)): ?>
        <input type="hidden" name="id" value="<?= e($category['id']) ?>">
    <?php endif; ?>

    <div>
        <label for="name">Kategori Adı (Örn: Veli Toplantısı, Seminer):</label><br>
        <input type="text" id="name" name="name" value="<?= e($category['name'] ?? '') ?>" required size="50">
    </div>
    <br>
    <div>
        <label for="description">Açıklama (İsteğe Bağlı):</label><br>
        <textarea id="description" name="description" rows="4" cols="50"><?= e($category['description'] ?? '') ?></textarea>
    </div>
    <br>
    
    <button type="submit"><?= $isEdit ? "Güncelle" : "Oluştur" ?></button>
    <a href="index.php?module=activity_categories&action=index" style="margin-left: 10px;">Vazgeç</a>
</form>