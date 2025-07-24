<h2>Etkinlik Yönetimi</h2>

<!-- Butonlar AdminLTE stiliyle güncellendi -->
<a href="index.php?module=activities&action=create" class="btn btn-primary mb-3">
    <i class="fa fa-plus"></i> Yeni Etkinlik Oluştur
</a>
<a href="index.php?module=activities&action=calendar" class="btn btn-info mb-3">
    <i class="fa fa-calendar"></i> Takvim Görünümü
</a>

<!-- Bildirim Mesajları -->
<?php if (isset($_GET['status_message'])): ?>
    <div class="alert alert-success">
        <?php
        $messages = [
            'created' => 'Etkinlik başarıyla oluşturuldu ve onaya gönderildi.',
            'updated' => 'Etkinlik başarıyla güncellendi.',
            'deleted' => 'Etkinlik başarıyla silindi.',
            'approved' => 'Etkinlik onaylandı.',
            'rejected' => 'Etkinlik reddedildi.'
        ];
        echo htmlspecialchars($messages[$_GET['status_message']] ?? 'İşlem başarılı.');
        ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error_message'])): ?>
     <div class="alert alert-danger">
        <?= htmlspecialchars($_GET['error_message']) ?>
     </div>
<?php endif; ?>


<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tüm Etkinlikler</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Başlık</th>
                    <th>Kategori</th>
                    <th>Tarih</th>
                    <th>Oluşturan</th>
                    <th>Durum</th>
                    <th style="width: 25%;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($activities)): ?>
                    <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td data-label="Başlık"><strong><?= htmlspecialchars($activity['title']) ?></strong></td>
                        <td data-label="Kategori"><?= htmlspecialchars($activity['category_name'] ?? 'Belirtilmemiş') ?></td>
                        <td data-label="Tarih"><?= htmlspecialchars(date('d.m.Y H:i', strtotime($activity['activity_date']))) ?></td>
                        <td data-label="Oluşturan"><?= htmlspecialchars($activity['creator_name']) ?></td>
                        <td data-label="Durum">
                            <?php
                                $status = $activity['status'] ?? 'bilinmiyor';
                                $status_map = [
                                    'approved' => ['class' => 'badge-success', 'text' => 'Onaylandı'],
                                    'pending' => ['class' => 'badge-warning', 'text' => 'Bekliyor'],
                                    'rejected' => ['class' => 'badge-danger', 'text' => 'Reddedildi']
                                ];
                                $s = $status_map[$status] ?? ['class' => 'badge-secondary', 'text' => ucfirst($status)];
                            ?>
                            <span class="badge <?= $s['class'] ?>"><?= $s['text'] ?></span>
                        </td>
                        <td data-label="İşlemler">
                            <?php if ($activity['status'] === 'approved'): ?>
                                <a href="<?= generateGoogleCalendarLink($activity) ?>" target="_blank" class="btn btn-sm btn-light" title="Google Takvime Ekle">
                                    <i class="fa fa-google"></i>
                                </a>
                            <?php endif; ?>

                            <?php
                            $can_edit = ($userRole === 'admin' || ($userRole === 'teacher' && $activity['creator_id'] == $currentUserId));
                            $can_delete = $can_edit;
                            ?>
                            <?php if ($can_edit): ?>
                                <a href="index.php?module=activities&action=edit&id=<?= htmlspecialchars($activity['id']) ?>" class="btn btn-sm btn-warning">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            <?php endif; ?>
                            <?php if ($can_delete): ?>
                                <a href="index.php?module=activities&action=delete&id=<?= htmlspecialchars($activity['id']) ?>"
                                   class="btn btn-sm btn-danger ml-1"
                                   onclick="return confirm('Bu etkinliği silmek istediğinize emin misiniz?')">
                                   <i class="fa fa-trash"></i>
                                </a>
                            <?php endif; ?>

                            <?php if ($userRole === 'admin' && $activity['status'] === 'pending'): ?>
                                <form method="post" action="index.php?module=activities&action=approve&id=<?= htmlspecialchars($activity['id']) ?>" class="d-inline-block mt-2">
                                    <button type="submit" class="btn btn-sm btn-success">Onayla</button>
                                    <a href="index.php?module=activities&action=reject&id=<?= htmlspecialchars($activity['id']) ?>" class="btn btn-sm btn-outline-danger ml-1">Reddet</a>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center p-4">Kayıtlı etkinlik bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
