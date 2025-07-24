<?php
// düzenleme modundaysa form dolu olur
$isEdit = isset($student);
$action = $isEdit ? "index.php?module=students&action=update" : "index.php?module=students&action=store";
?>

<form method="post" action="<?= $action ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= $student['id'] ?>">
    <?php endif; ?>

    <div class="form-group">
        <label>Ad Soyad</label>
        <input type="text" name="name" class="form-control" value="<?= $student['name'] ?? '' ?>" required>
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?= $student['email'] ?? '' ?>" required>
    </div>

    <div class="form-group">
        <label>Sınıf</label>
        <input type="text" name="sinif" class="form-control" value="<?= $student['sinif'] ?? '' ?>">
    </div>

    <div class="form-group">
        <label>Telefon</label>
        <input type="text" name="phone" class="form-control" value="<?= $student['phone'] ?? '' ?>">
    </div>

    <div class="form-group">
        <label>Adres</label>
        <textarea name="address" class="form-control"><?= $student['address'] ?? '' ?></textarea>
    </div>

    <div class="form-group">
        <label>Doğum Yeri</label>
        <input type="text" name="birth_place" class="form-control" value="<?= $student['birth_place'] ?? '' ?>">
    </div>

    <div class="form-group">
        <label>Doğum Tarihi</label>
        <input type="date" name="birth_date" class="form-control" value="<?= $student['birth_date'] ?? '' ?>">
    </div>

    <div class="form-group">
        <label>Veli Adı</label>
        <input type="text" name="parent_name" class="form-control" value="<?= $student['parent_name'] ?? '' ?>">
    </div>

    <div class="form-group">
        <label>Not</label>
        <textarea name="student_note" class="form-control"><?= $student['student_note'] ?? '' ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Kaydet</button>
</form>
