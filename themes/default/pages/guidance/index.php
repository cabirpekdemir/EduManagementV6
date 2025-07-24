<h2>Rehberlik Seansları</h2>
<p class="lead">Öğrencilerle yapılan veya planlanan rehberlik seanslarının listesi.</p>

<?php if (($userRole ?? 'guest') === 'admin' || ($userRole ?? 'guest') === 'teacher'): ?>
    <a href="index.php?module=guidance&action=create" class="btn btn-primary mb-3">
        <i class="fa fa-plus"></i> Yeni Seans Ekle
    </a>
<?php endif; ?>

<!-- Bildirim Mesajları -->
<?php if (isset($_GET['status_message'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_GET['status_message']) ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error_message'])): ?>
     <div class="alert alert-danger">
        <?= htmlspecialchars($_GET['error_message']) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tüm Seanslar</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Öğrenci</th>
                    <th>Rehber/Danışman</th>
                    <th>Seans Tarihi</th>
                    <th>Konu</th>
                    <th style="width: 20%;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sessions)): ?>
                    <tr><td colspan="5" class="text-center p-4">Kayıtlı rehberlik seansı bulunamadı.</td></tr>
                <?php else: ?>
                    <?php foreach ($sessions as $session): ?>
                    <tr>
                        <!-- MOBİL UYUM İÇİN data-label EKLENDİ -->
                        <td data-label="Öğrenci"><strong><?= htmlspecialchars($session['student_name']) ?></strong></td>
                        <td data-label="Danışman"><?= htmlspecialchars($session['counselor_name']) ?></td>
                        <td data-label="Tarih"><?= htmlspecialchars(date('d.m.Y', strtotime($session['session_date']))) ?></td>
                        <td data-label="Konu"><?= htmlspecialchars($session['title']) ?></td>
                        <td data-label="İşlemler">
                            <?php 
                            $can_edit_delete = false;
                            $can_view_detail_only = false;

                            if (($userRole ?? 'guest') === 'admin') {
                                $can_edit_delete = true;
                            } elseif (($userRole ?? 'guest') === 'teacher' && $session['counselor_id'] == ($currentUserId ?? 0)) {
                                $can_edit_delete = true;
                            } elseif (($userRole ?? 'guest') === 'student' || ($userRole ?? 'guest') === 'parent') {
                                $can_view_detail_only = true;
                            }
                            ?>
                            <?php if ($can_edit_delete): ?>
                                <a href="index.php?module=guidance&action=view&id=<?= htmlspecialchars($session['id']) ?>" class="btn btn-sm btn-info" title="Detayları Gör">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="index.php?module=guidance&action=edit&id=<?= htmlspecialchars($session['id']) ?>" class="btn btn-sm btn-warning" title="Düzenle">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="index.php?module=guidance&action=delete&id=<?= htmlspecialchars($session['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu seansı silmek istediğinize emin misiniz?')" title="Sil">
                                    <i class="fa fa-trash"></i>
                                </a>
                            <?php elseif ($can_view_detail_only): ?>
                                <a href="index.php?module=guidance&action=view&id=<?= htmlspecialchars($session['id']) ?>" class="btn btn-sm btn-primary">
                                    <i class="fa fa-folder-open"></i> Detayları Gör
                                </a>
                            <?php else: ?>
                                <span>-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
