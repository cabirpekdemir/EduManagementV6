<h2>Veli Eşleştir</h2>
<form method="post" action="?module=students&action=saveParent&id=<?= $student['id'] ?>">
    <select name="parent_id">
        <option value="">Seçiniz</option>
        <?php foreach ($parents as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $assigned_parent == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Kaydet</button>
</form>
