<h2><?= $isEdit ? "Menü Öğesini Düzenle" : "Yeni Menü Öğesi Ekle" ?></h2>

<form method="post" action="<?= e($formAction) ?>">
    <?php if ($isEdit && $menu): ?>
        <input type="hidden" name="id" value="<?= e($menu['id']) ?>">
    <?php endif; ?>

    <div>
        <label for="parent_id">Üst Menü (Ana menü için boş bırakın):</label><br>
        <select name="parent_id" id="parent_id">
            <option value="">-- Ana Menü Öğesi --</option>
            <?php if(!empty($parent_menus)): foreach ($parent_menus as $parent): ?>
                <option value="<?= e($parent['id']) ?>" <?= (($menu['parent_id'] ?? null) == $parent['id']) ? 'selected' : '' ?>>
                    <?= e($parent['title']) ?>
                </option>
            <?php endforeach; endif; ?>
        </select>
    </div>
    <br>
    <div>
        <label for="title">Başlık:</label><br>
        <input type="text" id="title" name="title" value="<?= e($menu['title'] ?? '') ?>" required size="50">
    </div>
    <br>
    <div>
        <label for="url">URL (örn: index.php?module=students&action=index):</label><br>
        <input type="text" id="url" name="url" value="<?= e($menu['url'] ?? '') ?>" required size="70">
    </div>
    <br>
    <div>
        <label for="icon">İkon (örn: fa-users, isteğe bağlı):</label><br>
        <input type="text" id="icon" name="icon" value="<?= e($menu['icon'] ?? '') ?>" size="30">
    </div>
    <br>
    <div>
        <label for="display_order">Görüntülenme Sırası:</label><br>
        <input type="number" id="display_order" name="display_order" value="<?= e($menu['display_order'] ?? 0) ?>" style="width: 70px;">
    </div>
    <br>
    <div>
        <label>Görünecek Roller:</label><br>
        <?php if(!empty($all_roles)): foreach ($all_roles as $role_key): ?>
            <label style="margin-right: 10px;">
                <input type="checkbox" name="roles[]" value="<?= e($role_key) ?>" 
                    <?= !empty($assigned_roles) && in_array($role_key, $assigned_roles) ? 'checked' : '' ?>>
                <?= e(ucfirst($role_key)) ?>
            </label>
        <?php endforeach; endif;?>
    </div>
    <br>
    <div>
        <label>
            <input type="checkbox" name="is_active" value="1" <?= (($menu['is_active'] ?? 1) == 1) ? 'checked' : '' ?>>
            Aktif mi?
        </label>
    </div>
    <br>
    <button type="submit"><?= $isEdit ? "Güncelle" : "Oluştur" ?></button>
    <a href="index.php?module=menumanager&action=index" style="margin-left: 10px;">Vazgeç</a>
</form>