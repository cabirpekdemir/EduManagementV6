<form action="<?= $formAction ?? '' ?>" method="post">
    <div class="form-group">
        <label for="name">Ad Soyad</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="form-group">
        <label for="email">E-posta Adresi</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="form-group">
        <label for="tc_kimlik">TC Kimlik Numarası</label>
        <input type="text" class="form-control" id="tc_kimlik" name="tc_kimlik" required>
    </div>
    <div class="form-group">
        <label for="password">Şifre (Boş bırakırsanız güncellenmez)</label>
        <input type="password" class="form-control" id="password" name="password">
    </div>
    <div class="form-group">
        <label for="class_id">Sınıfı</label>
        <select class="form-control" id="class_id" name="class_id">
            <option value="">-- Sınıf Seç --</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Kaydet</button>
</form>