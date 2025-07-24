<div class="container mt-4">
    <h2>Öğrenci Listesi</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Ad Soyad</th>
                <th>Email</th>
                <th>Sınıf</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($students)): ?>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= htmlspecialchars($student['name']) ?></td>
                        <td><?= htmlspecialchars($student['email']) ?></td>
                        <td><?= htmlspecialchars($student['sinif']) ?></td>
                        <td>
    <a href="index.php?module=students&action=edit&id=<?= $student['id'] ?>" class="btn btn-sm btn-primary" title="Düzenle">
        <i class="fas fa-edit"></i>
    </a>
    <a href="index.php?module=students&action=show&id=<?= $student['id'] ?>" class="btn btn-sm btn-info" title="Görüntüle">
        <i class="fas fa-id-card"></i>
    </a>
</td>

                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">Kayıtlı öğrenci bulunamadı.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
